<?php

namespace App\Http\Livewire\Public\Announcements;

use App\Http\Livewire\PublicModalComponent;
use App\Models\Announcement;
use App\Queries\HoaCenter\Announcements\AnnouncementListQuery;

class AnnouncementViewModal extends PublicModalComponent
{
    public $announcement = null;

    protected $listeners = [
        'loadAnnouncementViewModal'  => 'load',
        'closeAnnouncementViewModal' => 'closeModal',
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
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->announcement = $this->query->find($id);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->announcement = null;

        parent::resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.public.announcements.announcement-view-modal');
    }
}
