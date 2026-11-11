<?php

namespace App\Actions\Auth;

use App\Enums\HouseholdType;
use App\Enums\RegistrationStatus;
use App\Mail\RegistrationReceived;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\HomeownerRegisteredNotification;
use App\Queries\Settings\AuthenticationSettingsQuery;
use App\Services\NotificationService;
use App\Utility\SettingsHelper;
use App\Utility\StorageHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Submit a homeowner's self-registration.
 *
 * The account, the person, the unit, the household or membership, and the
 * valid ID are written in one transaction. Every property stands in the one
 * address the association assigns; a unit the office has no record of is
 * created under it. The applicant chooses their own password, which is hashed
 * here and never logged, flashed, or mailed back. Side effects run after the
 * transaction has committed; whether the account opens at once or waits for the
 * office is the office's switch.
 */
class RegisterHomeowner
{
    /** @var \App\Services\NotificationService */
    private $notifications;

    /** @var string|null The ID written for this attempt, so a failure can remove it */
    private $storedIdPath = null;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    /**
     * Create the account, the resident, and the property relationship.
     *
     * @param  array<string, mixed>  $data  email, password, resident (form array),
     *         property (block/lot/sqm/type), household (type/prev_address),
     *         link_household
     */
    public function handle(array $data, UploadedFile $idFile): User
    {
        if (! $this->registrationOpen()) {
            throw new \RuntimeException('Registration is closed.');
        }

        try {
            $user = DB::transaction(function () use ($data, $idFile) {
                $property = $this->propertyFor($data);

                $resident = $this->createResident($data, $idFile);

                $user = $this->createAccount($data, $resident);

                $this->attachHousehold($property, $data, $resident, $user);

                return $user;
            });
        } catch (\Throwable $exception) {
            $this->discardStoredId();

            throw $exception;
        }

        $this->sendSideEffects($user);

        return $user;
    }

    // ===== Records =====

    /**
     * The person the account belongs to, with the submitted ID on the private disk.
     */
    private function createResident(array $data, UploadedFile $idFile): Resident
    {
        $payload = Resident::payloadFrom($data['resident']);

        $payload['valid_id_path'] = StorageHelper::storePrivate($idFile, 'ids/residents');
        $this->storedIdPath = $payload['valid_id_path'];

        return Resident::create($payload);
    }

    /**
     * The portal account: the applicant's own password, and approved at once
     * only when the office has said registrations are accepted automatically.
     */
    private function createAccount(array $data, Resident $resident): User
    {
        return User::create([
            'resident_id'         => $resident->id,
            'email'               => $data['email'],
            'password'            => Hash::make($data['password']),
            'password_changed_at' => now(),
            'terms_accepted_at'   => now(),
            'privacy_accepted_at' => now(),
            'registration_status' => $this->autoAccept()
                ? RegistrationStatus::APPROVED
                : RegistrationStatus::PENDING,
            'is_active'           => true,
        ]);
    }

    /**
     * Place the resident: head of a vacant unit, or a member of the household
     * already registered at it. An existing head is never replaced.
     */
    private function attachHousehold(Property $property, array $data, Resident $resident, User $user): void
    {
        $household = $property->currentHousehold;

        if ($household !== null) {
            if (! (bool) ($data['link_household'] ?? false)) {
                throw new \RuntimeException('This address already has a household. Confirm the link before registering.');
            }

            HouseholdMember::create([
                'household_id' => $household->id,
                'resident_id'  => $resident->id,
                'is_head'      => false,
            ]);

            return;
        }

        $details = (array) ($data['household'] ?? []);

        $household = Household::create(Household::payloadFrom([
            'property_id'  => $property->id,
            'type'         => $details['type'] ?? HouseholdType::OWNER,
            'prev_address' => $details['prev_address'] ?? null,
        ]));

        $household->placeHead($resident->id, null);
    }

    // ===== Property =====

    /**
     * The unit the applicant claims: one already on file, or a new one the
     * office had not recorded yet, written from what the applicant supplied.
     */
    private function propertyFor(array $data): Property
    {
        $unit = (array) ($data['property'] ?? []);

        $property = Property::query()
            ->atUnit((int) $unit['block'], (int) $unit['lot'])
            ->lockForUpdate()
            ->first();

        return $property ?? $this->createProperty($unit);
    }

    /**
     * Write a unit the office had no record of under the association's own
     * address — the only address a property stands in.
     *
     * @param  array<string, mixed>  $unit  block, lot, sqm, type
     */
    private function createProperty(array $unit): Property
    {
        $address = Address::query()->primary()->first();

        if ($address === null) {
            throw new \RuntimeException('The association address is not recorded.');
        }

        return Property::create([
            'block'      => (int) $unit['block'],
            'lot'        => (int) $unit['lot'],
            'address_id' => $address->id,
            'sqm'        => $this->filled($unit, 'sqm'),
            'type'       => $this->filled($unit, 'type'),
        ]);
    }

    /**
     * One submitted value, without the spaces a form leaves behind.
     *
     * @param  array<string, mixed>  $data
     */
    private function filled(array $data, string $key): ?string
    {
        $value = trim((string) ($data[$key] ?? ''));

        return $value === '' ? null : $value;
    }

    // ===== Side effects =====

    /**
     * Mail the applicant and tell the office, once the write has committed; a
     * mail server that is down must not unwind a registration already saved.
     */
    private function sendSideEffects(User $user): void
    {
        try {
            Mail::to($user->email)->send(new RegistrationReceived($user));
        } catch (\Throwable $exception) {
            Log::error('Registration email failed: ' . get_class($exception));
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

    private function autoAccept(): bool
    {
        return SettingsHelper::get(
            AuthenticationSettingsQuery::AUTO_ACCEPT_REGISTRATION,
            AuthenticationSettingsQuery::GROUP,
            '0'
        ) === '1';
    }

    /**
     * Remove the ID written before a failed transaction, so a refused
     * registration leaves no file behind.
     */
    private function discardStoredId(): void
    {
        if ($this->storedIdPath !== null) {
            StorageHelper::deletePrivate($this->storedIdPath);
        }
    }
}
