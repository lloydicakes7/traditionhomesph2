<?php

namespace App\Models;

use App\Models\Household\Household;
use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    protected $fillable = [
        'block',
        'lot',
        'address_id',
        'sqm',
        'type',
        'picture',
    ];

    protected $casts = [
        'sqm' => 'decimal:2',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * The household occupying this unit.
     */
    public function currentHousehold(): HasOne
    {
        return $this->hasOne(Household::class, 'property_id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The current occupant, through the household relationship.
     *
     * @return \App\Models\User|null
     */
    public function getOwnerAttribute(): ?User
    {
        return $this->currentHousehold?->user;
    }

    /**
     * The resident who holds this unit's account.
     *
     */
    public function getHeadResidentIdAttribute(): ?int
    {
        return $this->currentHousehold?->head_resident?->id;
    }

    /**
     * The unit as the association writes it: "Blk 7 Lot 12".
     */
    public function getBlockLotLabelAttribute(): string
    {
        if ($this->block === null || $this->lot === null) {
            return 'None';
        }

        return 'Blk ' . $this->block . ' Lot ' . $this->lot;
    }

    /**
     * The unit as a person writes an address.
     */
    public function getFullAddressLabelAttribute(): string
    {
        $address = $this->address;

        return implode(' · ', array_filter([
            $this->block_lot_label,
            $address?->subdivision,
            $address?->street,
            $address?->city,
        ]));
    }

    /**
     * The combined SQM / Type display string with abbreviated type.
     */
    public function getSqmTypeDisplayAttribute(): string
    {
        $parts = [];

        if ($this->sqm !== null) {
            $parts[] = number_format($this->sqm, 2) . ' sqm';
        }

        if ($this->type) {
            $parts[] = $this->type_enum ? $this->type_enum->shortLabel() : $this->type;
        }

        return ! empty($parts) ? implode(' / ', $parts) : '—';
    }

    /**
     * The PropertyType enum instance for this property's type.
     */
    public function getTypeEnumAttribute(): ?PropertyType
    {
        return $this->type ? PropertyType::tryFrom($this->type) : null;
    }

    /**
     * The full URL for the property picture, or null while none is stored.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->picture
            ? Storage::disk('public')->url($this->picture)
            : null;
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * The unit carrying this block and lot; the pair is unique.
     */
    public function scopeAtUnit(Builder $query, int $block, int $lot): Builder
    {
        return $query->where('block', $block)->where('lot', $lot);
    }

    /**
     * Only the units a household occupies.
     *
     */
    public function scopeWithHousehold(Builder $query): Builder
    {
        return $query->whereHas('currentHousehold');
    }

    /**
     * Units without a household, plus the given household (for edit forms).
     */
    public function scopeAvailableFor(Builder $query, ?int $householdId = null): Builder
    {
        return $query->where(function ($q) use ($householdId) {
            $q->whereDoesntHave('currentHousehold');

            if ($householdId) {
                $q->orWhereHas('currentHousehold', function ($h) use ($householdId) {
                    $h->where('households.id', $householdId);
                });
            }
        });
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * The property columns out of a submitted form: blank-to-NULL, and the
     * block and lot stored as the numbers the office keys them by.
     */
    public static function payloadFrom(array $data): array
    {
        $data['sqm'] = ($data['sqm'] ?? '') !== '' ? $data['sqm'] : null;

        foreach (['block', 'lot'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== '' && $data[$field] !== null) {
                $data[$field] = (int) $data[$field];
            }
        }

        return $data;
    }

    /**
     * The occupied units as a select's options, with who holds each —
     * "Blk 7 Lot 12 · Juana Dela Cruz".
     *
     * @return array<int, string>
     */
    public static function selectOptions(): array
    {
        return static::query()
            ->withHousehold()
            ->with('currentHousehold.user.resident')
            ->orderBy('block')
            ->orderBy('lot')
            ->get()
            ->mapWithKeys(function (Property $property) {
                return [
                    $property->id => $property->block_lot_label
                        . ' · ' . ($property->owner?->name ?: 'No household on file'),
                ];
            })
            ->all();
    }
}
