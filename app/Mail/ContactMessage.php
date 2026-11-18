<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public string $senderName;

    public string $senderEmail;

    public string $senderContact;

    public string $subjectLine;

    public string $inquiry;

    public function __construct(array $data)
    {
        $this->senderName = $data['name'];
        $this->senderEmail = $data['email'];
        $this->senderContact = (string) ($data['contact_number'] ?? '');
        $this->subjectLine = $data['subject'];
        $this->inquiry = $data['message'];
    }

    /**
     * The inquiry is sent from the association's own address, and answers go
     * back to the resident through the Reply-To header. It is written in the
     * association's mail layout, which produces an HTML and a plain text part.
     */
    public function build()
    {
        return $this->subject(__('public.contact.mail_subject', ['subject' => $this->subjectLine]))
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo($this->senderEmail, $this->senderName)
            ->markdown('emails.contact-message');
    }
}
