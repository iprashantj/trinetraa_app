<?php

namespace App\Console\Commands;

use App\Support\Messenger;
use App\Support\Reminders;
use Illuminate\Console\Command;

/** Daily customer reminders: eye-test follow-ups, birthdays, lens renewals, review requests. */
class SendCustomerReminders extends Command
{
    protected $signature = 'reminders:send {--dry-run : Only count who would be messaged}';

    protected $description = 'Send eye-test follow-up, birthday, lens subscription and review-request reminders';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $rows = [
            ['eye-test follow-ups', Reminders::sendEyeTestFollowups($dry)],
            ['birthdays', Reminders::sendBirthdayWishes($dry)],
            ['review requests', Reminders::sendReviewRequests($dry)],
        ];
        if ($dry) {
            $rows[] = ['lens subscriptions (email)', ['total' => Reminders::countLensSubscriptionsDue(), 'sent' => 0, 'failed' => 0, 'manual' => 0]];
        } elseif (Messenger::configured('email')) {
            $rows[] = ['lens subscriptions (email)', Reminders::sendLensSubscriptionReminders() + ['manual' => 0]];
        }
        $this->table(['Reminder', 'Eligible', 'Sent', 'Failed', 'Needs manual send'], array_map(
            fn ($r) => [$r[0], $r[1]['total'], $r[1]['sent'], $r[1]['failed'], $r[1]['manual'] ?? 0], $rows));
        if ($dry) {
            $this->comment('Dry run: nothing was sent.');
        }

        return self::SUCCESS;
    }
}
