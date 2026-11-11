<?php

namespace App\Http\Livewire\Admin\Collections\Soa;

use App\Actions\Collections\Soa\PrepareStatementsOfAccount;
use App\Actions\Collections\Soa\SendStatementsOfAccount;
use App\Enums\SoaStatus;
use App\Http\Livewire\AdminComponent;
use App\Http\Livewire\Concerns\WithListCriteria;
use App\Models\SoaEmail;
use App\Queries\Collections\Soa\StatementListQuery;
use App\Utility\PeriodHelper;
use Livewire\WithPagination;

/**
 * Email SOA page — statements of account, prepared a period at a time and
 * mailed to the homeowners who owe something.
 *
 * The page works in two steps, and keeps them apart on purpose: preparing a
 * batch writes one row per unit that has an address on file and something
 * outstanding, and sending mails them. A statement that could not be delivered
 * is marked failed with the reason, so Send / Retry goes out to exactly the ones
 * that did not go out rather than re-mailing the whole batch.
 *
 * The view, delete and mass-delete dialogs are siblings, each opening its own
 * shell.
 *
 * Split (one folder per page):
 *   SoaTableList — this component (list state, batch actions, row actions)
 *   Modals\*     — the page's modals as event-driven siblings
 */
class SoaTableList extends AdminComponent
{
    use WithPagination,
        WithListCriteria;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var string Status filter */
    public $filterStatus = '';

    /** @var string Period filter, "Y-m" */
    public $filterPeriod = '';

    /** @var string The period a batch is prepared for */
    public $preparePeriod = '';

    /** @var array Selected statement IDs */
    public $selectedIds = [];

    /** @var bool Select all pages */
    public $selectAll = false;

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search', 'filterStatus', 'filterPeriod'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'soa-sent'  => '$refresh',
        'soa-saved' => 'refreshList',
    ];

    /** @var StatementListQuery */
    protected $query;

    /** @var PrepareStatementsOfAccount */
    protected $prepareStatements;

    /** @var SendStatementsOfAccount */
    protected $sendStatements;

    public function boot(
        StatementListQuery $query,
        PrepareStatementsOfAccount $prepareStatements,
        SendStatementsOfAccount $sendStatements
    ) {
        $this->query = $query;
        $this->prepareStatements = $prepareStatements;
        $this->sendStatements = $sendStatements;
    }

    public function mount(): void
    {
        $this->preparePeriod = PeriodHelper::current();
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPeriod(): void
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

    public function getStatementsProperty()
    {
        return $this->query->paginate($this->listCriteria());
    }

    public function updatedSelectAll($value): void
    {
        $this->selectedIds = $value ? $this->resolveSelectedIds() : [];
    }

    /**
     * A dialog wrote a statement — including the mass-delete dialog. Drop the
     * selection it consumed.
     */
    public function refreshList(): void
    {
        $this->selectedIds = [];
        $this->selectAll = false;
    }

    // =====================================================================
    // Batch actions
    // =====================================================================

    /**
     * Prepare the chosen period's statements.
     */
    public function prepare(): void
    {
        $this->authorize('create', SoaEmail::class);

        $prepared = $this->prepareStatements->handle($this->preparePeriod, auth('web')->user());

        $this->emit(
            'notify',
            $prepared > 0 ? 'success' : 'warning',
            $prepared > 0
                ? $prepared . ' statement(s) prepared for ' . PeriodHelper::label($this->preparePeriod) . '.'
                : 'Nothing to prepare — every homeowner account with an email on file has either settled or already been prepared for that period.'
        );
    }

    /**
     * Mail the selected statements. Statements already sent are skipped, so the
     * same action retries exactly the ones that failed.
     */
    public function sendSelected(): void
    {
        $this->authorize('send', SoaEmail::class);

        if (empty($this->selectedIds)) {
            $this->emit('notify', 'warning', 'No statements selected.');

            return;
        }

        $result = $this->sendStatements->handle($this->selectedIds, auth('web')->user());

        if ($result['sent'] === 0 && $result['failed'] === 0) {
            $this->emit('notify', 'warning', 'Nothing to send — the selected statements have already gone out.');

            return;
        }

        $this->emit(
            'notify',
            $result['failed'] > 0 ? 'warning' : 'success',
            $result['sent'] . ' statement(s) sent'
                . ($result['failed'] > 0 ? ', ' . $result['failed'] . ' failed — see the error column and retry.' : '.')
        );

        $this->selectedIds = [];
        $this->selectAll = false;
    }

    /**
     * Send (or re-send) one row's statement through the same Action the bulk
     * button uses, so a single row and a whole batch behave identically.
     */
    public function sendOne(int $id): void
    {
        $this->authorize('send', SoaEmail::class);

        $statement = $this->query->find($id);

        if (! $statement->email) {
            $this->emit('notify', 'warning', 'This statement has no email address on file — record one before sending it.');

            return;
        }

        $result = $this->sendStatements->handle([$id], auth('web')->user());

        if ($result['sent'] === 0 && $result['failed'] === 0) {
            $this->emit('notify', 'warning', 'Nothing to send — this statement has already gone out.');

            return;
        }

        $this->emit(
            'notify',
            $result['failed'] > 0 ? 'warning' : 'success',
            $result['failed'] > 0
                ? 'The statement could not be sent — see the error column and retry.'
                : 'Statement sent to ' . $statement->email . '.'
        );

        $this->emit('soa-sent');
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', SoaEmail::class);

        return view('livewire.admin.collections.soa.soa-table-list', [
            'statements' => $this->statements,
            'statuses'  => SoaStatus::labels(),
            'periods'   => $this->query->periods(),
        ]);
    }
}
