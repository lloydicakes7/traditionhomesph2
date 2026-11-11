<?php

namespace App\Http\Livewire\Portal\Statements;

use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalComponent;
use App\Queries\Collections\Payments\PortalPaymentQuery;

/**
 * Statement of Account — what this homeowner still has to pay.
 *
 * The rows are the account's own bills that still carry a balance, read
 * through `PortalPaymentQuery::openBills()` with the signed-in account as the
 * query's boundary, so another household's charges are never in the result
 * set — the ownership rule the portal applies everywhere.
 *
 * The page is read-only: nothing is paid online. The homeowner settles a bill
 * in person at the HOA office, where an officer records the receipt (see
 * \App\Actions\Collections\Payments\RecordPayment).
 */
class StatementList extends PortalComponent
{
    use ResolvesHomeownerAccount;

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
     * Render the homeowner's open bills and what they come to.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $account = $this->account();
        $bills = $this->query->openBills($account->id);

        return view('livewire.portal.statements.statement-list', [
            'bills'        => $bills,
            'totalBalance' => (float) $bills->sum('balance'),
        ]);
    }
}
