<?php

namespace App\Models;

use App\Casts\EnumCast;
use App\Enums\BillStatus;
use App\Enums\BusinessPermitPurpose;
use App\Enums\PrintDocument;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Models\Bill\Bill;
use App\Models\Concerns\HasSlipReference;
use Illuminate\Database\Eloquent\Model;

/**
 * One service request a homeowner filed with the office.
 *
 * A request is one of the printable documents the association issues — a
 * Membership Form, a Business Permit, an HOA Permit, an Endorsement Letter or a
 * Certification — and the fields it carries depend on which: a certification
 * has a purpose, a business permit adds the business
 * details, an HOA permit carries its type and scope of work, an endorsement
 * letter carries its addressee, and the Membership Form carries nothing at all
 * (it is filled in from the resident's own profile). The type states that shape
 * (RequestType); the record only holds the values.
 *
 * The record is filed under exactly one resident (`resident_id` — the account's
 * own, written by the service, never taken from the form). Where it stands is
 * `RequestStatus`'s to say; the office marks it done through the service, and
 * the record keeps who did and when.
 *
 * The model also states the existing printable document the request turns into
 * and the values that document reads (`printDocument()`, `printParams()`). The
 * document is printed by the office and handed over at the counter; what the
 * portal gives the homeowner is a transaction slip for this record.
 */
class Request extends Model
{
    use HasSlipReference;

    /**
     * How long an approved request's document stays valid, in days.
     *
     * The date is stamped on the record when the office approves it (see
     * \App\Actions\Support\Requests\ApproveRequest) and is what the printed
     * certification says. It is not a download window — nothing downloads.
     */
    const DOCUMENT_VALID_DAYS = 90;

    protected $fillable = [
        'resident_id',
        'type',
        'purpose',
        'business_name',
        'nature_of_business',
        'hours_of_operation',
        'purpose_other',
        'permit_type',
        'location',
        'scope_of_work',
        'addressee',
        'letter_date',
        'membership_data',
        'status',
        'remarks',
        'processed_by_admin_id',
        'processed_at',
        'completed_by_admin_id',
        'completed_at',
        'bill_id',
        'document_expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * `type` and `status` are enum-backed columns, cast through the app's
     * EnumCast (Laravel 7 predates native enum casts). `purpose` is deliberately
     * left a plain string: its meaning depends on the request's type, so it is
     * resolved against the type's own purpose enum in `purpose_label`, never
     * cast to one enum on the model.
     *
     * @var array
     */
    protected $casts = [
        'type'                    => EnumCast::class . ':' . RequestType::class,
        'status'                  => EnumCast::class . ':' . RequestStatus::class,
        'processed_at'            => 'datetime',
        'completed_at'            => 'datetime',
        'document_expires_at'     => 'datetime',
        'letter_date'             => 'date',
        'membership_data'          => 'array',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The homeowner the request belongs to.
     */
    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    /**
     * The officer who acted on the request.
     */
    public function processedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'processed_by_admin_id');
    }

    /**
     * The officer who completed the approved request.
     */
    public function completedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'completed_by_admin_id');
    }

    /**
     * The invoice raised for the request, when the office has raised one.
     *
     * A request is filed without a bill; the office raises one through the
     * list's Create Invoice action, which links it here, and the portal reads it
     * to know whether the document's invoice has been settled.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the slip reference, what the request asks for and
     * who filed it.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('reference', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('purpose', 'like', $like)
                ->orWhere('business_name', 'like', $like)
                ->orWhere('nature_of_business', 'like', $like)
                ->orWhere('permit_type', 'like', $like)
                ->orWhere('scope_of_work', 'like', $like)
                ->orWhere('addressee', 'like', $like)
                ->orWhereHas('resident', function ($query) use ($like) {
                    $query->where('first_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like)
                        ->orWhere('surname', 'like', $like);
                });
        });
    }

    /**
     * Only the requests in one status.
     */
    public function scopeWithStatus($query, $status)
    {
        $status = $status instanceof RequestStatus ? $status->value : $status;

        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Only the requests of one type.
     */
    public function scopeWithType($query, $type)
    {
        $type = $type instanceof RequestType ? $type->value : $type;

        return $type ? $query->where('type', $type) : $query;
    }

    /**
     * Only the requests filed by one homeowner — the query the portal's own
     * list is built on, so another household's request is never in the result
     * set to begin with.
     */
    public function scopeForResident($query, $residentId)
    {
        return $residentId ? $query->where('resident_id', $residentId) : $query;
    }

    /**
     * Only the requests the office still has to decide on.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', RequestStatus::PENDING);
    }

    /**
     * Only the requests the office approved — whose slip and document are
     * available.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', RequestStatus::APPROVED);
    }

    /**
     * Only the approved requests the office still has to hand over.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', RequestStatus::COMPLETED);
    }

    /**
     * Newest submission first — the ordering every list of them uses.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    // =====================================================================
    // References
    // =====================================================================

    /**
     * The prefix this record's slip references carry, read by
     * \App\Models\Concerns\HasSlipReference::nextReference().
     */
    protected static function referencePrefix(): string
    {
        return 'REQ';
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The slip heading for this request: its reference and what it asks for.
     */
    public function getSlipLabelAttribute(): string
    {
        return ($this->reference ?: '—') . ' — ' . $this->type_label;
    }

    /**
     * What is being asked for, as a label.
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type ? $this->type->label() : '—';
    }

    /**
     * Why it is being asked, as a label.
     *
     * The purpose column holds a value from the request type's own purpose
     * enum, so it is resolved against that enum rather than a single app-wide
     * one.
     */
    public function getPurposeLabelAttribute(): string
    {
        if (! $this->purpose || ! $this->type) {
            return '—';
        }

        $enum = $this->type->purposeEnum();

        if (! $enum) {
            return '—';
        }

        $case = $enum::tryFrom($this->purpose);

        return $case ? $case->label() : $this->purpose;
    }

    /**
     * The why behind the purpose: the written-out text when the purpose is a
     * business permit's Other, the purpose's own label otherwise.
     */
    public function getPurposeDisplayAttribute(): string
    {
        if ($this->purpose === BusinessPermitPurpose::OTHER && $this->type
            && $this->type->is(RequestType::BUSINESS_PERMIT())) {
            return $this->purpose_other ?: 'Other (not specified)';
        }

        return $this->purpose_label;
    }

    /**
     * Where the request stands, as a label.
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
     * The homeowner the request belongs to.
     */
    public function getResidentLabelAttribute(): string
    {
        return $this->resident->full_name ?? 'Removed resident';
    }

    /**
     * A one-line summary of what the request contains — what the list shows
     * under the type, whichever type it is.
     */
    public function getSummaryAttribute(): string
    {
        if (! $this->type) {
            return '—';
        }

        switch ($this->type->value) {
            case RequestType::MEMBERSHIP_FORM:
                return 'Filled in from the homeowner record';

            case RequestType::CERTIFICATION:
                return $this->purpose_label;

            case RequestType::BUSINESS_PERMIT:
                return $this->business_name ?: $this->purpose_display;

            case RequestType::HOA_PERMIT:
                return $this->permit_type ?: '—';

            case RequestType::ENDORSEMENT_LETTER:
                return $this->addressee ?: '—';
        }

        return '—';
    }

    /**
     * The request's own fields, as label → value rows — what the view modal
     * reads, so the view never re-derives a type's shape itself.
     *
     * @return array<string, string>
     */
    public function details(): array
    {
        if (! $this->type) {
            return [];
        }

        switch ($this->type->value) {
            case RequestType::MEMBERSHIP_FORM:
                return [
                    'Document' => $this->type->label(),
                ];

            case RequestType::CERTIFICATION:
                return [
                    'Purpose' => $this->purpose_display,
                ];

            case RequestType::BUSINESS_PERMIT:
                return [
                    'Business Name'      => $this->business_name ?: '—',
                    'Nature of Business' => $this->nature_of_business ?: '—',
                    'Hours of Operation' => $this->hours_of_operation ?: '—',
                    'Purpose'            => $this->purpose_display,
                ];

            case RequestType::HOA_PERMIT:
                return [
                    'Permit Type'           => $this->permit_type ?: '—',
                    'Location'              => $this->location ?: '—',
                    'Purpose / Scope of Work' => $this->scope_of_work ?: '—',
                ];

            case RequestType::ENDORSEMENT_LETTER:
                return [
                    'Addressee' => $this->addressee ?: '—',
                    'Date'      => $this->letter_date ? $this->letter_date->format('M j, Y') : '—',
                ];
        }

        return [];
    }

    /**
     * Whether the office has acted on this request.
     */
    public function getIsProcessedAttribute(): bool
    {
        return $this->processed_at !== null;
    }

    /**
     * Whether the office can still decide on this request: only a pending one
     * can be approved or cancelled, and an edit is for the same window.
     */
    public function getIsOpenAttribute(): bool
    {
        return $this->status ? $this->status->isPending() : false;
    }

    /**
     * Whether the office approved this request — the one state in which its
     * transaction slip is available and its document may be printed.
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status ? $this->status->isApproved() : false;
    }

    /**
     * Whether the office has completed this request — the document has been
     * handed over and nothing is left to do.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status ? $this->status->value === RequestStatus::COMPLETED : false;
    }

    // =====================================================================
    // Invoice & document release
    // =====================================================================

    /**
     * Whether the office has already raised an invoice for this request.
     */
    public function getHasInvoiceAttribute(): bool
    {
        return $this->bill_id !== null;
    }

    /**
     * Whether the invoice raised for this request is still waiting to be
     * settled — the state the portal flags on its cards.
     *
     * Only a collectible bill counts: one the office has cancelled (or already
     * settled) is not a debt (see \App\Enums\BillStatus::isCollectible()), and a
     * request with no invoice has nothing to pay at all. A partly-paid invoice
     * still carries a balance, so it is waiting too.
     */
    public function getHasBillWaitingAttribute(): bool
    {
        return (bool) $this->bill?->status_enum?->isCollectible();
    }

    /**
     * The invoice raised for this request as one line — its reference and where
     * it stands — or null while none has been raised.
     */
    public function getInvoiceLabelAttribute(): ?string
    {
        if (! $this->bill) {
            return null;
        }

        $status = BillStatus::tryFrom($this->bill->status);

        return $this->bill->reference . ' — ' . ($status ? $status->label() : $this->bill->status);
    }

    /**
     * Whether the invoice raised for this request is settled.
     *
     * The office collects the fee at the counter before handing the document
     * over; the portal reads this only to say on the card whether the bill is
     * still open.
     */
    public function getIsInvoicePaidAttribute(): bool
    {
        return $this->bill !== null && $this->bill->status === BillStatus::PAID;
    }

    /**
     * Whether a printed certification's validity date has passed.
     */
    public function getIsDocumentExpiredAttribute(): bool
    {
        return $this->document_expires_at !== null && $this->document_expires_at->isPast();
    }

    // =====================================================================
    // Printing
    // =====================================================================

    /**
     * The existing printable document this request turns into.
     */
    public function printDocument(): ?PrintDocument
    {
        return $this->type ? $this->type->printDocument() : null;
    }

    /**
     * The parameters the issued document is built from when the office prints
     * it.
     *
     * This is the request's own print parameters plus the few values the
     * request's type does not collect but the document requires anyway. An HOA
     * permit asks when the work starts and ends; a request never does, so the
     * permit it issues runs from the day the office approved it — the day it
     * became valid. The rest of the documents are drawn from what the request
     * already carries (see printParams()).
     *
     * @return array<string, mixed>
     */
    public function documentParams(): array
    {
        $params = $this->printParams();

        if ($this->type && $this->type->is(RequestType::HOA_PERMIT())) {
            $start = $this->processed_at ?: $this->created_at ?: now();

            $params['eventFrom'] = $start->format('Y-m-d');
        }

        return $params;
    }

    /**
     * The values the printable document reads, taken from the request's own
     * fields — the same keys the document's print form collects, so the
     * document is populated from the request rather than re-entered by hand.
     *
     * Only the fields the request's type carries are passed; the rest stay
     * blank on the document.
     *
     * @return array<string, mixed>
     */
    public function printParams(): array
    {
        $params = ['residentId' => $this->resident_id];

        if (! $this->type) {
            return $params;
        }

        switch ($this->type->value) {
            case RequestType::MEMBERSHIP_FORM:
                // A form is printed from the record. A detail the record leaves
                // blank rides along with this request, but by its id only: the
                // document reads it from the record server-side, so a contact
                // number never travels in a print URL.
                return $params + ['requestId' => $this->id];

            case RequestType::CERTIFICATION:
                return $params + [
                    'purpose'      => $this->purpose,
                    'purposeOther' => $this->purpose_other,
                ];

            case RequestType::BUSINESS_PERMIT:
                return $params + [
                    'purpose'      => $this->purpose,
                    'purposeOther' => $this->purpose_other,
                ];

            case RequestType::HOA_PERMIT:
                // The request asks for the work by name and scope; the permit
                // prints one purpose line, which is the scope of the work.
                return $params + [
                    'purpose'  => $this->scope_of_work,
                    'location' => $this->location,
                ];

            case RequestType::ENDORSEMENT_LETTER:
                // A request names the office it is addressed to in one line; the
                // letter prints that line as the addressee's name, and the
                // officer fills in the position, office and city the request
                // never asked for. The respondent's unit is the household's own,
                // read here rather than typed into the request.
                $unit = $this->resident ? $this->resident->unit() : null;

                return $params + [
                    'addresseeName'   => $this->addressee,
                    'letterDate'      => $this->letter_date ? $this->letter_date->toDateString() : null,
                    'respondentBlock' => $unit && $unit->block !== null ? (string) $unit->block : null,
                    'respondentLot'   => $unit && $unit->lot !== null ? (string) $unit->lot : null,
                ];
        }

        return $params;
    }
}
