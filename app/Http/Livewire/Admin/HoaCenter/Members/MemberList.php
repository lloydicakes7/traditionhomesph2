<?php

namespace App\Http\Livewire\Admin\HoaCenter\Members;

use App\Http\Livewire\AdminComponent;
use App\Models\HoaMember;
use App\Queries\HoaCenter\Members\MemberListQuery;

class MemberList extends AdminComponent
{
    protected $queryString = ['search'];

    public $search = '';

    public $expanded = [];

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
    // Group Expansion
    // =====================================================================

    public function showMore($key): void
    {
        $this->expanded[(string) $key] = true;
    }

    public function showLess($key): void
    {
        unset($this->expanded[(string) $key]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', HoaMember::class);

        $groups = $this->query->sections($this->search);

        foreach ($groups as $group) {
            $group->visible_members = isset($this->expanded[$group->section_key])
                ? $group->members
                : $group->members->take(MemberListQuery::VISIBLE_PER_GROUP);
            $group->hidden_members_count = $group->members->count() - $group->visible_members->count();
        }

        return view('livewire.admin.hoa-center.members.member-list', [
            'groups' => $groups,
        ]);
    }
}
