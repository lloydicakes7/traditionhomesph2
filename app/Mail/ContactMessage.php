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

    public function build()
    {
        return $this->subject(__('public.contact.mail_subject', ['subject' => $this->subjectLine]))
            ->replyTo($this->senderEmail, $this->senderName)
            ->markdown('emails.contact-message');
    }
}
