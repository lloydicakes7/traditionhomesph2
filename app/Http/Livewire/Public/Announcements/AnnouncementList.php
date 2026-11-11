<?php

namespace App\Http\Livewire\Public\Announcements;

use App\Queries\HoaCenter\Announcements\AnnouncementListQuery;
use App\Http\Livewire\PublicComponent;
use Livewire\WithPagination;

class AnnouncementList extends PublicComponent
{
    use WithPagination;

    protected $queryString = ['search' => ['except' => '']];

    public $search = '';

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
    // Search
    // =====================================================================

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.public.announcements.announcement-list', [
            'announcements' => $this->query->paginate((string) $this->search),
        ]);
    }
}
