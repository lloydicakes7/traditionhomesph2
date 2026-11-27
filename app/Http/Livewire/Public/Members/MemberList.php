<?php

namespace App\Http\Livewire\Public\Members;

use App\Http\Livewire\PublicComponent;
use App\Queries\HoaCenter\Members\MemberListQuery;
use Livewire\WithPagination;

class MemberList extends PublicComponent
{
    use WithPagination;

    protected $queryString = ['search' => ['except' => '']];

    public $search = '';

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

    public function updatingSearch(): void
    {
        $this->resetGroupPages();
    }

    public function clearSearch(): void
    {
        $this->search = '';
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
        return view('livewire.public.members.member-list', [
            'groups' => $this->query->sections((string) $this->search),
        ]);
    }
}
