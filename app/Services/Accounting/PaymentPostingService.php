<?php

namespace App\Services\Accounting;

use App\Enums\JournalClassification;
use App\Enums\JournalSource;
use App\Models\Account\Account;
use App\Models\Bill\Bill;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Writes the books when accounting posts a receipt.
 *
 * A receipt has one entry: the cash account accounting chose is debited, and
 * the income it was earned for is credited — one credit per product category
 * the bill's lines were filed under, so a partial payment still books against
 * the right accounts. The amount always comes from the payment, never from a
 * form, and the entry is written through JournalEntry::post().
 */
class PaymentPostingService
{
    // ===== Operations =====

    /**
     * Post the entry for one recorded receipt and remember it on the payment.
     */
    public function post(Payment $payment, Account $money, int $adminId): JournalEntry
    {
        if ($payment->journal_entry_id) {
            return $payment->journalEntry()->firstOrFail();
        }

        if (! $money->is_cash) {
            throw ValidationException::withMessages([
                'account_id' => 'Choose a cash account — cash on hand or a bank account.',
            ]);
        }

        return DB::transaction(function () use ($payment, $money, $adminId) {
            $entry = JournalEntry::post([
                'entry_date'     => $payment->paid_at ?: now(),
                'classification' => JournalClassification::STANDARD,
                'source_type'    => JournalSource::PAYMENT,
                'source_id'      => $payment->id,
                'reference_no'   => $payment->reference_no,
                'memo'           => $this->memo($payment),
                'lines'          => $this->lines($payment, $money),
            ], $adminId);

            $payment->update(['journal_entry_id' => $entry->id]);

            return $entry;
        });
    }

    // ===== Helpers =====

    /**
     * The balanced lines: the money received, then the income it earned.
     *
     * @return array<int, array<string, mixed>>
     */
    private function lines(Payment $payment, Account $money): array
    {
        $amount = bcadd((string) $payment->amount, '0', 2);

        $lines = [[
            'account_id' => $money->id,
            'side'       => JournalLine::SIDE_DEBIT,
            'amount'     => $amount,
            'memo'       => 'Payment received',
        ]];

        foreach ($this->shares($payment) as $share) {
            $lines[] = [
                'account_id' => $share['account']->id,
                'side'       => JournalLine::SIDE_CREDIT,
                'amount'     => $share['amount'],
                'memo'       => 'Income · ' . ($payment->bill ? $payment->bill->reference : 'Payment'),
            ];
        }

        return $lines;
    }

    /**
     * How much of the receipt each income account earns.
     *
     * A full payment takes each bill line's own amount; a part payment splits
     * the money in the same proportion. The last share carries the rounding so
     * the credits always come to exactly the amount received.
     *
     * @return array<int, array{account: Account, amount: string}>
     */
    private function shares(Payment $payment): array
    {
        $amount = bcadd((string) $payment->amount, '0', 2);
        $bill = $payment->bill;

        if (! $bill) {
            return [['account' => Account::feesIncome(), 'amount' => $amount]];
        }

        $bill->loadMissing('items.product.category');
        $subtotal = bcadd((string) $bill->subtotal, '0', 2);

        if (bccomp($subtotal, '0', 2) <= 0 || $bill->items->isEmpty()) {
            return [['account' => $this->incomeAccountFor($bill), 'amount' => $amount]];
        }

        $groups = [];

        foreach ($bill->items as $item) {
            $lineAmount = bcadd((string) $item->amount, '0', 2);

            if (bccomp($lineAmount, '0', 2) <= 0) {
                continue;
            }

            $account = $this->incomeAccountFor($bill, $item->product);

            if (! isset($groups[$account->id])) {
                $groups[$account->id] = ['account' => $account, 'amount' => '0.00'];
            }

            $groups[$account->id]['amount'] = bcadd($groups[$account->id]['amount'], $lineAmount, 2);
        }

        if (empty($groups)) {
            return [['account' => $this->incomeAccountFor($bill), 'amount' => $amount]];
        }

        $groups = array_values($groups);

        if (bccomp($amount, $subtotal, 2) === 0) {
            return $groups;
        }

        $shares = [];
        $credited = '0.00';

        foreach ($groups as $index => $group) {
            if ($index === count($groups) - 1) {
                $share = bcsub($amount, $credited, 2);
            } else {
                $share = bcdiv(bcmul($group['amount'], $amount, 4), $subtotal, 2);
                $credited = bcadd($credited, $share, 2);
            }

            if (bccomp($share, '0', 2) > 0) {
                $shares[] = ['account' => $group['account'], 'amount' => $share];
            }
        }

        return $shares ?: [['account' => $this->incomeAccountFor($bill), 'amount' => $amount]];
    }

    /**
     * The income account one of a bill's lines earns to.
     */
    private function incomeAccountFor(Bill $bill, $product = null): Account
    {
        $type = $bill->type_enum;

        if ($type && $type->isMonthlyDue()) {
            return Account::duesIncome();
        }

        if ($type && $type->isAmenityBooking()) {
            return Account::amenityIncome();
        }

        return Account::forProductCategory($product ? $product->category : null);
    }

    /**
     * What the receipt's entry is called — the OR number and who paid.
     */
    private function memo(Payment $payment): string
    {
        $payment->loadMissing('user');

        return 'Payment ' . ($payment->reference_no ?: '#' . $payment->id)
            . ' · ' . ($payment->user ? $payment->user->name : $payment->bill_reference);
    }
}
