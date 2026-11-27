<?php

namespace App\Http\Livewire\Admin\HoaCenter\Members;

use App\Http\Livewire\AdminComponent;
use App\Models\HoaMember;
use App\Queries\HoaCenter\Members\MemberListQuery;
use Livewire\WithPagination;

class MemberList extends AdminComponent
{
    use WithPagination;

    protected $queryString = ['search'];

    public $search = '';

    protected $listeners = [
        'member-saved' => '$refresh',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var MemberListQuery */
    protected $query;

    public function boot(MemberListQuery $query)
    {
        $this->query = $query;
    }

    // =====================================================================
    // Search
    // =====================================================================

    public function updatedSearch(): void
    {
        $this->resetGroupPages();
    }

    public function resetGroupPages(): void
    {
        foreach ($this->query->pageNames() as $pageName) {
            $this->setPage(1, $pageName);
        }
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', HoaMember::class);

        return view('livewire.admin.hoa-center.members.member-list', [
            'groups' => $this->query->sections($this->search),
        ]);
    }
}
