<?php

namespace App\Models;

use App\Enums\CivilStatus;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Enums\EducationalAttainment;
use App\Enums\SectorType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Storage;

class Resident extends Model
{
    /**
     * The resident details a Membership Form prints, and the label each one
     * reads as when the office still has to complete it.
     *
     * Age is not listed: it is read from the birth date, so requiring the birth
     * date requires the age with it. Religion is optional, and an attainment is
     * optional too: a form is never refused for either, and an empty one prints
     * blank.
     */
    const MEMBERSHIP_FORM_REQUIRED = [
        'surname'      => 'Surname',
        'first_name'   => 'First Name',
        'sex'          => 'Sex',
        'citizenship'  => 'Citizenship',
        'birth_date'   => 'Birthdate',
        'birth_place'  => 'Birthplace',
        'civil_status' => 'Civil Status',
        'mobile_no'    => 'Mobile Phone Number',
    ];

    protected $table = 'residents';

    protected $fillable = [
        'surname',
        'first_name',
        'middle_name',
        'suffix',
        'birth_date',
        'birth_place',
        'sex',
        'civil_status',
        'spouse_name',
        'citizenship',
        'religion',
        'occupation',
        'mobile_no',
        'educational_attainment',
        'sectors',
        'profile_photo_path',
        'valid_id_path',
        'philsys_card_no',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'sectors' => 'array',
    ];

    /**
     * Columns that are never exposed when a resident is serialized.
     */
    protected $hidden = [
        'valid_id_path',
    ];

    protected $appends = [
        'full_name',
        'name',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The user account linked to this resident.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'resident_id');
    }

    /**
     * The household memberships held by this resident.
     */
    public function householdMemberships(): HasMany
    {
        return $this->hasMany(HouseholdMember::class, 'resident_id');
    }

    /**
     * The household this resident lives in, and through it the unit.
     *
     */
    public function household(): HasOneThrough
    {
        return $this->hasOneThrough(
            Household::class,
            HouseholdMember::class,
            'resident_id',
            'id',
            'id',
            'household_id'
        );
    }

    /**
     * Residents with no household yet, plus the given resident (for edit forms).
     */
    public function scopeAvailableForHousehold(Builder $query, ?int $residentId = null): Builder
    {
        return $query->where(function ($q) use ($residentId) {
            $q->whereDoesntHave('householdMemberships');

            if ($residentId) {
                $q->orWhere($q->getModel()->getQualifiedKeyName(), $residentId);
            }
        });
    }

    /**
     * The unit this resident lives in, or null while none is on file.
     */
    public function unit(): ?Property
    {
        return $this->household?->property
            ?: $this->user?->household?->property;
    }

    // ===== Membership Form =====

    /**
     * What a Membership Form still needs from this resident's own records.
     *
     * The form is printed from the profile and the household, never typed in,
     * so the blanks the office still has to fill are named here — the office
     * completes them on the resident, household and unit forms.
     *
     * A blank the homeowner (or the office) has already supplied for the form is
     * not missing: `$supplied` names the resident columns the form carries.
     *
     * @param  array<int, string>|null  $supplied  Resident columns supplied on the form
     * @return array<string, string> The missing columns, each with its label
     */
    public function membershipFormMissing(?array $supplied = []): array
    {
        $supplied = $supplied ?? [];
        $missing = [];

        foreach (self::MEMBERSHIP_FORM_REQUIRED as $column => $label) {
            if (in_array($column, $supplied, true)) {
                continue;
            }

            if ($this->{$column} === null || trim((string) $this->{$column}) === '') {
                $missing[$column] = $label;
            }
        }

        $unit = $this->unit();

        foreach (['block' => 'Block', 'lot' => 'Lot'] as $column => $label) {
            if (! $unit || $unit->{$column} === null || trim((string) $unit->{$column}) === '') {
                $missing[$column] = $label;
            }
        }

        return $missing;
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The sectoral tags this resident carries, as plain values.
     *
     * @return array<int, string>
     */
    public function sectorValues(): array
    {
        return (array) ($this->sectors ?? []);
    }

    /**
     * The full name (first, middle, surname and suffix).
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->surname,
        ]);

        $full = implode(' ', $parts);

        if ($this->suffix) {
            $full .= ', ' . $this->suffix;
        }

        return $full ?: '—';
    }

    /**
     * Alias so code that reads `$resident->name` still works.
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * The full URL for the profile photo, or null while none is set.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : null;
    }

    /**
     * The age of the resident, from the birth date.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }

    /**
     * The PhilSys Card Number as the form prints it.
     */
    public function getPhilsysDisplayAttribute(): string
    {
        return $this->philsys_card_no ?: '';
    }

    /**
     * The sex as a display string (capitalized first letter).
     */
    public function getSexLabelAttribute(): string
    {
        return $this->sex ? ucfirst($this->sex) : '—';
    }

    /**
     * The civil status as a display string (capitalized first letter).
     */
    public function getCivilStatusLabelAttribute(): string
    {
        return $this->civil_status ? ucfirst($this->civil_status) : '—';
    }

    /**
     * The educational attainment as a display label.
     */
    public function getEducationalAttainmentLabelAttribute(): string
    {
        return EducationalAttainment::tryFrom($this->educational_attainment)?->label()
            ?? $this->educational_attainment
            ?? '—';
    }

    // =====================================================================
    // Payload
    // =====================================================================

    /**
     * The resident columns out of a submitted form, with blanks read as null.
     */
    public static function payloadFrom(array $data): array
    {
        $nullable = [
            'middle_name', 'suffix', 'mobile_no',
            'birth_place', 'religion', 'occupation',
            'spouse_name', 'philsys_card_no', 'educational_attainment',
        ];

        foreach ($nullable as $field) {
            if (array_key_exists($field, $data) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if (! CivilStatus::isMarried($data['civil_status'] ?? '')) {
            $data['spouse_name'] = null;
        }

        if (array_key_exists('sectors', $data)) {
            $data['sectors'] = self::sectorValuesFrom($data['sectors'] ?? []);
        }

        return $data;
    }

    /**
     * The sectoral tags a submitted form asked for, once each and known to the
     * enum, so a value the form never offered is dropped rather than stored.
     *
     * @return array<int, string>
     */
    public static function sectorValuesFrom($sectors): array
    {
        return array_values(array_unique(array_filter(
            (array) $sectors,
            function ($sector): bool {
                return SectorType::tryFrom((string) $sector) !== null;
            }
        )));
    }
}
