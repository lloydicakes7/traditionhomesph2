<?php

namespace App\Http\Livewire\Admin\HoaCenter\Members\Modals;

use App\Actions\HoaCenter\Members\DeleteMember;
use App\Http\Livewire\AdminModalComponent;
use App\Models\HoaMember;
use App\Queries\HoaCenter\Members\MemberListQuery;

class MemberDeleteModal extends AdminModalComponent
{
    public $deletingMemberId = null;

    public $deletingMemberData = null;

    protected $listeners = [
        'loadMemberDeleteModal'  => 'load',
        'closeMemberDeleteModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var MemberListQuery */
    protected $query;

    /** @var DeleteMember */
    protected $deleteMember;

    public function boot(MemberListQuery $query, DeleteMember $deleteMember)
    {
        $this->query = $query;
        $this->deleteMember = $deleteMember;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', HoaMember::class);

        $member = $this->query->find($id);

        $this->deletingMemberId = $member->id;
        $this->deletingMemberData = [
            'name'          => $member->name,
            'position'      => $member->position,
            'phone'         => $member->phone,
            'has_photo'     => $member->profile_photo_path !== null,
        ];

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingMemberId = null;
        $this->deletingMemberData = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingMemberId) {
            return;
        }

        $this->authorize('delete', HoaMember::class);
        $this->deleteMember->handle($this->query->find($this->deletingMemberId), auth('web')->user());

        $this->emit('notify', 'success', 'HOA member deleted successfully.');
        $this->emit('member-saved');
        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => 'memberDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.hoa-center.members.modals.member-delete-modal');
    }
}
