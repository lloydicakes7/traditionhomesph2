<?php

namespace App\Models;

use App\Casts\EnumCast;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Utility\TimeHelper;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    protected $fillable = [
        'resident_id',
        'resident_name',
        'violation_type',
        'occurred_on',
        'occurred_time',
        'location',
        'description',
        'status',
        'office_report',
        'issued_by_admin_id',
        'issued_at',
        'acknowledged_at',
        'resolution_notes',
        'resolved_by_admin_id',
        'resolved_at',
    ];

    protected $casts = [
        'violation_type'  => EnumCast::class . ':' . ViolationType::class,
        'status'          => EnumCast::class . ':' . ViolationStatus::class,
        'occurred_on'     => 'date',
        'issued_at'       => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at'     => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The resident the violation was issued against.
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * The officer who issued the violation.
     */
    public function issuedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'issued_by_admin_id');
    }

    /**
     * The officer who resolved the violation.
     */
    public function resolvedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'resolved_by_admin_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across what the violation says and who it is against.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('resident_name', 'like', $like)
                ->orWhere('location', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('violation_type', 'like', $like)
                ->orWhereHas('resident', function ($query) use ($like) {
                    $query->where('first_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like)
                        ->orWhere('surname', 'like', $like);
                });
        });
    }

    /**
     * Only the violations in one status.
     */
    public function scopeWithStatus($query, $status)
    {
        $status = $status instanceof ViolationStatus ? $status->value : $status;

        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Only the violations of one type.
     */
    public function scopeWithType($query, $type)
    {
        $type = $type instanceof ViolationType ? $type->value : $type;

        return $type ? $query->where('violation_type', $type) : $query;
    }

    /**
     * Only the violations against one homeowner
     */
    public function scopeForResident($query, $residentId)
    {
        return $residentId ? $query->where('resident_id', $residentId) : $query;
    }

    /**
     * Only the violations that still stand against the homeowner.
     */
    public function scopeActive($query)
    {
        return $query->where('status', ViolationStatus::ACTIVE);
    }

    /**
     * Only the violations the office has closed.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', ViolationStatus::RESOLVED);
    }

    /**
     * Newest first: the date the violation happened, and the day it was issued.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('occurred_on')->orderByDesc('id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * What the violation was for, as a label.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->violation_type ? $this->violation_type->label() : '—';
    }

    /**
     * Where the violation stands, as a label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? $this->status->label() : '—';
    }

    /**
     * The badge theme the status reads in
     */
    public function getStatusBadgeThemeAttribute(): string
    {
        return $this->status ? $this->status->badgeTheme() : 'secondary';
    }

    /**
     * The badge theme the status reads in on a member's history
     */
    public function getHistoryBadgeThemeAttribute(): string
    {
        return $this->status ? $this->status->historyBadgeTheme() : 'secondary';
    }

    /**
     * When the violation happened, as a record writes it: "Jun 14, 2026
     */
    public function getOccurredDisplayAttribute(): string
    {
        if (! $this->occurred_on) {
            return '—';
        }

        $display = $this->occurred_on->format('M j, Y');

        if ($this->occurred_time) {
            $display .= ' · ' . TimeHelper::clock($this->occurred_time);
        }

        return $display;
    }

    /**
     * The homeowner the violation names, falling back to the name snapshotted
     * when it was issued — a resident removed from the office's records since
     * then leaves the citation readable rather than nameless.
     */
    public function getResidentLabelAttribute(): string
    {
        return $this->resident->full_name ?? $this->resident_name ?? 'Removed resident';
    }

    /**
     * Whether the homeowner has confirmed receiving the notice.
     */
    public function getIsAcknowledgedAttribute(): bool
    {
        return $this->acknowledged_at !== null;
    }

    /**
     * When the homeowner acknowledged the notice, or a plain statement that
     * they have not — a column reads as a sentence rather than a bare dash.
     */
    public function getAcknowledgedDisplayAttribute(): string
    {
        return $this->acknowledged_at
            ? $this->acknowledged_at->format('M j, Y g:i A')
            : 'Not yet acknowledged';
    }

    /**
     * Whether the violation was issued by an officer or is a draft the office
     * has not sent — a violation exists to be sent, so an unissued one is the
     * exception, not the rule.
     */
    public function getIsIssuedAttribute(): bool
    {
        return $this->issued_at !== null;
    }

    /**
     * The officer who filed the office report, when one was written.
     */
    public function getHasOfficeReportAttribute(): bool
    {
        return $this->office_report !== null && trim((string) $this->office_report) !== '';
    }

    /**
     * Whether the office can still resolve this violation.
     */
    public function getCanBeResolvedAttribute(): bool
    {
        return $this->status ? $this->status->canBeResolved() : false;
    }

    /**
     * The steps of the violation, in the order they happen — what the view
     * modal's timeline walks through, and what the dashboard's recent list
     * summarises.
     *
     * @return array<int, array{label: string, when: string, note: string|null, icon: string}>
     */
    public function timeline(): array
    {
        $steps = [
            ['label' => 'Issued', 'at' => $this->issued_at, 'note' => $this->issuedByAdmin->name ?? null],
            ['label' => 'Acknowledged', 'at' => $this->acknowledged_at, 'note' => null],
            ['label' => 'Resolved', 'at' => $this->resolved_at, 'note' => $this->resolution_notes],
        ];

        return array_map(function (array $step) {
            return [
                'label' => $step['label'],
                'when'  => $step['at'] ? $step['at']->format('M j, Y g:i A') : 'Not reached',
                'note'  => $step['note'],
                'icon'  => $step['at']
                    ? 'fas fa-fw fa-check-circle text-success'
                    : 'far fa-fw fa-circle text-muted',
            ];
        }, $steps);
    }
}
