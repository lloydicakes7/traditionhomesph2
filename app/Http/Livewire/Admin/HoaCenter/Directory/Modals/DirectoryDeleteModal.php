<?php

namespace App\Http\Livewire\Admin\HoaCenter\Directory\Modals;

use App\Actions\HoaCenter\Directory\DeleteDirectoryEntry;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Directory\DirectoryEntry;
use App\Queries\HoaCenter\Directory\DirectoryListQuery;

class DirectoryDeleteModal extends AdminModalComponent
{
    public $deletingEntryId = null;

    public $deletingEntryData = null;

    protected $listeners = [
        'loadDirectoryDeleteModal'  => 'load',
        'closeDirectoryDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var DirectoryListQuery */
    protected $query;

    /** @var DeleteDirectoryEntry */
    protected $deleteEntry;

    public function boot(DirectoryListQuery $query, DeleteDirectoryEntry $deleteEntry)
    {
        $this->query = $query;
        $this->deleteEntry = $deleteEntry;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', DirectoryEntry::class);

        $entry = $this->query->find($id);

        $this->deletingEntryId = $entry->id;
        $this->deletingEntryData = [
            'name'   => $entry->name,
            'role'   => $entry->role,
            'group'  => $entry->group ? $entry->group->name : null,
            'phones' => $entry->phone_list,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingEntryId = null;
        $this->deletingEntryData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingEntryId) {
            return;
        }

        $this->authorize('delete', DirectoryEntry::class);
        $this->deleteEntry->handle($this->query->find($this->deletingEntryId), auth('web')->user());

        $this->emit('notify', 'success', 'Directory entry deleted successfully.');
        $this->emit('directory-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'directoryDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.hoa-center.directory.modals.directory-delete-modal');
    }
}
