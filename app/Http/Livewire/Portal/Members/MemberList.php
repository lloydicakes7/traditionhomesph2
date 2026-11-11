<?php

namespace App\Http\Livewire\Portal\Members;

use App\Http\Livewire\PortalComponent;
use App\Queries\HoaCenter\Members\MemberListQuery;
use Livewire\WithPagination;

/**
 * HOA Members — the association's officers and staff, as the association
 * publishes them.
 *
 * A member record is public data: the public site's members page lists the same
 * rows from the same query, so there is no ownership rule to check here — the
 * route's `auth:homeowner` middleware is the whole gate, exactly as it is on the
 * portal's Announcements page.
 *
 * The list is read the way every list in the app is: the search box narrows it
 * through the query that owns it, and the page it is on is part of the URL.
 */
class MemberList extends PortalComponent
{
    use WithPagination;

    /** @var array<string> Query-string persistent state (page comes from WithPagination) */
    protected $queryString = ['search'];

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var MemberListQuery */
    protected $query;

    public function boot(MemberListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function applySearch(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.portal.members.member-list', [
            'members' => $this->query->paginate($this->search),
        ]);
    }
}
