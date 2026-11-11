<?php

namespace App\Queries\Reports;

use App\Models\Payment;

/**
 * The day's collections, as the treasurer's register writes them.
 *
 * This is the money the association actually took in on a date, read from the
 * payment ledger itself rather than from the books: who paid, for which unit,
 * what it settled and how the money arrived, with what the day came to. Money
 * drawn from credit a homeowner has paid ahead is left out — as the report
 * says, it is credit being applied, not money received that day.
 */
class CollectionReportQuery
{
    /**
     * Every payment received on a date, with the day's totals.
     *
     * @param  string  $date
     * @return array<string, mixed>
     */
    public function daily(string $date): array
    {
        $payments = Payment::query()
            ->with(['bill', 'user.household.property', 'paymentMethod'])
            ->whereNull('advance_deposit_id')
            ->whereDate('paid_at', $date)
            ->orderBy('id')
            ->get();

        $rows = [];
        $total = 0.0;
        $byMethod = [];

        foreach ($payments as $payment) {
            $method = $payment->method_label;
            $amount = (float) $payment->amount;

            // A payment belongs to no unit of its own: the unit is the one the
            // paying account occupies, and who paid is the name the bill was
            // written for — the same path the rest of the app reaches a unit by.
            $rows[] = [
                'reference'   => $payment->reference_no ?: ('#' . $payment->id),
                'property'    => $payment->user?->household?->property,
                'homeowner'   => $payment->bill?->issued_to_name
                    ?: ($payment->user?->name ?: '—'),
                'particulars' => $payment->bill_reference . ' · ' . $payment->bill_type_label,
                'method'      => $method,
                'amount'      => $amount,
            ];

            $total += $amount;
            $byMethod[$method] = round(($byMethod[$method] ?? 0) + $amount, 2);
        }

        return [
            'date'      => $date,
            'rows'      => $rows,
            'count'     => $payments->count(),
            'total'     => round($total, 2),
            'by_method' => $byMethod,
        ];
    }
}
