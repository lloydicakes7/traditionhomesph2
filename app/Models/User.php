<?php

namespace App\Models;

use App\Casts\EnumCast;
use App\Enums\RegistrationStatus;
use App\Models\Amenity\AmenityBooking;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'resident_id', 'email', 'password', 'password_changed_at',
        'terms_accepted_at', 'privacy_accepted_at', 'is_active', 'registration_status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at'   => 'datetime',
        'password_changed_at' => 'datetime',
        'terms_accepted_at'   => 'datetime',
        'privacy_accepted_at' => 'datetime',
        'last_login_at'       => 'datetime',
        'is_active'           => 'boolean',
        'registration_status' => EnumCast::class . ':' . RegistrationStatus::class,
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * Get the resident record associated with this user account.
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    /**
     * Get the household this account's resident lives in.
     *
     * The household no longer names an account: it names the people who live
     * in it, and the head is the account holder. The link is therefore the
     * resident's membership, not a column on households.
     */
    public function household(): HasOneThrough
    {
        return $this->hasOneThrough(
            Household::class,
            HouseholdMember::class,
            'resident_id',
            'id',
            'resident_id',
            'household_id'
        );
    }

    /**
     * The bills issued to this account — every monthly due, invoice and amenity
     * charge the homeowner owes or has settled.
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * The payments this account has made.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The amenity reservations filed for this account.
     */
    public function amenityBookings(): HasMany
    {
        return $this->hasMany(AmenityBooking::class);
    }

    /**
     * The credit this account holds ahead of a bill.
     */
    public function advanceDeposits(): HasMany
    {
        return $this->hasMany(AdvanceDeposit::class);
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * Get the full name from the associated resident.
     */
    public function getNameAttribute(): string
    {
        return $this->resident?->full_name ?? '—';
    }

    /**
     * Get profile image URL for the user (homeowner portal use).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->resident?->photo_url;
    }

    /**
     * The alert a homeowner's account earns on the Homeowners list.
     *
     * One row carries one background, so the account's outstanding items are
     * ranked by urgency and the highest one wins: an active violation outranks
     * an unpaid bill, which outranks a request still waiting for the office.
     * The counts are added to the list query by HomeownerListQuery and are absent
     * on an account read anywhere else, which is why each is read defensively
     * through the raw attributes.
     *
     * @return string|null  A Bootstrap theme name (`danger`, `info`, `warning`), or null
     */
    public function getRowAlertAttribute(): ?string
    {
        if ((int) ($this->attributes['active_violation_count'] ?? 0) > 0) {
            return 'danger';
        }

        if ((int) ($this->attributes['unpaid_bill_count'] ?? 0) > 0) {
            return 'info';
        }

        if ((int) ($this->attributes['pending_request_count'] ?? 0) > 0) {
            return 'warning';
        }

        return null;
    }

    /**
     * Whether the registration is still waiting for the office to accept it.
     */
    public function isPendingRegistration(): bool
    {
        return $this->registration_status?->value === RegistrationStatus::PENDING;
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    public function scopeApprovedRegistration(Builder $query): Builder
    {
        return $query->where('registration_status', RegistrationStatus::APPROVED);
    }

    public function scopePendingRegistration(Builder $query): Builder
    {
        return $query->where('registration_status', RegistrationStatus::PENDING);
    }

    public function scopeAvailableHouseholdsFor(Builder $query, ?int $userId = null): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereDoesntHave('household');

            if ($userId) {
                $q->orWhere('id', $userId);
            }
        });
    }
}

