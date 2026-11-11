<?php

namespace App\Http\Livewire\Portal\Payments;

use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalComponent;
use App\Queries\Collections\Payments\PortalPaymentQuery;

/**
 * Payments — every payment this homeowner account has filed, and where each
 * one stands.
 *
 * The list is read through `PortalPaymentQuery::forUser()` with the signed-in
 * account as the query's boundary, so another household's receipts are never
 * in the result set. Each row carries its own status: a payment the office
 * has recorded is settled against its bill, one still pending is waiting for
 * the office to verify it (see \App\Enums\PaymentStatus) — which is the part
 * the homeowner is actually watching for.
 */
class PaymentList extends PortalComponent
{
    use ResolvesHomeownerAccount;

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'payment-submitted' => '$refresh',
    ];

    /** @var PortalPaymentQuery */
    protected $query;

    public function boot(PortalPaymentQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Render
    // =====================================================================

    /**
     * Render the homeowner's own payments, newest first.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.portal.payments.payment-list', [
            'payments' => $this->query->forUser($this->account()->id),
        ]);
    }
}
