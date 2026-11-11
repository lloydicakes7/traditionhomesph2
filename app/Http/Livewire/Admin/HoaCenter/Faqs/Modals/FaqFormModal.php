<?php

namespace App\Http\Livewire\Admin\HoaCenter\Faqs\Modals;

use App\Actions\HoaCenter\Faqs\CreateFaq;
use App\Actions\HoaCenter\Faqs\UpdateFaq;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Faq;
use App\Queries\HoaCenter\Faqs\FaqListQuery;

class FaqFormModal extends AdminModalComponent
{
    public $isEditing = false;

    public $editingFaqId = null;

    public $form = [
        'question' => '',
        'answer'   => '',
    ];

    protected $listeners = [
        'openFaqFormModal'  => 'openCreate',
        'loadFaqFormModal'  => 'openEdit',
        'closeFaqFormModal' => 'closeModal',
    ];

    // ===== Setup =====

    /** @var FaqListQuery */
    protected $query;

    /** @var CreateFaq */
    protected $createFaq;

    /** @var UpdateFaq */
    protected $updateFaq;

    public function boot(FaqListQuery $query, CreateFaq $createFaq, UpdateFaq $updateFaq)
    {
        $this->query = $query;
        $this->createFaq = $createFaq;
        $this->updateFaq = $updateFaq;
    }

    // ===== Rules =====

    protected function rules(): array
    {
        return [
            'form.question' => ['required', 'string', 'max:255'],
            'form.answer'   => ['required', 'string', 'max:5000'],
        ];
    }

    // ===== Lifecycle =====

    public function openCreate(): void
    {
        $this->authorize('create', Faq::class);

        $this->isEditing = false;
        $this->editingFaqId = null;
        $this->form = [
            'question' => '',
            'answer'   => '',
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Faq::class);

        $faq = $this->query->find($id);

        $this->isEditing = true;
        $this->editingFaqId = $faq->id;
        $this->form = [
            'question' => $faq->question,
            'answer'   => $faq->answer,
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingFaqId = null;
        $this->resetValidation();

        parent::resetModal();
    }

    // ===== Actions =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', Faq::class);
        $data = $this->validate()['form'];

        if ($this->isEditing) {
            $this->updateFaq->handle($this->query->find($this->editingFaqId), $data, auth('web')->user());
        } else {
            $this->createFaq->handle($data, auth('web')->user());
        }

        $this->emit('notify', 'success',
            $this->isEditing ? 'FAQ updated successfully.' : 'FAQ added successfully.');

        $this->emit('faq-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'faqFormModal']);
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.hoa-center.faqs.modals.faq-form-modal');
    }
}
