<?php

namespace App\Queries\Collections\MonthlyDues;

use App\Models\Bill\Bill;
use App\Models\Property;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Carbon;

class MonthlyDueMatrixQuery
{
    // ===== Rows =====

    public function matrix(?string $year = null, int $page = 1, int $perPage = 15): array
    {
        $matrix = $this->build($year);

        $matrix['rows'] = $this->paginateRows($matrix['rows'], $page, $perPage);

        return $matrix;
    }

    public function report(?string $year = null): array
    {
        return $this->build($year);
    }

    // ===== Helpers =====

    private function build(?string $year = null): array
    {
        $year = $year ?: Carbon::now()->format('Y');

        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $value = $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT);

            $months[] = [
                'value' => $value,
                'label' => Carbon::createFromFormat('Y-m', $value)->format('M'),
            ];
        }

        $yearOptions = PeriodHelper::years(
            Bill::query()->monthlyDues()->distinct()->pluck('billing_period')->all()
        );

        $properties = Property::query()
            ->withHousehold()
            ->with(['address', 'currentHousehold.user.resident'])
            ->orderBy('block')
            ->orderBy('lot')
            ->get();

        $dues = Bill::query()->monthlyDues()
            ->where('billing_period', 'like', $year . '-%')
            ->get()
            ->groupBy('user_id');

        $rows = [];
        $totals = array_fill_keys(array_column($months, 'value'), 0.0);
        $collected = array_fill_keys(array_column($months, 'value'), 0.0);
        $yearTotal = 0.0;
        $yearCollected = 0.0;
        $yearBalance = 0.0;

        foreach ($properties as $property) {
            $cells = [];
            $rowTotal = 0.0;
            $rowCollected = 0.0;
            $rowBalance = 0.0;

            $userId = $property->currentHousehold?->user_id;
            $bills = $userId !== null ? ($dues->get((string) $userId) ?? $dues->get($userId) ?? collect()) : collect();

            foreach ($months as $month) {
                $due = $bills->firstWhere('billing_period', $month['value']);

                $cells[$month['value']] = $due;

                if ($due) {
                    $rowTotal += (float) $due->total;
                    $rowCollected += (float) $due->paid_amount;
                    $rowBalance += (float) $due->balance;
                    $totals[$month['value']] += (float) $due->total;
                    $collected[$month['value']] += (float) $due->paid_amount;
                }
            }

            $yearTotal += $rowTotal;
            $yearCollected += $rowCollected;
            $yearBalance += $rowBalance;

            $rows[] = [
                'property'       => $property,
                'property_id'    => $property->id,
                'cells'          => $cells,
                'year_total'     => $rowTotal,
                'year_collected' => $rowCollected,
                'year_balance'   => $rowBalance,
                'row_total'      => MoneyHelper::format($rowTotal),
                'row_collected'  => MoneyHelper::format($rowCollected),
                'row_balance'    => MoneyHelper::format($rowBalance),
            ];
        }

        return [
            'year'              => $year,
            'year_options'      => $yearOptions,
            'months'            => $months,
            'rows'              => $rows,
            'totals'            => $totals,
            'totals_display'    => $this->formatAll($totals),
            'collected'         => $collected,
            'collected_display' => $this->formatAll($collected),
            'year_total'        => MoneyHelper::format($yearTotal),
            'year_collected'    => MoneyHelper::format($yearCollected),
            'year_balance'      => MoneyHelper::format($yearBalance),
        ];
    }

    private function formatAll(array $amounts): array
    {
        return array_map(function ($amount) {
            return MoneyHelper::format($amount);
        }, $amounts);
    }

    private function paginateRows(array $rows, int $page, int $perPage): LengthAwarePaginator
    {
        $page = max(1, $page);

        return new Paginator(
            array_slice($rows, ($page - 1) * $perPage, $perPage),
            count($rows),
            $perPage,
            $page
        );
    }
}
