<?php

namespace App\Http\Livewire\Admin\HoaCenter\Announcements;

use App\Http\Livewire\AdminComponent;
use App\Models\Announcement;
use App\Queries\HoaCenter\Announcements\AnnouncementListQuery;
use Livewire\WithPagination;

class AnnouncementList extends AdminComponent
{
    use WithPagination;

    protected $queryString = ['search'];

    public $search = '';

    protected $listeners = [
        'announcement-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AnnouncementListQuery */
    protected $query;

    public function boot(AnnouncementListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Filters
    // =====================================================================

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', Announcement::class);

        return view('livewire.admin.hoa-center.announcements.announcement-list', [
            'announcements' => $this->query->paginate($this->search),
        ]);
    }
}
