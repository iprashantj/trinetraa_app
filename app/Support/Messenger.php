<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\ReminderLog;
use Illuminate\Support\Facades\Http;

/**
 * One place to send customer messages over WhatsApp, SMS or email.
 *
 * A provider is "configured" only when its env keys are present; otherwise send() returns
 * status 'not_configured' and callers fall back to wa.me / sms: links the staff member opens themselves.
 *
 *   WhatsApp (Meta Cloud API): WHATSAPP_TOKEN, WHATSAPP_PHONE_ID, optional WHATSAPP_TEMPLATE (+ WHATSAPP_TEMPLATE_LANG)
 *   SMS (MSG91 Flow API):      MSG91_AUTH_KEY, MSG91_SENDER, MSG91_TEMPLATE_ID
 *                              (India requires a DLT-registered sender + template; the message text goes in as variable "message")
 *   Email (Resend):            RESEND_API_KEY
 *
 * Transactional messages (order ready, invoice) need no marketing consent; promotional ones do.
 */
class Messenger
{
    public const CHANNELS = ['whatsapp', 'sms', 'email'];

    public static function configured(string $channel): bool
    {
        return match ($channel) {
            'whatsapp' => (bool) (env('WHATSAPP_TOKEN') && env('WHATSAPP_PHONE_ID')),
            'sms' => (bool) (env('MSG91_AUTH_KEY') && env('MSG91_SENDER') && env('MSG91_TEMPLATE_ID')),
            'email' => (bool) env('RESEND_API_KEY'),
            default => false,
        };
    }

    /** Digits with country code (10-digit Indian numbers get 91), or null when not a usable mobile number. */
    public static function phone(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if (strlen($d) === 10) {
            return '91' . $d;
        }

        return strlen($d) >= 11 && strlen($d) <= 15 ? $d : null;
    }

    public static function waLink(?string $phone, string $message): ?string
    {
        $p = self::phone($phone);

        return $p ? 'https://wa.me/' . $p . '?text=' . rawurlencode($message) : null;
    }

    public static function smsLink(?string $phone, string $message): ?string
    {
        $p = self::phone($phone);

        return $p ? 'sms:+' . $p . '?body=' . rawurlencode($message) : null;
    }

    /** Promotional sends only reach customers who agreed; WhatsApp additionally needs the WhatsApp opt-in. */
    public static function mayMessage(?Customer $c, string $channel, bool $transactional): bool
    {
        if ($transactional) {
            return true;
        }
        if (! $c || ! $c->marketing_consent) {
            return false;
        }

        return $channel !== 'whatsapp' || (bool) $c->whatsapp_opt_in;
    }

    /**
     * Send one message on one channel.
     * $o: type (log label), recipient_id (customer id), subject (email), params (template variables), transactional.
     * Returns ['status' => sent|failed|not_configured|skipped, 'channel' => ..., 'error' => ?string].
     */
    public static function send(string $channel, string $to, string $message, array $o = []): array
    {
        $res = ['channel' => $channel, 'status' => 'not_configured', 'error' => null];
        if (! in_array($channel, self::CHANNELS, true)) {
            return ['channel' => $channel, 'status' => 'failed', 'error' => 'Unknown channel'];
        }
        if (! self::configured($channel)) {
            return $res;
        }
        $phone = $channel === 'email' ? null : self::phone($to);
        if ($channel !== 'email' && ! $phone) {
            return ['channel' => $channel, 'status' => 'skipped', 'error' => 'No valid mobile number'];
        }
        if ($channel === 'email' && ! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['channel' => $channel, 'status' => 'skipped', 'error' => 'No valid email address'];
        }

        try {
            match ($channel) {
                'whatsapp' => self::viaWhatsApp($phone, $message, $o['params'] ?? []),
                'sms' => self::viaSms($phone, $message, $o['params'] ?? []),
                'email' => ResendMailer::send($to, $o['subject'] ?? 'Trinetraa Optician', '<div style="font-family:sans-serif;max-width:520px;margin:0 auto;padding:20px">' . nl2br(e($message)) . '</div>'),
            };
            $res['status'] = 'sent';
        } catch (\Throwable $e) {
            $res['status'] = 'failed';
            $res['error'] = mb_substr($e->getMessage(), 0, 500);
        }

        ReminderLog::create([
            'type' => $o['type'] ?? 'message',
            'recipient_id' => $o['recipient_id'] ?? null,
            'email' => $channel === 'email' ? $to : ($phone ?? $to),
            'subject' => '[' . $channel . '] ' . mb_substr($o['subject'] ?? $message, 0, 120),
            'status' => $res['status'] === 'sent' ? 'sent' : 'failed',
            'sent_at' => now(),
            'error' => $res['error'],
        ]);

        return $res;
    }

    /**
     * Customer notification: first configured channel that works (WhatsApp → SMS → email).
     * Always also returns wa.me / sms: fallback links so the UI can offer them when nothing was sent automatically.
     * $o adds: customer (Customer model), phone, email, transactional (default true).
     */
    public static function notify(string $message, array $o = []): array
    {
        $customer = $o['customer'] ?? null;
        $transactional = $o['transactional'] ?? true;
        $phone = $o['phone'] ?? $customer?->phone;
        $email = $o['email'] ?? $customer?->email;
        $attempts = [];
        $sent = null;

        foreach (['whatsapp' => $phone, 'sms' => $phone, 'email' => $email] as $channel => $to) {
            if (! $to || ! self::mayMessage($customer, $channel, $transactional)) {
                continue;
            }
            $r = self::send($channel, (string) $to, $message, $o + ['recipient_id' => $customer?->id]);
            $attempts[] = $r;
            if ($r['status'] === 'sent') {
                $sent = $channel;
                break;
            }
        }

        return [
            'sent' => (bool) $sent,
            'channel' => $sent,
            'attempts' => $attempts,
            'whatsapp_url' => self::waLink($phone, $message),
            'sms_url' => self::smsLink($phone, $message),
        ];
    }

    private static function viaWhatsApp(string $phone, string $message, array $params): void
    {
        $template = env('WHATSAPP_TEMPLATE');
        $payload = ['messaging_product' => 'whatsapp', 'to' => $phone];
        if ($template && $params) {
            $payload += ['type' => 'template', 'template' => [
                'name' => $template, 'language' => ['code' => env('WHATSAPP_TEMPLATE_LANG', 'en')],
                'components' => [['type' => 'body', 'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], array_values($params))]],
            ]];
        } else {
            // Free-form text is only delivered inside WhatsApp's 24-hour customer-service window.
            $payload += ['type' => 'text', 'text' => ['preview_url' => true, 'body' => $message]];
        }
        $res = Http::withToken((string) env('WHATSAPP_TOKEN'))->timeout(20)
            ->post('https://graph.facebook.com/v20.0/' . env('WHATSAPP_PHONE_ID') . '/messages', $payload);
        if (! $res->successful()) {
            throw new \RuntimeException('WhatsApp error: ' . ($res->json('error.message') ?? $res->status()));
        }
    }

    private static function viaSms(string $phone, string $message, array $params): void
    {
        $recipient = ['mobiles' => $phone, 'message' => $message];
        foreach (array_values($params) as $i => $p) {
            $recipient['var' . ($i + 1)] = (string) $p;
        }
        $res = Http::withHeaders(['authkey' => (string) env('MSG91_AUTH_KEY')])->timeout(20)
            ->post('https://control.msg91.com/api/v5/flow/', [
                'template_id' => env('MSG91_TEMPLATE_ID'), 'sender' => env('MSG91_SENDER'), 'short_url' => 0,
                'recipients' => [$recipient],
            ]);
        if (! $res->successful() || strtolower((string) $res->json('type')) === 'error') {
            throw new \RuntimeException('SMS error: ' . ($res->json('message') ?? $res->status()));
        }
    }
}
