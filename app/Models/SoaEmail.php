<?php

namespace App\Models;

use App\Enums\SoaStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One statement of account prepared for one homeowner account.
 *
 * The office prepares a period's statements, the system mails them, and this
 * record says what went where: the address it was sent to, the period it
 * covered, and — when the mail bounced — why, so a failed statement can be
 * retried without re-preparing the batch. The statement is addressed to the
 * account; the unit it is about is reached through the account's household.
 */
class SoaEmail extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'period',
        'status',
        'sent_at',
        'error',
        'sent_by_admin_id',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The homeowner account the statement is addressed to, and through it the
     * unit it is about.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The officer who prepared the statement.
     */
    public function sentByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'sent_by_admin_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the address, the unit and the period.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('email', 'like', $like)
                ->orWhere('period', 'like', $like)
                ->orWhereHas('user.household.property', function ($property) use ($like) {
                    $property->where('block', 'like', $like)
                        ->orWhere('lot', 'like', $like);
                });
        });
    }

    /**
     * Only the statements in one status.
     */
    public function scopeWithStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Only the statements of one period, "Y-m".
     */
    public function scopeForPeriod($query, ?string $period)
    {
        return $period ? $query->where('period', $period) : $query;
    }

    /**
     * Newest first — the ordering the SOA list opens on.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The SoaStatus enum instance for this statement.
     */
    public function getStatusEnumAttribute(): ?SoaStatus
    {
        return $this->status ? SoaStatus::tryFrom($this->status) : null;
    }

    /**
     * The period as a record writes it: "June 2026".
     */
    public function getPeriodDisplayAttribute(): string
    {
        if (! $this->period) {
            return 'All periods';
        }

        return Carbon::createFromFormat('Y-m', $this->period)->format('F Y');
    }

    /**
     * The unit the statement is about, reached through the account.
     */
    public function getPropertyLabelAttribute(): string
    {
        return $this->user?->household?->property?->block_lot_label ?? '—';
    }

    /**
     * The unit itself, when one is on file.
     */
    public function getPropertyAttribute()
    {
        return $this->user?->household?->property;
    }

    /**
     * Who the statement is addressed to.
     */
    public function getIssuedToNameAttribute(): string
    {
        return $this->user?->name ?: '—';
    }

    /**
     * When the statement went out, as a list writes it.
     */
    public function getSentAtDisplayAttribute(): string
    {
        return $this->sent_at ? $this->sent_at->format('M j, Y g:i A') : 'None';
    }
}
