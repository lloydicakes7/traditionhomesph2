<?php

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\SoaEmail;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * A statement of account — the account's own position, sent by the office.
 *
 * The statement is written to an address on the record, so the send path mails
 * it on demand; the account that address belongs to also keeps a line for the
 * bell, which is the same event told twice on purpose. Channels are chosen by
 * the notifiable: mailed to an address, stored for an account
 * (App\Actions\Collections\Soa\SendStatementsOfAccount).
 */
class StatementOfAccountNotification extends ThhaNotification
{
    /** @var \App\Models\SoaEmail */
    private $statement;

    /** @var array<string, mixed> */
    private $summary;

    /**
     * @param  \App\Models\SoaEmail  $statement
     * @param  array<string, mixed>  $summary  As built by StatementDataQuery
     */
    public function __construct(SoaEmail $statement, array $summary)
    {
        $this->statement = $statement;
        $this->summary = $summary;
    }

    public function type(): NotificationType
    {
        return NotificationType::STATEMENT();
    }

    /**
     * The channels this notification goes out on.
     *
     * A portal account keeps the statement for the bell; a bare email address —
     * the on-demand notifiable the send path uses — can only be mailed.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return $notifiable instanceof User ? ['database'] : ['mail'];
    }

    protected function title($notifiable): string
    {
        return 'Statement of account — ' . $this->summary['period'];
    }

    protected function body($notifiable): string
    {
        return 'The office sent your statement of account. It is in your email, and the open bills behind it are on '
            . 'your account here.';
    }

    protected function url($notifiable): ?string
    {
        return route('portal.statement-of-account');
    }

    /**
     * The mail itself — the account, what is outstanding, and how to settle it.
     *
     * The statement is rendered from one view so it reads as a single running
     * account: every bill the homeowner owes for the period, dues and one-off
     * charges alike, appears in the one table.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('Statement of Account — ' . $this->summary['period'])
            ->markdown('emails.statement-of-account', [
                'summary' => $this->summary,
            ]);
    }
}
