<?php

namespace App\Http\Livewire\Portal\Complaints;

use App\Http\Livewire\PortalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Queries\Support\Complaints\PortalComplaintQuery;

/**
 * Complaints — the complaints this homeowner has filed with the association,
 * and the way to file another.
 *
 * The list is scoped to the signed-in account's own resident before it is read
 * (ComplaintPolicy::viewOwn), so another household's complaint is never in the
 * result set. Each card reads the way the portal reads a booking: what it is
 * about, when it was filed, where it stands, and — the part a submitter is
 * actually waiting for — the office's response once one has been written.
 *
 * Filing is offered always: who may file is ComplaintPolicy::createOwn, and the
 * submitter is the account's own resident, never somebody else.
 */
class ComplaintList extends PortalComponent
{
    use ResolvesHomeownerAccount;

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'complaint-filed' => '$refresh',
    ];

    /** @var PortalComplaintQuery */
    protected $query;

    public function boot(PortalComplaintQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Render
    // =====================================================================

    /**
     * Render the homeowner's own complaints.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.portal.complaints.complaint-list', [
            'complaints' => $this->query->all($this->account()->resident_id),
            'canFile'    => (bool) $this->account()->resident_id,
        ]);
    }
}
