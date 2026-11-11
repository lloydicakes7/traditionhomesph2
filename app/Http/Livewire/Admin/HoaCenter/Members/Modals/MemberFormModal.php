<?php

namespace App\Http\Livewire\Admin\HoaCenter\Members\Modals;

use App\Actions\HoaCenter\Members\AddMember;
use App\Actions\HoaCenter\Members\UpdateMember;
use App\Http\Livewire\AdminModalComponent;
use App\Models\HoaMember;
use App\Queries\HoaCenter\Members\MemberListQuery;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class MemberFormModal extends AdminModalComponent
{
    use WithFileUploads;

    public $isEditing = false;

    public $editingMemberId = null;

    public $form = [
        'name'            => '',
        'position'        => '',
        'phone'           => '',
        'email'           => '',
        'note'            => '',
    ];

    public $memberPhoto;

    public $existingMemberPhotoUrl = null;

    public $removeMemberPhoto = false;

    protected $listeners = [
        'openMemberFormModal'  => 'openCreate',
        'loadMemberFormModal'  => 'openEdit',
        'closeMemberFormModal' => 'closeModal',
    ];

    /** @var MemberListQuery */
    protected $query;

    /** @var AddMember */
    protected $addMember;

    /** @var UpdateMember */
    protected $updateMember;

    public function boot(MemberListQuery $query, AddMember $addMember, UpdateMember $updateMember)
    {
        $this->query = $query;
        $this->addMember = $addMember;
        $this->updateMember = $updateMember;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'form.name'           => ['required', 'string', 'max:150'],
            'form.position'       => ['required', 'string', 'max:100'],
            'form.phone'          => ['nullable', 'string', 'max:50'],
            'form.email'          => ['nullable', 'email', 'max:150'],
            'form.note'           => ['nullable', 'string', 'max:1000'],
            'memberPhoto'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', HoaMember::class);

        $this->isEditing = false;
        $this->editingMemberId = null;
        $this->form = [
            'name'           => '',
            'position'       => '',
            'phone'          => '',
            'email'          => '',
            'note'           => '',
        ];
        $this->resetPhotoState();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', HoaMember::class);

        $member = $this->query->find($id);

        $this->isEditing = true;
        $this->editingMemberId = $member->id;
        $this->form = [
            'name'           => $member->name,
            'position'       => $member->position,
            'phone'          => $member->phone ?? '',
            'email'          => $member->email ?? '',
            'note'           => $member->note ?? '',
        ];
        $this->resetPhotoState();
        $this->existingMemberPhotoUrl = $member->photo_url;
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingMemberId = null;
        $this->resetPhotoState();
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', HoaMember::class);
        $this->validate();

        if ($this->isEditing) {
            $this->updateMember->handle(
                $this->query->find($this->editingMemberId),
                $this->form,
                $this->memberPhoto,
                $this->removeMemberPhoto,
                auth('web')->user()
            );
        } else {
            $this->addMember->handle($this->form, $this->memberPhoto, auth('web')->user());
        }

        $this->emit('notify', 'success',
            $this->isEditing
                ? 'HOA member updated successfully.'
                : 'HOA member added successfully.');

        $this->emit('member-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'memberFormModal']);
    }

    // =====================================================================
    // Image handling
    // =====================================================================

    public function removePhoto(): void
    {
        $this->authorize('update', HoaMember::class);

        if ($this->memberPhoto) {
            $this->resetPhotoState();

            return;
        }

        $this->removeMemberPhoto = true;
        $this->existingMemberPhotoUrl = null;

        $this->emit('notify', 'info', 'The member photo will be removed on save.');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetPhotoState(): void
    {
        $this->memberPhoto = null;
        $this->existingMemberPhotoUrl = null;
        $this->removeMemberPhoto = false;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        $this->authorize('viewAny', HoaMember::class);

        return view('livewire.admin.hoa-center.members.modals.member-form-modal');
    }
}
