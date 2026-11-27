<?php

namespace App\Actions\Homeowners\Profiles;

use App\Enums\HouseholdLinkStatus;
use App\Exceptions\ProfileSubmissionException;
use App\Enums\HouseholdType;
use App\Enums\ProfileStatus;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Household\HouseholdLinkRequest;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\HouseholdLinkRequested;
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
        if ($account->profile_status?->isSubmitted()) {
            throw new \RuntimeException('Your information is already waiting for the office.');
        }

        $resident = $account->resident;

        if ($resident === null) {
            throw new \RuntimeException('Your account has no resident record yet.');
        }

        $link = DB::transaction(function () use ($account, $resident, $data, $idFile) {
            $resident->update(Resident::payloadFrom((array) $data['form']));

            $this->storeId($resident, $idFile);

            $unit = Property::normalizeUnit((array) $data['property']);
            $property = $this->propertyFor($unit['block'], $unit['lot'], $unit);

            $link = $this->placeInHousehold($property, $resident, (array) ($data['household'] ?? []));

            $account->forceFill([
                'profile_status'               => ProfileStatus::SUBMITTED,
                'profile_submitted_at'         => now(),
                'profile_verified_at'          => null,
                'profile_verified_by_admin_id' => null,
                'profile_rejection_note'       => null,
            ])->save();

            return $link;
        });

        $this->tellTheHead($link);
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
    private function propertyFor(string $block, string $lot, array $details): Property
    {
        $property = Property::query()->atUnit($block, $lot)->first();

        if ($property !== null) {
            return $property;
        }

        $address = Address::query()->primary()->first();

        if ($address === null) {
            throw new ProfileSubmissionException('The office has not recorded the association address yet. Please contact the office before you submit your profile.');
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
     * A household the resident already heads moves with them; a household they
     * only belong to, or one another family already holds, is refused.
     *
     * @param  array<string, mixed>  $details
     */
    private function placeInHousehold(Property $property, Resident $resident, array $details): ?HouseholdLinkRequest
    {
        $relationship = $details['relationship'] ?? null;
        $beHead = (bool) ($details['be_head'] ?? false);
        $household = $property->currentHousehold;
        $current = $resident->household;

        if ($current !== null && $household !== null && (int) $current->id === (int) $household->id) {
            return $this->keepPlacement($household, $resident, $relationship, $beHead);
        }

        if ($current !== null && ! $this->isHeadOf($current, $resident)) {
            throw new ProfileSubmissionException('You already belong to another household. Ask the office to move you to this unit.');
        }

        if ($household === null) {
            $household = $current !== null
                ? $this->moveHousehold($current, $property, $details)
                : Household::create(Household::payloadFrom([
                    'property_id'  => $property->id,
                    'type'         => $details['type'] ?? HouseholdType::OWNER,
                    'prev_address' => $details['prev_address'] ?? null,
                ]));

            $household->placeHead($resident->id, $relationship);

            return null;
        }

        if ($current !== null) {
            throw new ProfileSubmissionException('This unit already belongs to another household. Keep the unit on file for your household, or ask the office to move it.');
        }

        if (! $household->hasHead() || ! $this->headHasAccount($household)) {
            if ($beHead || ! $household->hasHead()) {
                $household->placeHead($resident->id, $relationship);
            } else {
                $this->placeMember($household, $resident, $relationship);
            }

            return null;
        }

        return $this->requestTheHead($household, $resident, $relationship, $beHead);
    }

    /**
     * An existing member asking to take over a household someone else heads.
     */
    private function keepPlacement(Household $household, Resident $resident, ?string $relationship, bool $beHead): ?HouseholdLinkRequest
    {
        if (! $beHead || $this->isHeadOf($household, $resident)) {
            return null;
        }

        if (! $this->headHasAccount($household)) {
            $household->placeHead($resident->id, $relationship);

            return null;
        }

        $relationship = $relationship ?: $household->memberRecords()
            ->where('resident_id', $resident->id)
            ->value('relationship');

        return $this->requestTheHead($household, $resident, $relationship, true);
    }

    private function placeMember(Household $household, Resident $resident, ?string $relationship): void
    {
        HouseholdMember::create([
            'household_id' => $household->id,
            'resident_id'  => $resident->id,
            'relationship' => HouseholdMember::valueFrom($relationship),
            'is_head'      => false,
        ]);
    }

    private function headHasAccount(Household $household): bool
    {
        return $household->head_resident?->user !== null;
    }

    private function moveHousehold(Household $household, Property $property, array $details): Household
    {
        $household->update(Household::payloadFrom([
            'property_id'  => $property->id,
            'type'         => $details['type'] ?? $household->type,
            'prev_address' => $details['prev_address'] ?? null,
        ]));

        return $household;
    }

    private function isHeadOf(Household $household, Resident $resident): bool
    {
        return (int) $household->head_resident?->id === (int) $resident->id;
    }

    private function requestTheHead(Household $household, Resident $resident, ?string $relationship, bool $wantsHead): ?HouseholdLinkRequest
    {
        $link = HouseholdLinkRequest::query()
            ->where('household_id', $household->id)
            ->where('resident_id', $resident->id)
            ->first();

        $waiting = $link !== null && $link->isPending();

        $link = HouseholdLinkRequest::updateOrCreate(
            ['household_id' => $household->id, 'resident_id' => $resident->id],
            [
                'relationship' => HouseholdMember::valueFrom($relationship),
                'wants_head'   => $wantsHead,
                'status'       => HouseholdLinkStatus::PENDING,
                'decided_at'   => null,
            ]
        );

        return $waiting ? null : $link;
    }

    // ===== Side effects =====

    private function tellTheHead(?HouseholdLinkRequest $link): void
    {
        $head = $link?->headAccount();

        if ($head === null) {
            return;
        }

        try {
            $this->notifications->toHomeowner($head, new HouseholdLinkRequested($link));
        } catch (\Throwable $exception) {
            Log::error('Household link notification failed: ' . get_class($exception));
        }
    }

    private function tellTheOfficers(User $account): void
    {
        try {
            $this->notifications->toOfficers(new ProfileSubmittedForVerification($account));
        } catch (\Throwable $exception) {
            Log::error('Profile submission notification failed: ' . get_class($exception));
        }
    }
}
