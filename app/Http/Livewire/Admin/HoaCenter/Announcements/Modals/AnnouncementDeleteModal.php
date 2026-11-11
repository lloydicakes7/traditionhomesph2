<?php

namespace App\Http\Livewire\Admin\HoaCenter\Announcements\Modals;

use App\Actions\HoaCenter\Announcements\DeleteAnnouncement;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Announcement;
use App\Queries\HoaCenter\Announcements\AnnouncementListQuery;

class AnnouncementDeleteModal extends AdminModalComponent
{
    /** @var int|null ID of the post being deleted */
    public $deletingAnnouncementId = null;

    /** @var array|null Summary of the post shown in the modal */
    public $deletingAnnouncementData = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAnnouncementDeleteModal'  => 'load',
        'closeAnnouncementDeleteModal' => 'closeModal',
    ];

    /** @var AnnouncementListQuery */
    protected $query;

    /** @var DeleteAnnouncement */
    protected $deleteAnnouncement;

    public function boot(AnnouncementListQuery $query, DeleteAnnouncement $deleteAnnouncement)
    {
        $this->query = $query;
        $this->deleteAnnouncement = $deleteAnnouncement;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Announcement::class);

        $announcement = $this->query->find($id);

        $this->deletingAnnouncementId = $announcement->id;
        $this->deletingAnnouncementData = [
            'title'     => $announcement->title,
            'date'      => $announcement->display_date,
            'has_image' => $announcement->image_path !== null,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingAnnouncementId = null;
        $this->deletingAnnouncementData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingAnnouncementId) {
            return;
        }

        $this->authorize('delete', Announcement::class);
        $this->deleteAnnouncement->handle($this->query->find($this->deletingAnnouncementId), auth('web')->user());

        $this->emit('notify', 'success', 'Announcement deleted successfully.');
        $this->emit('announcement-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'announcementDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.hoa-center.announcements.modals.announcement-delete-modal');
    }
}
