<?php

namespace App\Http\Livewire\Admin\Support\Requests;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Http\Livewire\Admin\Collections\Invoices\Modals\InvoiceFormModal;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Bill\Bill;
use App\Actions\Support\Requests\AttachRequestInvoice;
use App\Models\Request;
use App\Queries\Support\Requests\RequestDetailQuery;
use App\Queries\Support\Requests\RequestListQuery;
use Livewire\WithPagination;

/**
 * Requests page — what homeowners have asked the office for: search, the type
 * and status filters, sorting, pagination, selection, the "New Request" button
 * and the row actions.
 *
 * A row's moves live in one dropdown (see
 * resources/views/livewire/admin/support/requests/partials/request-actions).
 * View, Edit, Approve, Complete, Cancel and Delete each open one shared dialog
 * per page per action by id (`#requestViewModal`, `#requestEditModal`,
 * `#requestApproveModal`, `#requestCompleteModal`, `#requestCancelModal`,
 * `#requestDeleteModal`) and
 * `public/js/modal-loader.js` hands the row's id to that dialog, which loads
 * the record itself. Two moves need the server before anything is on screen,
 * so this component makes them:
 *
 *   — Create Invoice raises a bill for the homeowner the request came from on
 *     the Invoices page's own form (see createInvoice());
 *   — Print is a normal route to the request's own document, because a PDF is
 *     streamed rather than rendered by Livewire.
 *
 * "New Request" is the office's own filing — a homeowner who asks over the
 * counter — and opens the page's own form dialog.
 *
 * Split (one folder per page, the panel's convention):
 *   RequestTableList — this component (list state, row actions)
 *   Modals\*         — every modal as its own event-driven component
 */
class RequestTableList extends AdminComponent
{
    use WithPagination,
        WithSorting;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Status filter */
    public $filterStatus = '';

    /** @var string Request type filter */
    public $filterType = '';

    /** @var string Sort field */
    public $sortField = 'created_at';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected request IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /**
     * The request the open invoice form was opened for, while it is open.
     *
     * The office raises a request's bill on the Invoices page's own form, so
     * this remembers which request sent the officer there; the bill the form
     * creates is linked back to it when the form announces it (see
     * linkInvoice()).
     *
     * @var int|null
     */
    public $invoiceRequestId = null;

    /** @var array<string> Query-string persistent state */
    protected $queryString = [
        'search', 'filterStatus', 'filterType', 'sortField', 'sortDirection',
    ];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'request-saved' => 'refreshList',
        'invoice-saved' => 'linkInvoice',
    ];

    /** @var RequestListQuery */
    protected $list;

    /** @var RequestDetailQuery */
    protected $detail;

    /** @var AttachRequestInvoice */
    protected $attachInvoice;

    public function boot(RequestListQuery $list, RequestDetailQuery $detail, AttachRequestInvoice $attachInvoice)
    {
        $this->list = $list;
        $this->detail = $detail;
        $this->attachInvoice = $attachInvoice;
    }

    // =====================================================================
    // Filters — every one of them returns the table to its first page
    // =====================================================================

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Selection ("select all pages")
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->list->ids($this->criteria());
    }

    /**
     * Toggle "all pages" using the filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * Another component wrote a request — a save, a delete, a mass delete.
     *
     * Drop the selection it consumed, so a bulk delete never leaves the toolbar
     * standing over rows that are already gone, and let the re-render that
     * follows this call load what is on file now.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    /**
     * A bill was raised on the invoice form this page opened: link it to the
     * request that asked for it.
     *
     * The form announces every bill it creates; only the single-account charge
     * this page opened for a request is linked, and only while that request is
     * still the one waiting for its invoice. A charge raised for every account
     * at once belongs to no one request, so its ids are ignored.
     *
     * @param  array|null  $payload  The created bill ids, as the form announced them
     */
    public function linkInvoice($payload = null): void
    {
        $requestId = $this->invoiceRequestId;
        $this->invoiceRequestId = null;

        $billIds = array_values(array_filter((array) ($payload['bill_ids'] ?? [])));

        if (! $requestId || count($billIds) !== 1) {
            return;
        }

        $this->attachInvoice->handle($this->detail->find((int) $requestId), (int) $billIds[0], auth('web')->user());
    }

    // =====================================================================
    // Row actions needing the server
    // =====================================================================

    /**
     * Raise a bill for the homeowner a request came from.
     *
     * Billing is the Collections module's own permission whichever page asks
     * for it, and a bill belongs to a portal account, so the account is read
     * from the request's resident.
     * The form is the Invoices page's own `#invoiceFormModal`, opened with that
     * account already filled in; the office still names the lines and the
     * amount, exactly as it does there.
     *
     * @param  int|string  $id  The request whose homeowner is billed
     */
    public function createInvoice($id): void
    {
        $this->authorize('createInvoice', Bill::class);

        $request = $this->detail->find((int) $id);

        $account = $request->resident ? $request->resident->user : null;

        if (! $account) {
            $this->emit('notify', 'error', 'That homeowner has no portal account, so there is no account to bill.');

            return;
        }

        // Remember which request this bill is for, so the form's announcement
        // links the bill back to it (see linkInvoice()).
        $this->invoiceRequestId = $request->id;

        $this->emit('openInvoiceFormModal', $account->id);
        $this->dispatchBrowserEvent('modal-open', ['id' => InvoiceFormModal::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function criteria(): array
    {
        return [
            'search'    => $this->search,
            'filters'   => ['status' => $this->filterStatus, 'type' => $this->filterType],
            'sort'      => $this->sortField,
            'direction' => $this->sortDirection,
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Request::class);

        return view('livewire.admin.support.requests.request-table-list', [
            'requests' => $this->list->paginate($this->criteria()),
            'statuses' => RequestStatus::labels(),
            'types'    => RequestType::labels(),
        ]);
    }
}
