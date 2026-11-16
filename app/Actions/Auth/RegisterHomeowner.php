<?php

namespace App\Actions\Auth;

use App\Enums\ProfileStatus;
use App\Enums\RegistrationStatus;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\HomeownerRegisteredNotification;
use App\Queries\Settings\AuthenticationSettingsQuery;
use App\Services\NotificationService;
use App\Utility\SettingsHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Submit a homeowner's self-registration.
 *
 * The person and the account are written in one transaction; the address, the
 * household and the valid ID are not asked for yet. The account is enabled at
 * once but cannot be used until the email address is verified, and that link is
 * the notice the applicant receives. The household details follow on the
 * onboarding form, where the office checks them.
 *
 * Side effects run after the transaction has committed: a mail server that is
 * down must not unwind a registration already saved.
 */
class RegisterHomeowner
{
    /** @var NotificationService */
    private $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    /**
     * Create the person and the account they sign in with.
     *
     * @param  array<string, mixed>  $data  email, password, resident (form array)
     */
    public function handle(array $data): User
    {
        if (! $this->registrationOpen()) {
            throw new \RuntimeException('Registration is closed.');
        }

        $user = DB::transaction(function () use ($data) {
            $resident = Resident::create(Resident::payloadFrom($data['resident']));

            return User::create([
                'resident_id'         => $resident->id,
                'email'               => $data['email'],
                'password'            => Hash::make($data['password']),
                'password_changed_at' => now(),
                'terms_accepted_at'   => now(),
                'privacy_accepted_at' => now(),
                'registration_status' => RegistrationStatus::APPROVED,
                'is_active'           => true,
                'profile_status'      => ProfileStatus::INCOMPLETE,
            ]);
        });

        $this->sendSideEffects($user);

        return $user;
    }

    // ===== Side effects =====

    /**
     * Mail the verification link and tell the office, once the write has committed.
     */
    private function sendSideEffects(User $user): void
    {
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            Log::error('Verification email failed: ' . get_class($exception));
        }

        try {
            $this->notifications->toOfficers(new HomeownerRegisteredNotification($user));
        } catch (\Throwable $exception) {
            Log::error('Registration notification failed: ' . get_class($exception));
        }
    }

    // ===== Tools =====

    private function registrationOpen(): bool
    {
        $stored = SettingsHelper::get(
            AuthenticationSettingsQuery::ALLOW_RESIDENT_REGISTRATION,
            AuthenticationSettingsQuery::GROUP,
            '1'
        );

        return $stored === '1';
    }
}
