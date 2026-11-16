<?php

namespace App\Actions\Homeowners\Profiles;

use App\Enums\HouseholdType;
use App\Enums\ProfileStatus;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\ProfileSubmittedForVerification;
use App\Services\NotificationService;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Submit a resident's own profile for the office to check.
 *
 * One transaction writes the person, the valid ID, the unit and the household,
 * and puts the account in the verification queue. The office then checks that
 * the person is who they say, that the unit exists and that the household is the
 * one registered there.
 *
 * The office is told once the write has committed: a mail server that is down
 * must not unwind a submission already saved.
 */
class SubmitProfileVerification
{
    /** @var NotificationService */
    private $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    // ===== Handle =====

    /**
     * Write the profile and ask the office to verify it.
     *
     * @param  array<string, mixed>  $data  form, property (block/lot), household (type/prev_address)
     * @param  mixed  $idFile  The uploaded valid ID, or null when one is already on file
     */
    public function handle(User $account, array $data, $idFile): User
    {
        $resident = $account->resident;

        if ($resident === null) {
            throw new \RuntimeException('Your account has no resident record yet.');
        }

        DB::transaction(function () use ($account, $resident, $data, $idFile) {
            $resident->update(Resident::payloadFrom((array) $data['form']));

            $this->storeId($resident, $idFile);

            $property = $this->propertyFor((int) $data['property']['block'], (int) $data['property']['lot'], (array) $data['property']);

            $this->placeInHousehold($property, $resident, (array) ($data['household'] ?? []));

            $account->forceFill([
                'profile_status'               => ProfileStatus::SUBMITTED,
                'profile_submitted_at'         => now(),
                'profile_verified_at'          => null,
                'profile_verified_by_admin_id' => null,
                'profile_rejection_note'       => null,
            ])->save();
        });

        $this->tellTheOfficers($account);

        return $account;
    }

    // ===== Records =====

    /**
     * Keep the submitted ID on the private disk, replacing the one it supersedes.
     *
     * @param  mixed  $idFile
     */
    private function storeId(Resident $resident, $idFile): void
    {
        if (! $idFile) {
            if (! $resident->valid_id_path) {
                throw new \RuntimeException('A valid ID is required.');
            }

            return;
        }

        $path = StorageHelper::storePrivate($idFile, 'ids/residents');

        StorageHelper::deletePrivate($resident->valid_id_path);

        $resident->forceFill(['valid_id_path' => $path])->save();
    }

    /**
     * The unit the household lives in: one on file, or a new one under the
     * association's own address, carrying the floor area and type the resident
     * reported for it.
     *
     * @param  array<string, mixed>  $details
     */
    private function propertyFor(int $block, int $lot, array $details): Property
    {
        $property = Property::query()->atUnit($block, $lot)->first();

        if ($property !== null) {
            return $property;
        }

        $address = Address::query()->primary()->first();

        if ($address === null) {
            throw new \RuntimeException('The association address is not recorded.');
        }

        return Property::create(Property::payloadFrom([
            'block'      => $block,
            'lot'        => $lot,
            'address_id' => $address->id,
            'sqm'        => $details['sqm'] ?? null,
            'type'       => $details['type'] ?? null,
        ]));
    }

    /**
     * Put the resident in the household at the unit: its head when the unit has
     * none, and otherwise a member, or the head when the resident says they are.
     *
     * @param  array<string, mixed>  $details
     */
    private function placeInHousehold(Property $property, Resident $resident, array $details): void
    {
        $relationship = $details['relationship'] ?? null;
        $beHead = (bool) ($details['be_head'] ?? false);
        $household = $property->currentHousehold;

        if ($household === null) {
            $household = Household::create(Household::payloadFrom([
                'property_id'  => $property->id,
                'type'         => $details['type'] ?? HouseholdType::OWNER,
                'prev_address' => $details['prev_address'] ?? null,
            ]));

            $household->placeHead($resident->id, $relationship);

            return;
        }

        if ($household->memberRecords()->where('resident_id', $resident->id)->exists()) {
            if ($beHead) {
                $household->placeHead($resident->id, $relationship);
            }

            return;
        }

        if ($beHead || ! $household->hasHead()) {
            $household->placeHead($resident->id, $relationship);

            return;
        }

        HouseholdMember::create([
            'household_id' => $household->id,
            'resident_id'  => $resident->id,
            'relationship' => HouseholdMember::valueFrom($relationship),
            'is_head'      => false,
        ]);
    }

    // ===== Side effects =====

    private function tellTheOfficers(User $account): void
    {
        try {
            $this->notifications->toOfficers(new ProfileSubmittedForVerification($account));
        } catch (\Throwable $exception) {
            Log::error('Profile submission notification failed: ' . get_class($exception));
        }
    }
}
