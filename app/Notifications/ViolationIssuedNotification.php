<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Violation;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * A violation was recorded against the homeowner's household.
 *
 * The one notice that is both mailed and kept: it is a document the homeowner
 * has to acknowledge, so the mail carries the full notice and the bell keeps
 * the line until they read it. It is issued by an officer only — never by the
 * homeowner — so no "your own doing" case exists here (see
 * App\Actions\Support\Violations\IssueViolation).
 */
class ViolationIssuedNotification extends ThhaNotification
{
    /** @var \App\Models\Violation */
    private $violation;

    public function __construct(Violation $violation)
    {
        $this->violation = $violation;
    }

    public function type(): NotificationType
    {
        return NotificationType::VIOLATION();
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
        return 'Violation recorded — ' . $this->violation->type_label;
    }

    protected function body($notifiable): string
    {
        return $this->violation->occurred_display . ' · ' . $this->violation->location
            . ' — open it to read the notice and acknowledge it.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.violations');
    }

    /**
     * The mail itself.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $violation = $this->violation;

        return (new MailMessage())
            ->subject('Notice of Violation — ' . $violation->type_label)
            ->greeting('Hello, ' . $violation->resident_label . '!')
            ->line('The association has recorded a violation against your household.')
            ->line('Violation: ' . $violation->type_label)
            ->line('When: ' . $violation->occurred_display)
            ->line('Where: ' . $violation->location)
            ->line('Details: ' . $violation->description)
            ->line('Please sign in to the homeowner portal to read the full notice and acknowledge that you have received it.')
            ->line('Acknowledging only confirms that you have read this notice — it does not mean you agree with it.')
            ->salutation(config('app.name'));
    }
}
