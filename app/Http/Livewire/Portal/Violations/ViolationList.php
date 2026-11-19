<?php

namespace App\Http\Livewire\Portal\Violations;

use App\Http\Livewire\PortalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Queries\Support\Violations\PortalViolationQuery;

/**
 * Violations — the notices the association has issued against this household.
 *
 * The page reads the homeowner's record in the two parts that matter to them:
 * the violations that still stand (their current list) and the ones the office
 * has closed (their history, which stays for good — resolving a violation
 * never deletes it).
 *
 * Both lists are scoped to the signed-in account's own resident before they are
 * read, which is the ownership rule ViolationPolicy::viewOwn states, applied as
 * the query's boundary: another household's violations are never in the result
 * set to begin with.
 */
class ViolationList extends PortalComponent
{
    use ResolvesHomeownerAccount;

    /** @var PortalViolationQuery */
    protected $list;

    public function boot(PortalViolationQuery $list)
    {
        $this->list = $list;
    }

    // =====================================================================
    // Render
    // =====================================================================

    /**
     * Render the homeowner's current violations and their violation history.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.portal.violations.violation-list', [
            'violations' => $this->list->forResident($this->account()->resident_id),
        ]);
    }
}
