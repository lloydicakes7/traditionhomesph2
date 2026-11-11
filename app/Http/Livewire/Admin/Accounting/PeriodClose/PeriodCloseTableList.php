<?php

namespace App\Http\Livewire\Admin\Accounting\PeriodClose;

use App\Http\Livewire\AdminComponent;
use App\Models\Account\AccountingPeriod;
use App\Queries\Accounting\PeriodListQuery;

/**
 * The Period Close page — the month-end and year-end lock.
 *
 * The register lays a year's months out, joins the periods on file onto them
 * and shows where each one stands, so a month with no row reads as Open rather
 * than as missing. Closing a month is what stops every module from posting into
 * it, and reopening one puts a locked month back in play — both go through the
 * service, and both are confirmed, because either one changes what every report
 * for that month will say.
 *
 * Split (one folder per page):
 *   PeriodCloseTableList — this component (list state, row actions)
 *   Modals\*             — the close and reopen confirmations as siblings
 */
class PeriodCloseTableList extends AdminComponent
{
    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Year filter */
    public $filterYear = '';

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['filterYear'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'period-updated' => '$refresh',
    ];

    /** @var PeriodListQuery */
    protected $periods;

    public function boot(PeriodListQuery $periods)
    {
        $this->periods = $periods;
    }

    /**
     * The register opens on the current year.
     */
    public function mount(): void
    {
        if ($this->filterYear === '') {
            $this->filterYear = now()->format('Y');
        }
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatedFilterYear(): void
    {
        $this->filterYear = (string) $this->filterYear;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', AccountingPeriod::class);

        return view('livewire.admin.accounting.period-close.period-close-table-list', [
            'months' => $this->periods->months($this->filterYear),
            'years'  => $this->periods->yearOptions(),
        ]);
    }
}
