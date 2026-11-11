<?php

namespace App\Models\Household;

use App\Enums\HouseholdRelationship;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdMember extends Model
{
    protected $fillable = [
        'household_id',
        'resident_id',
        'relationship',
        'is_head',
    ];

    protected $casts = [
        'is_head' => 'boolean',
    ];

    // ===== Relationships =====

    /**
     * The household this member belongs to.
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    /**
     * The resident record for this household member.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    // ===== Scopes =====

    public function scopeHead(Builder $query): Builder
    {
        return $query->where('is_head', true);
    }

    // ===== Display =====

    /**
     * The member's full name, read from the resident record.
     */
    public function getFullNameAttribute(): string
    {
        return $this->resident?->full_name ?? '—';
    }

    /**
     * The relationship to the family head as a display string.
     */
    public function getRelationshipLabelAttribute(): string
    {
        return self::labelFor($this->relationship);
    }

    // ===== Rules =====

    /**
     * The display label for a stored relationship: the listed relationship's
     * label, or the member's own words when the office typed something else.
     */
    public static function labelFor(?string $relationship): string
    {
        $relationship = trim((string) $relationship);

        if ($relationship === '') {
            return '—';
        }

        return HouseholdRelationship::tryFrom($relationship)?->label() ?? $relationship;
    }

    /**
     * The value to store for a submitted relationship: a listed relationship's
     * value when the text names one, otherwise the text itself.
     */
    public static function valueFrom(?string $relationship): ?string
    {
        $relationship = trim((string) $relationship);

        if ($relationship === '') {
            return null;
        }

        $key = strtolower(str_replace([' ', '-'], '_', $relationship));

        if (in_array($key, HouseholdRelationship::values(), true)) {
            return $key;
        }

        foreach (HouseholdRelationship::labels() as $value => $label) {
            if (strcasecmp($label, $relationship) === 0) {
                return $value;
            }
        }

        return $relationship;
    }

    // ===== Placements =====

    /**
     * Whether the resident already belongs to a household — a resident lives
     * in one household only.
     */
    public static function residentIsPlaced(int $residentId, ?int $householdId = null): bool
    {
        return static::query()
            ->where('resident_id', $residentId)
            ->when($householdId, function ($query) use ($householdId) {
                $query->where('household_id', '!=', $householdId);
            })
            ->exists();
    }

    /**
     * Whether a resident is already the head of another household.
     */
    public static function residentIsHeadElsewhere(int $residentId, ?int $householdId = null): bool
    {
        return static::query()
            ->head()
            ->where('resident_id', $residentId)
            ->when($householdId, function ($query) use ($householdId) {
                $query->where('household_id', '!=', $householdId);
            })
            ->exists();
    }
}
