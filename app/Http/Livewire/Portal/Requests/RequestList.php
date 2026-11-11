<?php

namespace App\Http\Livewire\Portal\Requests;

use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalComponent;
use App\Queries\Support\Requests\PortalRequestQuery;

/**
 * Requests — what this homeowner has asked the office for, and the way to ask
 * for something else.
 *
 * The list is scoped to the signed-in account's own resident before it is read
 * (RequestPolicy::viewOwn applied as the query's own boundary), so another
 * household's request is never in the result set. Each card reads the way the
 * portal reads a complaint: what was asked for, when it was filed, where it
 * stands, and — the part a submitter is actually waiting for — the office's
 * remarks once it has acted.
 *
 * Filing is offered always: who may file is RequestPolicy::createOwn, and the
 * request is filed under the account's own resident, never somebody else's.
 */
class RequestList extends PortalComponent
{
    use ResolvesHomeownerAccount;

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'request-filed'      => '$refresh',
        'request-withdrawn'  => '$refresh',
    ];

    /** @var PortalRequestQuery */
    protected $query;

    public function boot(PortalRequestQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Render
    // =====================================================================

    /**
     * Render the homeowner's own requests and the office's answers.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.portal.requests.request-list', [
            'requests' => $this->query->all($this->account()->resident_id),
            'canFile'  => (bool) $this->account()->resident_id,
        ]);
    }
}
