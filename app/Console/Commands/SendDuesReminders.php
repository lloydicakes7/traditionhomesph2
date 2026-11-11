<?php

namespace App\Console\Commands;

use App\Models\Bill\Bill;
use App\Notifications\DuesReminderNotification;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * The automated email reminder the Collections settings switch turns on.
 *
 * It runs daily (see App\Console\Kernel) and looks at the recurring monthly
 * dues whose due date is within the reminder window and whose balance is still
 * open. Each one earns a single reminder: `bills.reminder_sent_at` records that
 * it went out, so a bill is never reminded twice. The switch itself lives with
 * the rest of the dues configuration, so an officer turns the reminders on and
 * off where they set the rates (Settings → Collections).
 */
class SendDuesReminders extends Command
{
    /**
     * How many days before its due date a bill is reminded, at most.
     *
     * The command runs once a day, so a bill is reminded on the first run that
     * finds it inside this window — three days out by default.
     */
    const REMIND_WITHIN_DAYS = 3;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dues:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind homeowners of monthly dues whose due date is near (automated email reminders)';

    /** @var CollectionSettingsQuery */
    private $settings;

    public function __construct(CollectionSettingsQuery $settings)
    {
        parent::__construct();

        $this->settings = $settings;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! $this->settings->get('auto_email_reminders')) {
            $this->info('Automated email reminders are switched off.');

            return 0;
        }

        $windowEnd = Carbon::now()->startOfDay()->addDays(self::REMIND_WITHIN_DAYS);

        $dues = Bill::query()
            ->monthlyDues()
            ->open()
            ->whereNotNull('user_id')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', Carbon::today()->toDateString())
            ->whereDate('due_date', '<=', $windowEnd->toDateString())
            ->whereNull('reminder_sent_at')
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($dues as $due) {
            if (! $due->user) {
                continue;
            }

            $due->user->notify(new DuesReminderNotification($due));

            $due->update(['reminder_sent_at' => now()]);

            $sent++;
        }

        $this->info('Sent ' . $sent . ' dues reminder(s).');

        return 0;
    }
}
