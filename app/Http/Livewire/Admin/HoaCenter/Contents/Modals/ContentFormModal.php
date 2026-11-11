<?php

namespace App\Http\Livewire\Admin\HoaCenter\Contents\Modals;

use App\Actions\HoaCenter\Contents\CreateContent;
use App\Actions\HoaCenter\Contents\UpdateContent;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Content;
use App\Queries\HoaCenter\Contents\ContentListQuery;
use Livewire\WithFileUploads;

class ContentFormModal extends AdminModalComponent
{
    use WithFileUploads;

    public $isEditing = false;

    public $editingContentId = null;

    public $editingContentSlug = null;

    public $form = [
        'title'   => '',
        'group'   => '',
        'details' => '',
    ];

    public $contentImage;

    public $existingContentImageUrl = null;

    public $removeContentImage = false;

    protected $listeners = [
        'openContentFormModal'  => 'openCreate',
        'loadContentFormModal'  => 'openEdit',
        'closeContentFormModal' => 'closeModal',
    ];

    // ===== Setup =====

    /** @var ContentListQuery */
    protected $query;

    /** @var CreateContent */
    protected $createContent;

    /** @var UpdateContent */
    protected $updateContent;

    public function boot(
        ContentListQuery $query,
        CreateContent $createContent,
        UpdateContent $updateContent
    ) {
        $this->query = $query;
        $this->createContent = $createContent;
        $this->updateContent = $updateContent;
    }

    // ===== Rules =====

    protected function rules(): array
    {
        return [
            'form.title'   => ['required', 'string', 'max:150'],
            'form.group'   => ['nullable', 'string', 'max:100'],
            'form.details' => ['required', 'string', 'max:20000'],
            'contentImage' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    // ===== Lifecycle =====

    public function openCreate(): void
    {
        $this->authorize('create', Content::class);

        $this->isEditing = false;
        $this->editingContentId = null;
        $this->editingContentSlug = null;
        $this->form = [
            'title'   => '',
            'group'   => '',
            'details' => '',
        ];
        $this->resetImageState();
        $this->resetValidation();
        $this->markLoaded();

        $this->refreshEditor('');
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Content::class);

        $content = $this->query->find($id);

        $this->isEditing = true;
        $this->editingContentId = $content->id;
        $this->editingContentSlug = $content->slug;
        $this->form = [
            'title'   => $content->title,
            'group'   => (string) $content->group,
            'details' => $content->details,
        ];
        $this->resetImageState();
        $this->existingContentImageUrl = $content->image_url;
        $this->resetValidation();
        $this->markLoaded();

        $this->refreshEditor($content->details);
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingContentId = null;
        $this->editingContentSlug = null;
        $this->resetImageState();
        $this->resetValidation();

        parent::resetModal();
    }

    // ===== Actions =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', Content::class);
        $data = $this->validate()['form'];

        if ($this->isEditing) {
            $this->updateContent->handle(
                $this->query->find($this->editingContentId),
                $data,
                $this->contentImage,
                $this->removeContentImage,
                auth('web')->user()
            );
        } else {
            $this->createContent->handle($data, $this->contentImage, auth('web')->user());
        }

        $this->emit('notify', 'success',
            $this->isEditing ? 'Page updated successfully.' : 'Page created successfully.');

        $this->emit('content-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'contentFormModal']);
    }

    public function removeImage(): void
    {
        $this->authorize('update', Content::class);

        if ($this->contentImage) {
            $this->resetImageState();

            return;
        }

        $this->removeContentImage = true;
        $this->existingContentImageUrl = null;

        $this->emit('notify', 'info', 'The page image will be removed on save.');
    }

    // ===== Helpers =====

    private function refreshEditor(string $value): void
    {
        $this->dispatchBrowserEvent('thha:rich-text', [
            'id'    => 'contentDetails',
            'value' => $value,
        ]);
    }

    private function resetImageState(): void
    {
        $this->contentImage = null;
        $this->existingContentImageUrl = null;
        $this->removeContentImage = false;
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.hoa-center.contents.modals.content-form-modal');
    }
}
