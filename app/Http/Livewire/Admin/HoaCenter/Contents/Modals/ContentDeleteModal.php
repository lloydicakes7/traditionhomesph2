<?php

namespace App\Http\Livewire\Admin\HoaCenter\Contents\Modals;

use App\Actions\HoaCenter\Contents\DeleteContent;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Content;
use App\Queries\HoaCenter\Contents\ContentListQuery;

class ContentDeleteModal extends AdminModalComponent
{
    public $deletingContentId = null;

    public $deletingContentData = null;

    protected $listeners = [
        'loadContentDeleteModal'  => 'load',
        'closeContentDeleteModal' => 'closeModal',
    ];

    // ===== Setup =====

    /** @var ContentListQuery */
    protected $query;

    /** @var DeleteContent */
    protected $deleteContent;

    public function boot(ContentListQuery $query, DeleteContent $deleteContent)
    {
        $this->query = $query;
        $this->deleteContent = $deleteContent;
    }

    // ===== Lifecycle =====

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Content::class);

        $content = $this->query->find($id);

        $this->deletingContentId = $content->id;
        $this->deletingContentData = [
            'title'     => $content->title,
            'group'     => $content->group,
            'url'       => $content->url,
            'has_image' => $content->image_path !== null,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingContentId = null;
        $this->deletingContentData = null;

        parent::resetModal();
    }

    // ===== Actions =====

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingContentId) {
            return;
        }

        $this->authorize('delete', Content::class);
        $this->deleteContent->handle($this->query->find($this->deletingContentId), auth('web')->user());

        $this->emit('notify', 'success', 'Page deleted successfully.');
        $this->emit('content-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'contentDeleteModal']);
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.hoa-center.contents.modals.content-delete-modal');
    }
}
