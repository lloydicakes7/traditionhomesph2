<?php

namespace App\Http\Livewire\Admin\Accounting\CashFunding;

use App\Enums\JournalSource;
use App\Http\Livewire\AdminComponent;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Accounting\JournalEntryListQuery;
use Livewire\WithPagination;

class CashFundingTableList extends AdminComponent
{
    use WithPagination;

    /** @var string */
    public $search = '';

    /** @var string */
    public $filterPeriod = '';

    /** @var array<string> */
    protected $queryString = ['search', 'filterPeriod'];

    /** @var array<string, string> */
    protected $listeners = [
        'cash-funding-saved' => '$refresh',
    ];

    /** @var JournalEntryListQuery */
    protected $journalEntries;

    public function boot(JournalEntryListQuery $journalEntries)
    {
        $this->journalEntries = $journalEntries;
    }

    // ===== Filters =====

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPeriod(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterPeriod = '';

        $this->resetPage();
    }

    /**
     * @return array<string, mixed>
     */
    private function criteria(): array
    {
        return [
            'search'    => $this->search,
            'filters'   => [
                'source' => JournalSource::FUND_ADDITION,
                'period' => $this->filterPeriod,
            ],
            'sort'      => 'entry_date',
            'direction' => 'desc',
        ];
    }

    // ===== Render =====

    public function render()
    {
        $this->authorize('viewAny', JournalEntry::class);

        return view('livewire.admin.accounting.cash-funding.cash-funding-table-list', [
            'entries' => $this->journalEntries->paginate($this->criteria()),
            'periods' => $this->journalEntries->periods(),
            'totals'  => $this->journalEntries->totals($this->criteria()),
        ]);
    }
}
