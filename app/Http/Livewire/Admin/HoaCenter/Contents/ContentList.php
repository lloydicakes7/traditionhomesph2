<?php

namespace App\Http\Livewire\Admin\HoaCenter\Contents;

use App\Http\Livewire\AdminComponent;
use App\Models\Content;
use App\Queries\HoaCenter\Contents\ContentListQuery;
use Livewire\WithPagination;

class ContentList extends AdminComponent
{
    use WithPagination;

    protected $queryString = ['search'];

    public $search = '';

    protected $listeners = [
        'content-saved' => '$refresh',
    ];

    // ===== Setup =====

    /** @var ContentListQuery */
    protected $query;

    public function boot(ContentListQuery $query)
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
        $this->authorize('viewAny', Content::class);

        return view('livewire.admin.hoa-center.contents.content-list', [
            'contents' => $this->query->paginate($this->search),
        ]);
    }
}
