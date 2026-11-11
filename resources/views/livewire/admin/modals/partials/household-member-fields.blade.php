@php($hideHead = $hideHead ?? false)
@php($isEditing = $editingMemberIndex !== null)
@php($isNewResident = ! $isEditing && $memberType === 'new_resident')
@php($isExisting = ! $isEditing && $memberType === 'existing')

{{-- ===== Header ===== --}}

@if ($isEditing)
    <h6 class="font-weight-bold mb-3">
        <i class="fas fa-fw fa-user-edit mr-1"></i>Edit Member: {{ $editingResidentName }}
    </h6>
@else
    <h6 class="font-weight-bold mb-2">
        <i class="fas fa-fw fa-user-plus mr-1"></i>Add New Member
    </h6>

    <div class="btn-group btn-group-sm w-100 mb-3" role="group">
        <button type="button"
            class="btn {{ $isExisting ? 'btn-primary' : 'btn-outline-primary' }}"
            wire:click="openAddMemberForm">
            <i class="fas fa-fw fa-user mr-1"></i>Add Existing Resident
        </button>
        <button type="button"
            class="btn {{ $isNewResident ? 'btn-primary' : 'btn-outline-primary' }}"
            wire:click="$set('memberType', 'new_resident')">
            <i class="fas fa-fw fa-user-plus mr-1"></i>New Resident
        </button>
    </div>
@endif

<div class="rounded border bg-light p-3">

    {{-- ===== Resident ===== --}}

    @if ($isExisting)
        <div class="form-group mb-2">
            <label class="small font-weight-bold mb-1">Resident</label>
            <x-adminlte-select2 id="editMembersResidentId" name="memberForm.resident_id"
                wire:model.defer="memberForm.resident_id" igroup-size="sm"
                :config="['placeholder' => '— Select Resident —']">
                <option value="">— Select Resident —</option>
                @foreach ($selectableResidents as $res)
                    <option value="{{ $res->id }}">{{ $res->full_name }}</option>
                @endforeach
            </x-adminlte-select2>
        </div>
    @else
        <div wire:key="edit-members-profile-{{ $isEditing ? 'edit-'.$editingMemberIndex : 'new' }}">
            @include('livewire.admin.homeowners.partials.profile-photo-field', [
                'prop' => 'memberPhoto',
                'domId' => 'editMemberPhoto',
                'photo' => $memberPhoto,
                'existingUrl' => null,
                'removeAction' => null,
            ])

            @include('livewire.admin.homeowners.partials.personal-info-fields', ['prefix' => 'memberForm'])

            @include('livewire.admin.homeowners.partials.rbi-profile-fields', [
                'prefix' => 'memberForm',
                'sectorOptions' => $sectorOptions,
            ])
        </div>
    @endif

    {{-- ===== Relationship ===== --}}
    <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mt-4 mb-3">
        <i class="fas fa-fw fa-user mr-1"></i>Relationships
    </h6>

    <div class="form-group mb-2">
        <label class="small font-weight-bold mb-1">Relationship</label>
        <x-adminlte-input name="memberForm.relationship" placeholder="e.g., Son, Mother, Uncle"
            wire:model.defer="memberForm.relationship" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-link"></i></div></x-slot>
        </x-adminlte-input>
    </div>

    @if (! $hideHead)
        <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" class="custom-control-input" id="editMemberIsHead"
                wire:model.defer="memberForm.is_head">
            <label class="custom-control-label small" for="editMemberIsHead">
                Household Head
                <br><small>Make this person the head of the family</small>
            </label>
        </div>
    @endif

    {{-- ===== Actions ===== --}}

    @if ($isEditing)
        <div class="d-flex justify-content-end">
            <x-adminlte-button label="Save Changes" theme="success" icon="fas fa-fw fa-save"
                wire:click="saveMemberEdit" wire:loading.attr="disabled" class="btn-sm mr-2" />
            <x-adminlte-button label="Cancel" theme="outline-secondary"
                wire:click="openAddMemberForm" class="btn-sm" />
        </div>
    @else
        <div class="d-flex justify-content-end">
            <x-adminlte-button label="Add Member" theme="success" icon="fas fa-fw fa-plus"
                wire:click="addMember" wire:loading.attr="disabled" class="btn-sm" />
        </div>
    @endif
</div>
