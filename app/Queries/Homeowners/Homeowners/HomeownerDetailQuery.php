<?php

namespace App\Queries\Homeowners\Homeowners;

use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Models\Bill\Bill;
use App\Models\Complaint;
use App\Models\Household\Household;
use App\Models\Payment;
use App\Models\Request as RequestModel;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Collection;
use App\Queries\Homeowners\Households\HouseholdListQuery;
use App\Queries\Homeowners\Residents\ResidentListQuery;

class HomeownerDetailQuery
{
    private ResidentListQuery $residents;

    private HouseholdListQuery $households;

    public function __construct(ResidentListQuery $residents, HouseholdListQuery $households)
    {
        $this->residents = $residents;
        $this->households = $households;
    }

    // ===== One homeowner =====

    public function view(int $residentId): array
    {
        $resident = $this->residents->find($residentId);

        $householdData = null;

        if ($resident->user) {
            $household = $this->households->forUser($resident->user->id);

            if ($household) {
                $household->loadMissing(['property.address', 'memberRecords.resident']);

                $householdData = [
                    'type_label'          => HouseholdType::labels()[$household->type] ?? $household->type,
                    'property_label'      => $household->property
                        ? "{$household->property->block_lot_label}"
                        : '—',
                    'property_address'    => $household->property?->address?->full_address ?? '—',
                    'property_type_label' => $household->property
                        ? (PropertyType::tryFrom($household->property->type)?->label() ?? $household->property->type ?? '—')
                        : '—',
                    'prev_address'        => $household->prev_address,
                    'members'             => [],
                ];

                foreach ($household->memberRecords as $record) {
                    $householdData['members'][] = [
                        'id'           => $record->id,
                        'full_name'    => $record->resident?->full_name ?? '—',
                        'relationship' => $record->relationship ? ucfirst($record->relationship) : 'Others',
                        'age'          => $record->resident?->birth_date ? $record->resident->birth_date->age : null,
                    ];
                }
            }
        }

        return [
            'resident'  => $resident,
            'household' => $householdData,
        ];
    }

    public function householdOf(int $residentId): ?Household
    {
        $resident = Resident::with('user')->findOrFail($residentId);

        return $resident->user ? $this->households->forUser($resident->user->id) : null;
    }

    // ===== Account activity =====

    /**
     * The requests the homeowner filed, newest first.
     */
    public function requests(int $residentId): Collection
    {
        return RequestModel::query()
            ->forResident($residentId)
            ->ordered()
            ->get();
    }

    /**
     * The complaints filed for or by the homeowner, newest first.
     */
    public function complaints(int $residentId): Collection
    {
        return Complaint::query()
            ->forResident($residentId)
            ->ordered()
            ->get();
    }

    /**
     * The bills issued to the account, newest first.
     */
    public function invoices(?int $userId): Collection
    {
        if (! $userId) {
            return new Collection();
        }

        return Bill::query()
            ->forUser($userId)
            ->ordered()
            ->get();
    }

    /**
     * The payments recorded against the account, newest first.
     */
    public function payments(?int $userId): Collection
    {
        if (! $userId) {
            return new Collection();
        }

        return Payment::query()
            ->forUser($userId)
            ->with('paymentMethod')
            ->ordered()
            ->get();
    }
}
