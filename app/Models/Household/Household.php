<?php

namespace App\Models\Household;

use App\Enums\HouseholdType;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use App\Utility\TextHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = [
        'property_id',
        'type',
        'prev_address',
    ];

    // ===== Relationships =====

    /**
     * The property this household is associated with.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * The members (residents) of this household.
     */
    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(
            Resident::class,
            HouseholdMember::class,
            'household_id',
            'id',
            'id',
            'resident_id'
        );
    }

    /**
     * The household member records directly.
     */
    public function memberRecords(): HasMany
    {
        return $this->hasMany(HouseholdMember::class, 'household_id', 'id');
    }

    /**
     * The member record flagged as the family head.
     */
    public function headMember(): HasOne
    {
        return $this->hasOne(HouseholdMember::class, 'household_id')->where('is_head', true);
    }

    /**
     * The account held by the head of the family.
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            HouseholdMember::class,
            'household_id',
            'resident_id',
            'id',
            'resident_id'
        )->where('household_members.is_head', true);
    }

    /**
     * Match every word of the search against any member's name, or the unit's
     * block and lot.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $terms = TextHelper::searchTerms($search);

        if ($terms === []) {
            return $query;
        }

        return $query->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                $query->where(function ($query) use ($term) {
                    $query->whereHas('memberRecords.resident', function ($resident) use ($term) {
                        $resident->where('first_name', 'like', "%{$term}%")
                            ->orWhere('middle_name', 'like', "%{$term}%")
                            ->orWhere('surname', 'like', "%{$term}%")
                            ->orWhere('suffix', 'like', "%{$term}%");
                    })->orWhereHas('property', function ($property) use ($term) {
                        $property->where('block', 'like', "%{$term}%")
                            ->orWhere('lot', 'like', "%{$term}%");
                    });
                });
            }
        });
    }

    // ===== Display =====

    /**
     * The display name for the household: the head's surname and "Family".
     */
    public function getDisplayNameAttribute(): string
    {
        $surname = $this->head_resident?->surname;

        return $surname ? $surname . ' Family' : "Household #{$this->id}";
    }

    /**
     * The household's tenure, as the RBI Form A ticks it: how the unit is held.
     */
    public function getTenureLabelAttribute(): string
    {
        return $this->type_enum?->label() ?? 'Not recorded';
    }

    /**
     * The household's name, as the RBI forms print it.
     */
    public function getRbiNameAttribute(): string
    {
        return $this->display_name;
    }

    /**
     * The HouseholdType enum instance for this household's type — how the unit
     * is held.
     */
    public function getTypeEnumAttribute(): ?HouseholdType
    {
        return $this->type ? HouseholdType::tryFrom($this->type) : null;
    }

    /**
     * The resident the family marks as its head.
     */
    public function getHeadResidentAttribute(): ?Resident
    {
        return $this->headMember?->resident;
    }

    /**
     * The head's relationship to the family, as the RBI form prints it.
     */
    public function getHeadRelationshipLabelAttribute(): ?string
    {
        return $this->headMember?->relationship_label;
    }

    /**
     * How many families live in the unit — the members on record.
     */
    public function getFamiliesCountAttribute(): int
    {
        return $this->member_count;
    }

    /**
     * The property display string: "7/12".
     */
    public function getPropertyDisplayAttribute(): string
    {
        return $this->property ? $this->property->block_lot_label : 'None';
    }

    /**
     * The member records with the family head first.
     */
    public function getOrderedMembersAttribute()
    {
        return $this->memberRecords->sortByDesc('is_head')->values();
    }

    /**
     * The number of members on the household.
     */
    public function getMemberCountAttribute(): int
    {
        return $this->memberRecords->count();
    }

    /**
     * Whether the household already has a member flagged as its head.
     */
    public function hasHead(): bool
    {
        return $this->memberRecords->contains('is_head', true);
    }

    // ===== Rules =====

    /**
     * Keep the previous address in the case the records are written in.
     */
    public function setPrevAddressAttribute($value): void
    {
        $this->attributes['prev_address'] = TextHelper::upper(self::blankToNull($value));
    }

    /**
     * The household columns out of a submitted form, blanks read as null.
     */
    public static function payloadFrom(array $data): array
    {
        return [
            'property_id'  => $data['property_id'],
            'type'         => $data['type'],
            'prev_address' => TextHelper::upper(self::blankToNull($data['prev_address'] ?? null)),
        ];
    }

    /**
     * Whether a unit is free, or already held by the household being edited.
     */
    public static function propertyIsFree(int $propertyId, ?int $householdId = null): bool
    {
        return ! static::query()
            ->where('property_id', $propertyId)
            ->when($householdId, function ($query) use ($householdId) {
                $query->where('id', '!=', $householdId);
            })
            ->exists();
    }

    /**
     * Make one member the family head, clearing the mark from every other.
     */
    public function designateHead(int $residentId): void
    {
        $this->assertHeadIsAdult($residentId);

        $this->memberRecords()->update(['is_head' => false]);

        $this->memberRecords()
            ->where('resident_id', $residentId)
            ->update(['is_head' => true]);
    }

    /**
     * Make one resident the family head and write their relationship.
     */
    public function placeHead(int $residentId, ?string $relationship): void
    {
        $this->assertHeadIsAdult($residentId);

        $this->memberRecords()->update(['is_head' => false]);

        $this->memberRecords()->updateOrCreate(
            ['resident_id' => $residentId],
            [
                'relationship' => HouseholdMember::valueFrom($relationship),
                'is_head'      => true,
            ]
        );
    }

    /**
     * A household is headed by an adult: nobody below 18 may take the role.
     */
    private function assertHeadIsAdult(int $residentId): void
    {
        $resident = Resident::find($residentId);

        if ($resident === null || ! $resident->canHeadHousehold()) {
            throw new \RuntimeException('A household head must be at least '.Resident::MINIMUM_HEAD_AGE.' years old.');
        }
    }

    private static function blankToNull($value)
    {
        return $value === '' || $value === null ? null : $value;
    }
}
