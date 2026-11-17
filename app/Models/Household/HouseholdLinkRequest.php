<?php

namespace App\Models\Household;

use App\Casts\EnumCast;
use App\Enums\HouseholdLinkStatus;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdLinkRequest extends Model
{
    protected $fillable = [
        'household_id',
        'resident_id',
        'relationship',
        'status',
        'decided_at',
    ];

    protected $casts = [
        'status'     => EnumCast::class . ':' . HouseholdLinkStatus::class,
        'decided_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', HouseholdLinkStatus::PENDING);
    }

    public function getRelationshipLabelAttribute(): string
    {
        return HouseholdMember::labelFor($this->relationship);
    }

    public function headAccount(): ?User
    {
        return $this->household?->head_resident?->user;
    }

    public function isPending(): bool
    {
        return (bool) $this->status?->isPending();
    }
}
