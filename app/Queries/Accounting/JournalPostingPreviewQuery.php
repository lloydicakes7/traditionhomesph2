<?php

namespace App\Queries\Accounting;

use App\Models\Account\Account;
use App\Models\Expense\Expense;
use App\Models\Payment;
use App\Services\Accounting\ExpensePostingService;
use App\Services\Accounting\PaymentPostingService;

/**
 * The entry a payment or an expense carries, as the posting modal opens it.
 *
 * Nothing here is computed twice: the figures come from the same draft the
 * posting service writes, so the officer starts from the entry the system
 * would post and may change it before it reaches the books.
 */
class JournalPostingPreviewQuery
{
    /** @var PaymentPostingService */
    private $payments;

    /** @var ExpensePostingService */
    private $expenses;

    public function __construct(PaymentPostingService $payments, ExpensePostingService $expenses)
    {
        $this->payments = $payments;
        $this->expenses = $expenses;
    }

    // ===== Queries =====

    public function payment(Payment $payment, Account $money): array
    {
        return $this->present($this->payments->draft($payment, $money));
    }

    public function expense(Expense $expense, Account $money): array
    {
        return $this->present($this->expenses->draft($expense, $money));
    }

    // ===== Helpers =====

    private function present(array $draft): array
    {
        return [
            'entry_date'     => $draft['entry_date'],
            'classification' => (string) $draft['classification'],
            'reference_no'   => (string) $draft['reference_no'],
            'memo'           => (string) $draft['memo'],
            'lines'          => array_map(function (array $line) {
                return [
                    'account_id' => (string) $line['account_id'],
                    'side'       => (string) $line['side'],
                    'amount'     => bcadd((string) $line['amount'], '0', 2),
                ];
            }, $draft['lines']),
        ];
    }
}
