<?php

namespace App\Http\Livewire\Portal\Violations;

use App\Http\Livewire\PortalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Actions\Support\Violations\AcknowledgeViolation;
use App\Queries\Support\Violations\PortalViolationQuery;
use App\Queries\Support\Violations\ViolationDetailQuery;

/**
 * Violations — the notices the association has issued against this household.
 *
 * The page reads the homeowner's record in the two parts that matter to them:
 * the violations that still stand (their current list, where acknowledging
 * lives) and the ones the office has closed (their history, which stays for
 * good — resolving a violation never deletes it).
 *
 * Both lists are scoped to the signed-in account's own resident before they are
 * read, which is the ownership rule ViolationPolicy::viewOwn states, applied as
 * the query's boundary: another household's violations are never in the result
 * set to begin with.
 *
 * The one thing a homeowner does here is acknowledge a notice — confirming they
 * received and read it. That is not an appeal and not an agreement, so it never
 * changes the violation's status.
 */
class ViolationList extends PortalComponent
{
    use ResolvesHomeownerAccount;

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'violation-acknowledged' => '$refresh',
    ];

    /** @var ViolationDetailQuery */
    protected $detail;

    /** @var PortalViolationQuery */
    protected $list;

    /** @var AcknowledgeViolation */
    protected $acknowledge;

    public function boot(ViolationDetailQuery $detail, PortalViolationQuery $list, AcknowledgeViolation $acknowledge)
    {
        $this->detail = $detail;
        $this->list = $list;
        $this->acknowledge = $acknowledge;
    }

    // =====================================================================
    // Actions
    // =====================================================================

    /**
     * Confirm the notice was received and read.
     *
     * Ownership and whether there is anything to acknowledge are the portal side
     * of the policy's call; the write itself is the service's.
     *
     * @param  mixed  $id
     * @return void
     */
    public function acknowledge($id): void
    {
        $violation = $this->detail->find((int) $id);

        $this->authorize('acknowledgeOwn', $violation);

        $this->acknowledge->handle($violation);

        $this->emit('notify', 'success', 'Thank you — the association has recorded that you received this notice.');
        $this->emit('violation-acknowledged');
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
