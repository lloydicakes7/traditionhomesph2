<?php

namespace App\Http\Livewire\Public\Members;

use App\Queries\HoaCenter\Members\MemberListQuery;
use App\Http\Livewire\PublicComponent;
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
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.public.members.member-list', [
            'members' => $this->query->paginate((string) $this->search),
        ]);
    }
}
