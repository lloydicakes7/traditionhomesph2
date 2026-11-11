<?php

namespace App\Queries\Collections\Payments;

use App\Models\Bill\Bill;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class PaymentDetailQuery
{
    public function find(int $id): Payment
    {
        return Payment::with(['bill.user.household.property', 'user', 'paymentMethod', 'advanceDeposit'])
            ->findOrFail($id);
    }

    public function forBill(Bill $bill): Collection
    {
        return Payment::query()
            ->with('paymentMethod')
            ->where('bill_id', $bill->id)
            ->ordered()
            ->get();
    }
}
