<?php

namespace App\Support;

/**
 * Sends transactional email via the Resend HTTP API instead of SMTP/sendmail.
 * Needed because this host disables stream_socket_client and proc_open,
 * which blocks both Symfony Mailer's smtp and sendmail transports.
 */
class ResendMailer
{
    public static function send(string $to, string $subject, string $html): void
    {
        $apiKey = env('RESEND_API_KEY');
        if (! $apiKey) {
            throw new \RuntimeException('RESEND_API_KEY is not set.');
        }

        $fromAddress = env('RESEND_FROM_ADDRESS', 'onboarding@resend.dev');
        $fromName = env('RESEND_FROM_NAME', 'Trinetraa Optician');

        $payload = json_encode([
            'from'    => $fromName . ' <' . $fromAddress . '>',
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $html,
        ]);

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('Resend request failed: ' . $error);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException('Resend API error (' . $httpCode . '): ' . $response);
        }
    }
}
