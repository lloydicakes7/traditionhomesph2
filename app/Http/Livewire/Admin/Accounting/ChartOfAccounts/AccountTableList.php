<?php

namespace App\Http\Livewire\Admin\Accounting\ChartOfAccounts;

use App\Http\Livewire\AdminComponent;
use App\Http\Livewire\Concerns\WithCsvExport;
use App\Models\Account\Account;
use App\Queries\Accounting\ChartOfAccountsQuery;
use App\Utility\CsvHelper;

/**
 * The Chart of Accounts page — the association's income, expenses and cash
 * accounts, with every balance computed from the journal lines behind it.
 *
 * Split (one folder per page):
 *   AccountTableList  — this component (list state, row actions)
 *   Modals\*          — the page's modals as event-driven siblings
 */
class AccountTableList extends AdminComponent
{
    use WithCsvExport;

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'account-saved' => '$refresh',
    ];

    /** @var ChartOfAccountsQuery */
    protected $chart;

    public function boot(ChartOfAccountsQuery $chart)
    {
        $this->chart = $chart;
    }

    // =====================================================================
    // Export
    // =====================================================================

    protected function exportAuthorizable(): string
    {
        return Account::class;
    }

    protected function exportFileName(): string
    {
        return 'chart-of-accounts-' . now()->format('Y-m-d') . '.csv';
    }

    protected function exportCsv(): string
    {
        $rows = [];

        foreach ($this->chart->groups($this->search) as $group) {
            foreach ($group['rows'] as $row) {
                $rows[] = [
                    $row['account']->code,
                    $row['account']->name,
                    $row['account']->kind_label,
                    $row['amount'],
                ];
            }
        }

        return CsvHelper::build(['Code', 'Account Name', 'Kind', 'Balance'], $rows);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Account::class);

        $groups = $this->chart->groups($this->search);
        $allGroups = $this->search === '' ? $groups : $this->chart->groups();
        $summary = [];

        foreach ($allGroups as $group) {
            $summary[$group['key']] = $group['total'];
        }

        return view('livewire.admin.accounting.chart-of-accounts.account-table-list', [
            'groups'  => $groups,
            'summary' => $summary,
        ]);
    }
}
