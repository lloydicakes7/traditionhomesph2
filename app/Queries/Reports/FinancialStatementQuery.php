<?php

namespace App\Queries\Reports;

use App\Enums\AccountType;
use App\Models\Account\Account;
use App\Queries\Accounting\ChartOfAccountsQuery;

/**
 * The statements the association reports on itself with: the profit and loss
 * statement and the cash flow statement.
 *
 * Both are readings of the same balances — what each account holds, from the
 * postings behind it (see \App\Models\Account\Account) — presented the way a
 * statement presents them.
 */
class FinancialStatementQuery
{
    /** @var ChartOfAccountsQuery */
    private $accounts;

    public function __construct(ChartOfAccountsQuery $accounts)
    {
        $this->accounts = $accounts;
    }

    // =====================================================================
    // Profit and loss
    // =====================================================================

    /**
     * What the association earned and what it spent over a period, and the
     * difference between them.
     *
     * @return array<string, mixed>
     */
    public function profitAndLoss(?string $from, ?string $to): array
    {
        $revenue = $this->accounts->rowsOfTypes([AccountType::INCOME()], $from, $to);
        $expenses = $this->accounts->rowsOfTypes([AccountType::EXPENSE()], $from, $to);

        $totalRevenue = $this->total($revenue);
        $totalExpenses = $this->total($expenses);
        $netIncome = bcsub($totalRevenue, $totalExpenses, 2);

        return [
            'from'           => $from,
            'to'             => $to,
            'revenue'        => $revenue,
            'expenses'       => $expenses,
            'total_revenue'  => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income'     => $netIncome,
            'profitable'     => bccomp($netIncome, '0.00', 2) >= 0,
        ];
    }

    // =====================================================================
    // Cash flow
    // =====================================================================

    /**
     * How the association's money moved over a period, account by account.
     *
     * Only the accounts money sits in are read — the cash box, the bank, the
     * GCash account — so the statement answers one question: what came in, what
     * went out, and what the association holds at the end of it.
     *
     * @return array<string, mixed>
     */
    public function cashFlow(?string $from, ?string $to): array
    {
        $rows = [];
        $totalIn = '0.00';
        $totalOut = '0.00';
        $opening = '0.00';

        foreach (Account::query()->cash()->ordered()->get() as $account) {
            $openingBalance = $this->openingFor($account, $from);
            $totals = $this->accounts->totalsFor($account->id, $from, $to);

            $moneyIn = $totals['debits'];
            $moneyOut = $totals['credits'];

            if (bccomp($moneyIn, '0.00', 2) === 0
                && bccomp($moneyOut, '0.00', 2) === 0
                && bccomp($openingBalance, '0.00', 2) === 0) {
                continue;
            }

            $rows[] = [
                'account' => $account,
                'opening' => $openingBalance,
                'in'      => $moneyIn,
                'out'     => $moneyOut,
                'net'     => bcsub($moneyIn, $moneyOut, 2),
                'closing' => bcadd(bcsub($openingBalance, $moneyOut, 2), $moneyIn, 2),
            ];

            $opening = bcadd($opening, $openingBalance, 2);
            $totalIn = bcadd($totalIn, $moneyIn, 2);
            $totalOut = bcadd($totalOut, $moneyOut, 2);
        }

        $closing = bcadd(bcsub($opening, $totalOut, 2), $totalIn, 2);

        return [
            'from'        => $from,
            'to'          => $to,
            'rows'        => $rows,
            'opening'     => $opening,
            'total_in'    => $totalIn,
            'total_out'   => $totalOut,
            'net_change'  => bcsub($totalIn, $totalOut, 2),
            'closing'     => $closing,
            'increased'   => bccomp($closing, $opening, 2) >= 0,
        ];
    }

    /**
     * What a cash account held before a period began.
     */
    private function openingFor(Account $account, ?string $from): string
    {
        if (! $from) {
            return '0.00';
        }

        $totals = $this->accounts->totalsFor($account->id, null, now()->parse($from)->subDay()->toDateString());

        return bcsub($totals['debits'], $totals['credits'], 2);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * What a statement's rows come to. Each row is an account with the balance
     * its postings give it, read on the side that account normally carries —
     * so a contra account or an overdrawn one subtracts, as it should.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function total(array $rows): string
    {
        $total = '0.00';

        foreach ($rows as $row) {
            $total = bcadd($total, $row['signed'], 2);
        }

        return $total;
    }
}
