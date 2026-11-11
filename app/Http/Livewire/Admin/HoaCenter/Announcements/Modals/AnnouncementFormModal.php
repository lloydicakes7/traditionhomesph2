<?php

namespace App\Http\Livewire\Admin\HoaCenter\Announcements\Modals;

use App\Actions\HoaCenter\Announcements\PublishAnnouncement;
use App\Actions\HoaCenter\Announcements\UpdateAnnouncement;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Announcement;
use App\Queries\HoaCenter\Announcements\AnnouncementListQuery;
use Livewire\WithFileUploads;

class AnnouncementFormModal extends AdminModalComponent
{
    use WithFileUploads;

    public $isEditing = false;

    public $editingAnnouncementId = null;

    public $form = [
        'title' => '',
        'body'  => '',
    ];

    public $announcementImage;

    public $existingAnnouncementImageUrl = null;

    public $existingAnnouncementDate = null;

    public $removeAnnouncementImage = false;

    protected $listeners = [
        'openAnnouncementFormModal'  => 'openCreate',
        'loadAnnouncementFormModal'  => 'openEdit',
        'closeAnnouncementFormModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AnnouncementListQuery */
    protected $query;

    /** @var PublishAnnouncement */
    protected $publishAnnouncement;

    /** @var UpdateAnnouncement */
    protected $updateAnnouncement;

    public function boot(
        AnnouncementListQuery $query,
        PublishAnnouncement $publishAnnouncement,
        UpdateAnnouncement $updateAnnouncement
    ) {
        $this->query = $query;
        $this->publishAnnouncement = $publishAnnouncement;
        $this->updateAnnouncement = $updateAnnouncement;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'form.title'         => ['required', 'string', 'max:200'],
            'form.body'          => ['required', 'string', 'max:10000'],
            'announcementImage'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Announcement::class);

        $this->isEditing = false;
        $this->editingAnnouncementId = null;
        $this->form = [
            'title' => '',
            'body'  => '',
        ];
        $this->resetImageState();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (!$id) {
            return;
        }

        $this->authorize('update', Announcement::class);

        $announcement = $this->query->find($id);

        $this->isEditing = true;
        $this->editingAnnouncementId = $announcement->id;
        $this->form = [
            'title' => $announcement->title,
            'body'  => $announcement->body,
        ];
        $this->resetImageState();
        $this->existingAnnouncementImageUrl = $announcement->image_url;
        $this->existingAnnouncementDate = $announcement->display_date;
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingAnnouncementId = null;
        $this->resetImageState();
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (!$this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', Announcement::class);
        $this->validate();

        if ($this->isEditing) {
            $this->updateAnnouncement->handle(
                $this->query->find($this->editingAnnouncementId),
                $this->form,
                $this->announcementImage,
                $this->removeAnnouncementImage,
                auth('web')->user()
            );
        } else {
            $this->publishAnnouncement->handle(
                $this->form,
                $this->announcementImage,
                auth('web')->user()
            );
        }

        $this->emit('notify', 'success',
            $this->isEditing
                ? 'Announcement updated successfully.'
                : 'Announcement published successfully.');

        $this->emit('announcement-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'announcementFormModal']);
    }

    // =====================================================================
    // Image handling
    // =====================================================================

    public function removeImage(): void
    {
        $this->authorize('update', Announcement::class);

        if ($this->announcementImage) {
            $this->resetImageState();

            return;
        }

        $this->removeAnnouncementImage = true;
        $this->existingAnnouncementImageUrl = null;

        $this->emit('notify', 'info', 'The announcement image will be removed on save.');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetImageState(): void
    {
        $this->announcementImage = null;
        $this->existingAnnouncementImageUrl = null;
        $this->existingAnnouncementDate = null;
        $this->removeAnnouncementImage = false;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.hoa-center.announcements.modals.announcement-form-modal');
    }
}
