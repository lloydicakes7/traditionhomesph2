<?php

namespace App\Queries\Admin;

use App\Enums\BillStatus;
use App\Models\Bill\Bill;
use App\Models\User;
use App\Queries\Reports\ExpenseReportQuery;
use App\Queries\Reports\IncomeReportQuery;
use App\Queries\Reports\ResidentReportQuery;
use App\Queries\Support\Requests\RequestListQuery;
use App\Queries\Support\Violations\ViolationListQuery;
use App\Utility\PeriodHelper;
use Illuminate\Database\Eloquent\Collection;

class DashboardQuery
{
    private RequestListQuery $requests;

    private ViolationListQuery $violations;

    private ResidentReportQuery $residentReports;

    private IncomeReportQuery $incomeReports;

    private ExpenseReportQuery $expenseReports;

    public function __construct(
        RequestListQuery $requests,
        ViolationListQuery $violations,
        ResidentReportQuery $residentReports,
        IncomeReportQuery $incomeReports,
        ExpenseReportQuery $expenseReports
    ) {
        $this->requests = $requests;
        $this->violations = $violations;
        $this->residentReports = $residentReports;
        $this->incomeReports = $incomeReports;
        $this->expenseReports = $expenseReports;
    }

    // ===== Queries =====

    /**
     * The dashboard's headline figures, each counted from the records it names.
     *
     * @return array{homeowners: int, paid_bills: int, open_requests: int, active_violations: int}
     */
    public function summary(): array
    {
        return [
            'homeowners'        => User::count(),
            'paid_bills'        => Bill::query()->where('status', BillStatus::PAID)->count(),
            'open_requests'     => $this->requests->openCount(),
            'active_violations' => $this->violations->activeCount(),
        ];
    }

    /**
     * The figures behind the dashboard's charts, read from the same queries
     * their report pages read.
     *
     * @return array<string, mixed>
     */
    public function charts(): array
    {
        $year = (int) now()->format('Y');
        $gender = $this->residentReports->genderDistribution();

        return [
            'year'      => $year,
            'gender'    => $gender,
            'residents' => array_sum(array_column($gender, 'value')),
            'income'    => $this->monthRows($this->incomeReports->monthly($year)['months'], $year),
            'expenses'  => $this->monthRows($this->expenseReports->monthly($year)['months'], $year),
        ];
    }

    public function recentViolations(int $limit = 5): Collection
    {
        return $this->violations->recentActive($limit);
    }

    public function recentRequests(int $limit = 5): Collection
    {
        return $this->requests->recentOpen($limit);
    }

    // ===== Helpers =====

    private function monthRows(array $months, int $year): array
    {
        $rows = [];

        foreach ($months as $month => $amount) {
            $rows[] = [
                'label' => PeriodHelper::shortLabel($year . '-' . $month),
                'value' => $amount,
            ];
        }

        return $rows;
    }
}
