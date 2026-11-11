<?php

namespace App\Actions\Support;

use App\Mail\ContactMessage;
use App\Utility\SettingsHelper;
use Illuminate\Support\Facades\Mail;

class SendContactMessage
{
    // ===== Handle =====

    public function handle(array $data): bool
    {
        $recipient = trim((string) SettingsHelper::get('email', 'hoa_info'));

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        Mail::to($recipient)->send(new ContactMessage($data));

        return true;
    }
}
