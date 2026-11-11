<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * "We received your registration" — an acknowledgement only.
 *
 * It never carries a password: the applicant chose their own when they filed,
 * and no password is ever written down, logged, or mailed.
 */
class RegistrationReceived extends Mailable
{
    use Queueable, SerializesModels;

    /** @var \App\Models\User */
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject(__('auth.registration_received_subject'))
            ->markdown('emails.registration-received');
    }
}
