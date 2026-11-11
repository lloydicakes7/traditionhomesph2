<?php

namespace App\Http\Livewire\Admin\AmenitiesBooking\Bookings;

use App\Enums\BookingStatus;
use App\Enums\DepositStatus;
use App\Http\Livewire\Admin\Collections\Invoices\Modals\InvoiceFormModal;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\AdminComponent;
use App\Models\Amenity\AmenityBooking;
use App\Models\Bill\Bill;
use App\Queries\Amenities\AmenityBookingListQuery;
use App\Queries\Amenities\AmenityListQuery;
use Livewire\WithPagination;

/**
 * Amenity Bookings page — the reservations table: search, the three filters,
 * sorting, pagination, selection, and the row actions.
 *
 * Every row action is a Bootstrap trigger — reading or correcting a
 * reservation (`#bookingViewModal`, `#bookingFormModal`), recording a payment
 * (`#recordPaymentModal`), the delete confirmation (`#bookingDeleteModal`), the
 * bulk delete, and each move in the reservation's life (approving it, closing
 * it, settling its deposit, cancelling it). The invoice is raised on the
 * Collections module's own form, so its row action first asks this component
 * for the booking (see createInvoice()).
 *
 * Which moves a reservation still allows is a question about its status, and
 * BookingStatus answers it; this component never re-derives it from a list of
 * statuses.
 *
 * Split (one folder per page):
 *   BookingTableList — this component (list state, row actions)
 *   Modals\*         — every modal as its own event-driven component
 */
class BookingTableList extends AdminComponent
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

    /** @var string Amenity filter */
    public $filterAmenity = '';

    /** @var string Deposit filter */
    public $filterDeposit = '';

    /** @var string Sort field */
    public $sortField = 'starts_at';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected booking IDs for mass delete */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = [
        'search', 'filterStatus', 'filterAmenity', 'filterDeposit',
        'sortField', 'sortDirection',
    ];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'booking-saved'    => 'refreshList',
        'invoice-saved'    => 'refreshList',
        'payment-recorded' => 'refreshList',
    ];

    /** @var AmenityBookingListQuery */
    protected $bookings;

    /** @var AmenityListQuery */
    protected $amenities;

    public function boot(AmenityBookingListQuery $bookings, AmenityListQuery $amenities)
    {
        $this->bookings = $bookings;
        $this->amenities = $amenities;
    }

    // =====================================================================
    // Filters — every one of them returns the table to its first page
    // =====================================================================

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAmenity(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDeposit(): void
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
        return $this->bookings->ids($this->criteria());
    }

    /**
     * Toggle "all pages" using the service-filtered id list.
     */
    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    // =====================================================================
    // Refresh
    // =====================================================================

    /**
     * Another component wrote a reservation — a save, an action, a delete, a
     * mass delete.
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

    // =====================================================================
    // Row actions needing the server
    // =====================================================================

    public function createInvoice($id): void
    {
        $this->authorize('createInvoice', Bill::class);
        $this->authorize('recordPayment', AmenityBooking::class);

        $booking = $this->bookings->detail((int) $id);

        if (! $booking->can_be_invoiced) {
            $this->emit('notify', 'warning', 'That move no longer applies to this booking.');

            return;
        }

        $this->emit('openBookingInvoiceFormModal', $booking->id);
        $this->dispatchBrowserEvent('modal-open', ['id' => InvoiceFormModal::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', AmenityBooking::class);

        return view('livewire.admin.amenities-booking.bookings.booking-table-list', [
            'bookings'        => $this->bookings->paginate($this->criteria()),
            'statuses'        => BookingStatus::labels(),
            'depositStatuses' => DepositStatus::labels(),
            'amenities'       => $this->amenities->options(),
        ]);
    }

    private function criteria(): array
    {
        return [
            'search'    => $this->search,
            'sort'      => $this->sortField,
            'direction' => $this->sortDirection,
            'filters'   => [
                'status'  => $this->filterStatus,
                'amenity' => $this->filterAmenity,
                'deposit' => $this->filterDeposit,
            ],
        ];
    }
}
