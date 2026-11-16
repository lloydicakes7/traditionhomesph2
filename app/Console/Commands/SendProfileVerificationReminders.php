<?php

namespace App\Console\Commands;

use App\Actions\Homeowners\Profiles\NotifyPendingProfileVerifications;
use Illuminate\Console\Command;

class SendProfileVerificationReminders extends Command
{
    protected $signature = 'homeowners:send-verification-reminders';

    protected $description = 'Tell the officers about homeowner accounts still waiting for verification approval';

    public function handle(NotifyPendingProfileVerifications $reminders): int
    {
        $pending = $reminders->handle();

        $this->info($pending === 0
            ? 'No homeowner account is waiting for verification.'
            : 'Reminded the office about ' . $pending . ' homeowner account(s) waiting for verification.');

        return 0;
    }
}
