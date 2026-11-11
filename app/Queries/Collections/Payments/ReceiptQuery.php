<?php

namespace App\Queries\Collections\Payments;

use App\Enums\PaymentStatus;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;

class ReceiptQuery
{
    // ===== Queries =====

    public function build(Payment $payment): array
    {
        $payment->load([
            'paymentMethod:id,name',
            'receivedByAdmin:id,name,role_id',
            'receivedByAdmin.role:id,name',
            'bill:id,reference,user_id,billed_to_name,type,billing_period,total,discount,penalty',
            'bill.user:id,resident_id',
            'bill.user.resident:id,first_name,middle_name,surname,suffix',
            'bill.user.household.property:id,block,lot',
            'bill.items:id,bill_id,name,quantity,unit_price,amount',
        ]);

        $bill = $payment->bill;

        return [
            'receipt_no'      => (string) $payment->receipt_no,
            'date_paid'       => $payment->paid_at ? $payment->paid_at->format('F j, Y') : '',
            'payer'           => $bill ? $bill->issued_to_name : '',
            'unit'            => $bill?->property?->block_lot_label ?? '—',
            'bill_reference'  => $bill->reference ?? '',
            'bill_period'     => $bill && $bill->billing_period ? PeriodHelper::label($bill->billing_period) : '—',
            'bill_type'       => $bill->type_label ?? '',
            'items'           => $this->items($bill),
            'total'           => MoneyHelper::format($bill->total ?? 0),
            'method'          => $this->method($payment),
            'reference_no'    => (string) ($payment->reference_no ?: ''),
            'amount_paid'     => MoneyHelper::format($payment->amount),
            'amount_in_words' => MoneyHelper::toWords((string) $payment->amount),
            'balance_after'   => MoneyHelper::format($this->balanceAfter($payment, $bill)),
            'notes'           => (string) ($payment->notes ?? ''),
            'received_by'     => (string) ($payment->receivedByAdmin?->name ?? ''),
            'received_by_role' => (string) ($payment->receivedByAdmin?->role?->name ?? ''),
        ];
    }

    // ===== Helpers =====

    private function items(?Bill $bill): array
    {
        if (! $bill) {
            return [];
        }

        return $bill->items->map(function ($item) {
            return [
                'name'       => $item->name,
                'quantity'   => (string) (float) $item->quantity,
                'unit_price' => MoneyHelper::format($item->unit_price),
                'amount'     => MoneyHelper::format($item->amount),
            ];
        })->all();
    }

    private function method(Payment $payment): string
    {
        if ($payment->payment_method_id) {
            return (string) ($payment->paymentMethod?->name ?? '');
        }

        if ($payment->advance_deposit_id) {
            return __('payments.receipt.advance_deposit') . ' #' . $payment->advance_deposit_id;
        }

        return PaymentMethod::IN_PERSON_LABEL;
    }

    private function balanceAfter(Payment $payment, ?Bill $bill): float
    {
        if (! $bill) {
            return 0.0;
        }

        $paid = Payment::query()
            ->where('bill_id', $bill->id)
            ->where('status', PaymentStatus::RECORDED)
            ->where(function ($query) use ($payment) {
                $query->where('paid_at', '<', $payment->paid_at)
                    ->orWhere(function ($query) use ($payment) {
                        $query->where('paid_at', $payment->paid_at)
                            ->where('id', '<=', $payment->id);
                    });
            })
            ->sum('amount');

        return max(0.0, round((float) $bill->total - (float) $paid, 2));
    }
}
