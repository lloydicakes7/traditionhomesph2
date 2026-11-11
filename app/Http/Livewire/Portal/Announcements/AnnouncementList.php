<?php

namespace App\Http\Livewire\Portal\Announcements;

use App\Http\Livewire\PortalComponent;
use App\Queries\HoaCenter\Announcements\AnnouncementListQuery;
use Livewire\WithPagination;

/**
 * Announcements — the notices the association's officers publish.
 *
 * An announcement is public data: the public site publishes the same list from
 * the same query, and a signed-in homeowner sees nothing an anonymous visitor
 * cannot. There is therefore no ownership rule to check here — the route's
 * `auth:homeowner` middleware is the whole gate.
 *
 * The list is read the way every list in the app is: the search box narrows it
 * through the query that owns it, and the page it is on is part of the URL. The
 * term is applied when the homeowner submits it — Enter or the Search button —
 * so the list is only narrowed when they ask for it.
 */
class AnnouncementList extends PortalComponent
{
    use WithPagination;

    /** @var array<string> Query-string persistent state (page comes from WithPagination) */
    protected $queryString = ['search'];

    // =====================================================================
    // List state
    // =====================================================================

    /** @var string Search query */
    public $search = '';

    /** @var AnnouncementListQuery */
    protected $query;

    public function boot(AnnouncementListQuery $query)
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
        return view('livewire.portal.announcements.announcement-list', [
            'announcements' => $this->query->paginate($this->search),
        ]);
    }
}
