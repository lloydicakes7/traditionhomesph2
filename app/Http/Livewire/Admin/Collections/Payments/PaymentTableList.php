<?php

namespace App\Http\Livewire\Admin\Collections\Payments;

use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\Concerns\WithListCriteria;
use App\Http\Livewire\Concerns\WithSorting;
use App\Http\Livewire\Admin\Accounting\JournalEntries\Modals\PostToJournalModal;
use App\Http\Livewire\AdminComponent;
use App\Models\JournalEntry\JournalEntry;
use App\Models\Payment;
use App\Queries\Collections\Payments\PaymentListQuery;
use App\Queries\Collections\PaymentMethods\PaymentMethodQuery;
use App\Utility\CsvHelper;
use App\Utility\PeriodHelper;
use Livewire\WithPagination;

/**
 * Payments page — the money the association has received, one ledger for both
 * kinds of bill.
 *
 * Recording a receipt opens the shared Record Payment dialog, which here opens
 * blank so the officer picks which bill the money settles. A receipt the
 * portal filed waits as pending in the same ledger and is approved through
 * the page's own Approve dialog (see Modals\PaymentApproveModal). The delete
 * and mass-delete confirmations open client-side from their triggers and load
 * what they confirm lazily.
 *
 * Split (one folder per page):
 *   PaymentTableList — this component (list state, row actions)
 *   Modals\*         — the page's modals as event-driven siblings
 */
class PaymentTableList extends AdminComponent
{
    use WithPagination,
        WithSorting,
        WithListCriteria,
        WithCsvExport;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Payment method filter */
    public $filterMethod = '';

    /** @var string Month filter, "Y-m" */
    public $filterMonth = '';

    /** @var string Sort field */
    public $sortField = 'paid_at';

    /** @var string Sort direction */
    public $sortDirection = 'desc';

    /** @var array Selected payment IDs for mass delete */
    public $selectedIds = [];

    /** @var int|null The payment just recorded, offered for printing straight away */
    public $recentPaymentId = null;

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterMethod', 'filterMonth', 'sortField', 'sortDirection'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'payment-recorded'    => 'paymentRecorded',
        'payment-approved'    => 'refreshList',
        'payment-saved'       => 'refreshList',
        'journal-entry-posted' => 'refreshList',
    ];

    /** @var PaymentListQuery */
    protected $query;

    /** @var PaymentMethodQuery */
    protected $paymentMethodQuery;

    public function boot(PaymentListQuery $query, PaymentMethodQuery $paymentMethodQuery)
    {
        $this->query = $query;
        $this->paymentMethodQuery = $paymentMethodQuery;
    }

    // =====================================================================
    // Queries
    // =====================================================================

    public function getPaymentsProperty()
    {
        return $this->query->paginate($this->listCriteria());
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingFilterMethod(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMonth(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Selection
    // =====================================================================

    /**
     * Ids implied by "select all pages" for the page's current filters.
     */
    public function resolveSelectedIds(): array
    {
        return $this->query->ids($this->listCriteria());
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * A dialog wrote a payment — including the mass-delete dialog. Drop the
     * selection it consumed.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    /**
     * A payment was just recorded: refresh the ledger and offer its receipt.
     */
    public function paymentRecorded($id = null): void
    {
        $this->refreshList();
        $this->recentPaymentId = $id ? (int) $id : null;
    }

    /**
     * The officer is done with the receipt offered after recording.
     */
    public function dismissReceipt(): void
    {
        $this->recentPaymentId = null;
    }

    public function postToJournal($id): void
    {
        $this->authorize('create', JournalEntry::class);

        $payment = $this->query->forPosting((int) $id);

        $this->authorize('view', $payment);

        if (! $payment->is_recorded || $payment->journal_entry_id) {
            $this->emit('notify', 'warning', 'That payment cannot be posted to the journal.');

            return;
        }

        $this->emit('openPostToJournal', ['source' => 'payment', 'id' => $payment->id]);
        $this->dispatchBrowserEvent('modal-open', ['id' => PostToJournalModal::MODAL_ID]);
    }

    // =====================================================================
    // WithCsvExport
    // =====================================================================

    protected function exportAuthorizable(): string
    {
        return Payment::class;
    }

    protected function exportCsv(): string
    {
        $rows = $this->query->rows($this->listCriteria())
            ->map(function (Payment $payment) {
                return [
                    $payment->paid_at_display,
                    $payment->reference_no ?: '—',
                    $payment->property_label,
                    $payment->bill_type_label,
                    $payment->bill_reference,
                    $payment->amount_display,
                    $payment->method_label,
                ];
            })
            ->all();

        return CsvHelper::build([
            'Date Received', 'Reference No.', 'Property', 'Bill Type',
            'Bill', 'Amount', 'Method',
        ], $rows);
    }

    protected function exportFileName(): string
    {
        return 'payment-export-' . now()->format('Y-m-d') . '.csv';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Payment::class);

        return view('livewire.admin.collections.payments.payment-table-list', [
            'payments'      => $this->payments,
            'methods'       => $this->paymentMethodQuery->options(),
            'months'        => PeriodHelper::recentMonths(),
            'recentPayment' => $this->recentPaymentId ? $this->query->find($this->recentPaymentId) : null,
        ]);
    }
}
