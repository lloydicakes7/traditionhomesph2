<?php

namespace App\Http\Livewire\Admin\HoaCenter\Faqs;

use App\Http\Livewire\AdminComponent;
use App\Models\Faq;
use App\Queries\HoaCenter\Faqs\FaqListQuery;
use Livewire\WithPagination;

class FaqList extends AdminComponent
{
    use WithPagination;

    protected $queryString = ['search'];

    public $search = '';

    protected $listeners = [
        'faq-saved' => '$refresh',
    ];

    // ===== Setup =====

    /** @var FaqListQuery */
    protected $query;

    public function boot(FaqListQuery $query)
    {
        $this->query = $query;
    }

    // ===== Lifecycle =====

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ===== Render =====

    public function render()
    {
        $this->authorize('viewAny', Faq::class);

        return view('livewire.admin.hoa-center.faqs.faq-list', [
            'faqs' => $this->query->paginate($this->search),
        ]);
    }
}
