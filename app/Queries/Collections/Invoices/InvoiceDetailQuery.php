<?php

namespace App\Queries\Collections\Invoices;

use App\Models\Bill\Bill;

class InvoiceDetailQuery
{
    public function find(int $id): Bill
    {
        return Bill::query()->invoices()->with([
            'user.household.property.address',
            'user.resident',
            'items',
            'payments.paymentMethod',
            'createdByAdmin',
        ])->findOrFail($id);
    }

    public function findForEdit(int $id): Bill
    {
        return Bill::query()->invoices()->with(['items', 'user.household.property'])->findOrFail($id);
    }
}
