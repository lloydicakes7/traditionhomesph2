<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Bill\Bill;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * A monthly due is nearly due and still unpaid, the association's reminder to
 * settle it.
 *
 * One reminder per bill, sent by the scheduled `dues:send-reminders` command
 * when the account's `auto_email_reminders` switch is on (see
 * \App\Console\Commands\SendDuesReminders). It is the same shape as every other
 * homeowner event: a line for the bell (database) and the words by mail, so a
 * homeowner who never opens the portal still hears it.
 */
class DuesReminderNotification extends ThhaNotification
{
    /** @var \App\Models\Bill\Bill */
    private $bill;

    public function __construct(Bill $bill)
    {
        $this->bill = $bill;
    }

    public function type(): NotificationType
    {
        return NotificationType::STATEMENT();
    }

    /**
     * The channels this notification goes out on.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    protected function title($notifiable): string
    {
        return 'Monthly dues reminder for ' . $this->bill->billing_period_display;
    }

    protected function body($notifiable): string
    {
        return 'Your monthly dues for ' . $this->bill->billing_period_display . ' have an outstanding balance of '
            . $this->bill->balance_display . ' due on ' . $this->dueDate() . '.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.statement-of-account');
    }

    /**
     * The mail itself: what is owed, for which month, and when it is due.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $reference = $this->bill->reference ? ' (reference ' . $this->bill->reference . ')' : '';

        return (new MailMessage())
            ->template('emails.notification')
            ->subject('Monthly Dues Reminder for ' . $this->bill->billing_period_display)
            ->greeting('Greetings, ' . ($notifiable->name ?: 'Homeowner') . ',')
            ->line('This is a reminder that your monthly association dues for '
                . $this->bill->billing_period_display . ' remain unpaid.')
            ->line('Billing period: ' . $this->bill->billing_period_display . $reference)
            ->line('Amount due: ' . $this->bill->balance_display)
            ->line('Due date: ' . $this->dueDate())
            ->line('Kindly settle the amount through any of the payment methods listed on your statement of account, '
                . 'or in person at the association office.')
            ->action('View your statement of account', route('portal.statement-of-account'));
    }

    /**
     * The due date as the line and the mail write it.
     */
    private function dueDate(): string
    {
        return $this->bill->due_date ? $this->bill->due_date->format('M j, Y') : 'not recorded';
    }
}
