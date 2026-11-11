<?php

namespace App\Models\Account;

use App\Models\Admin;
use App\Casts\EnumCast;
use App\Enums\AccountingPeriodStatus;
use App\Utility\PeriodHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * One month the books are closed for — the month-end and year-end lock.
 *
 * A month with no row is open; a month with a closed row may not be posted
 * into until it is reopened, which is what makes a set of financial statements
 * mean something. The row keeps who closed it and when, and who reopened it and
 * when, because a lock that leaves no trace is not a lock.
 *
 * The rule itself is this model's own (`locksDate()`), asked before anything is
 * posted, so no module can slip an entry into a closed month. A period is
 * identified by its month (`period`, "Y-m"), so the register and the lock always
 * name the same thing.
 */
class AccountingPeriod extends Model
{
    protected $fillable = [
        'period',
        'status',
        'closed_at',
        'closed_by_admin_id',
        'reopened_at',
        'reopened_by_admin_id',
        'notes',
    ];

    protected $casts = [
        'status'      => EnumCast::class . ':' . AccountingPeriodStatus::class,
        'closed_at'   => 'datetime',
        'reopened_at' => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The officer who closed the period.
     */
    public function closedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'closed_by_admin_id');
    }

    /**
     * The officer who reopened it.
     */
    public function reopenedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'reopened_by_admin_id');
    }

    // =====================================================================
    // The lock
    // =====================================================================

    /**
     * Whether a date falls in a closed month — the rule the books ask before
     * they write anything.
     */
    public static function locksDate($date): bool
    {
        return static::locksPeriod(PeriodHelper::of($date));
    }

    /**
     * Whether a month is closed.
     */
    public static function locksPeriod(?string $period): bool
    {
        if (! $period) {
            return false;
        }

        return static::query()
            ->where('period', $period)
            ->closed()
            ->exists();
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Only the months the books are closed for.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', AccountingPeriodStatus::CLOSED);
    }

    /**
     * Only the months left open.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', AccountingPeriodStatus::OPEN);
    }

    // =====================================================================
    // Display and state
    // =====================================================================

    /**
     * The month as a heading writes it: "June 2026".
     */
    public function getPeriodLabelAttribute(): string
    {
        return PeriodHelper::label($this->period);
    }

    /**
     * The month short, for a narrow column: "Jun 2026".
     */
    public function getPeriodShortAttribute(): string
    {
        return PeriodHelper::shortLabel($this->period);
    }

    /**
     * The status's label, safe when the stored value is unknown.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? $this->status->label() : 'Open';
    }

    /**
     * The status's badge theme, safe when the stored value is unknown.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status ? $this->status->badgeTheme() : 'success';
    }

    /**
     * Whether this period refuses new postings.
     */
    public function isClosed(): bool
    {
        return $this->status ? $this->status->isClosed() : false;
    }
}
