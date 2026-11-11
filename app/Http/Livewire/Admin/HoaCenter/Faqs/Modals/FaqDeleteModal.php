<?php

namespace App\Http\Livewire\Admin\HoaCenter\Faqs\Modals;

use App\Actions\HoaCenter\Faqs\DeleteFaq;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Faq;
use App\Queries\HoaCenter\Faqs\FaqListQuery;

class FaqDeleteModal extends AdminModalComponent
{
    public $deletingFaqId = null;

    public $deletingFaqData = null;

    protected $listeners = [
        'loadFaqDeleteModal'  => 'load',
        'closeFaqDeleteModal' => 'closeModal',
    ];

    // ===== Setup =====

    /** @var FaqListQuery */
    protected $query;

    /** @var DeleteFaq */
    protected $deleteFaq;

    public function boot(FaqListQuery $query, DeleteFaq $deleteFaq)
    {
        $this->query = $query;
        $this->deleteFaq = $deleteFaq;
    }

    // ===== Lifecycle =====

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Faq::class);

        $faq = $this->query->find($id);

        $this->deletingFaqId = $faq->id;
        $this->deletingFaqData = [
            'question' => $faq->question,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingFaqId = null;
        $this->deletingFaqData = null;

        parent::resetModal();
    }

    // ===== Actions =====

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingFaqId) {
            return;
        }

        $this->authorize('delete', Faq::class);
        $this->deleteFaq->handle($this->query->find($this->deletingFaqId), auth('web')->user());

        $this->emit('notify', 'success', 'FAQ deleted successfully.');
        $this->emit('faq-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'faqDeleteModal']);
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.hoa-center.faqs.modals.faq-delete-modal');
    }
}
