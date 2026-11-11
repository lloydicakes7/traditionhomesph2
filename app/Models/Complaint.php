<?php

namespace App\Models;

use App\Casts\EnumCast;
use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Model;

/**
 * One complaint, filed by a homeowner from the portal or by an officer for
 * something reported to the office.
 *
 * `resident_id` names the homeowner the complaint concerns — the filer on a
 * portal submission, the subject on one the office recorded about them — and
 * `filed_by_admin_id` names the officer when the office recorded it (and is
 * null when the homeowner filed it themselves). `submitter_name` snapshots the
 * name the complaint was filed under so a retired account leaves the record
 * readable. When it was filed is its own `created_at` — a complaint is
 * submitted once and never re-dated.
 *
 * The office's answer is part of the same record (response / responded_at), so
 * the submitter reads the update where they read the complaint's status.
 */
class Complaint extends Model
{
    protected $fillable = [
        'resident_id',
        'filed_by_admin_id',
        'submitter_name',
        'category',
        'subject',
        'description',
        'status',
        'response',
        'responded_by_admin_id',
        'responded_at',
        'closed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'category'     => EnumCast::class . ':' . ComplaintCategory::class,
        'status'       => EnumCast::class . ':' . ComplaintStatus::class,
        'responded_at' => 'datetime',
        'closed_at'    => 'datetime',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The homeowner who filed the complaint, when a member filed it.
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * The officer who filed the complaint, when the office recorded it.
     */
    public function filedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'filed_by_admin_id');
    }

    /**
     * The officer who answered the complaint.
     */
    public function respondedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'responded_by_admin_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across what the complaint says and who filed it.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('subject', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('submitter_name', 'like', $like)
                ->orWhere('category', 'like', $like)
                ->orWhereHas('resident', function ($query) use ($like) {
                    $query->where('first_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like)
                        ->orWhere('surname', 'like', $like);
                });
        });
    }

    /**
     * Only the complaints in one status.
     */
    public function scopeWithStatus($query, $status)
    {
        $status = $status instanceof ComplaintStatus ? $status->value : $status;

        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Only the complaints in one category.
     */
    public function scopeWithCategory($query, $category)
    {
        $category = $category instanceof ComplaintCategory ? $category->value : $category;

        return $category ? $query->where('category', $category) : $query;
    }

    /**
     * Only the complaints filed by one homeowner — the query the portal's own
     * list is built on, so another household's complaint is never in the result
     * set to begin with.
     */
    public function scopeForResident($query, $residentId)
    {
        return $residentId ? $query->where('resident_id', $residentId) : $query;
    }

    /**
     * Only the complaints the office still has to act on.
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [ComplaintStatus::PENDING, ComplaintStatus::IN_PROGRESS]);
    }

    /**
     * Newest submission first — the ordering every list of them uses.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * What the complaint is about, as a label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->category ? $this->category->label() : '—';
    }

    /**
     * Where the complaint stands, as a label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? $this->status->label() : '—';
    }

    /**
     * The badge theme the status reads in — decided by the enum, never by a
     * view's own conditional.
     */
    public function getStatusBadgeThemeAttribute(): string
    {
        return $this->status ? $this->status->badgeTheme() : 'secondary';
    }

    /**
     * Who filed the complaint: the homeowner who submitted it, or the officer
     * who recorded it for them.
     *
     * `resident_id` alone does not answer this — an office filing names the
     * resident the complaint concerns too — so the filer is judged by who wrote
     * the record: a filing with no `filed_by_admin_id` came from the portal.
     */
    public function getSubmitterLabelAttribute(): string
    {
        if ($this->submitted_by_homeowner) {
            return $this->resident->full_name
                ?? $this->submitter_name
                ?? 'Removed account';
        }

        return $this->filedByAdmin->name
            ?? $this->submitter_name
            ?? 'Removed account';
    }

    /**
     * Whether a homeowner filed this complaint from the portal (rather than the
     * office recording it).
     */
    public function getSubmittedByHomeownerAttribute(): bool
    {
        return $this->filed_by_admin_id === null;
    }

    /**
     * Whether the office has written an update against this complaint.
     */
    public function getIsRespondedAttribute(): bool
    {
        return $this->responded_at !== null;
    }

    /**
     * Whether the office still has something to do with this complaint.
     */
    public function getIsOpenAttribute(): bool
    {
        return $this->status ? $this->status->isOpen() : false;
    }

    /**
     * Whether the office can still respond to this complaint.
     */
    public function getCanBeRespondedToAttribute(): bool
    {
        return $this->status ? $this->status->canBeRespondedTo() : false;
    }

    /**
     * The steps of the complaint, in the order they happen — what the view
     * modal's timeline walks through.
     *
     * @return array<int, array{label: string, when: string, note: string|null, icon: string}>
     */
    public function timeline(): array
    {
        $steps = [
            ['label' => 'Submitted', 'at' => $this->created_at, 'note' => $this->submitter_label],
            ['label' => 'Response written', 'at' => $this->responded_at, 'note' => $this->respondedByAdmin->name ?? null],
            ['label' => 'Closed', 'at' => $this->closed_at, 'note' => null],
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
