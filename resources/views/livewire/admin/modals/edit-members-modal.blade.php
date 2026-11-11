<div wire:ignore.self>
    <x-adminlte-modal id="editMembersModal" wire:key="edit-members-modal"
        title="Edit Household Members"
        icon="fas fa-fw fa-users" theme="primary" size="xl" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading members…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="row">
                {{-- ===== Left: add a member, or edit one in place ===== --}}
                <div class="col-12 col-xl-7 mb-3 mb-xl-0">
                    @include('livewire.admin.modals.partials.household-member-fields')
                </div>

                {{-- ===== Right: the current list ===== --}}
                <div class="col-12 col-xl-5">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-fw fa-users mr-1"></i>Current Members ({{ count($members) }})
                    </h6>

                    @if (count($members) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:2rem;">#</th>
                                        <th>Resident</th>
                                        <th style="width:90px;" class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $index => $member)
                                        <tr wire:key="edit-members-row-{{ $member['resident_id'] }}"
                                            class="{{ $editingMemberIndex === $index ? 'table-active' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="font-weight-medium small">
                                                    {{ $member['resident_name'] ?? '—' }}
                                                    @if (! empty($member['is_head']))
                                                        <span class="badge badge-success ml-1">Head</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $member['relationship_label'] ?? '—' }}</small>
                                            </td>
                                            <td class="text-right text-nowrap">
                                                <div class="btn-group btn-group-xs" role="group">
                                                    <x-adminlte-button label="" theme="dark" icon="fas fa-fw fa-crown"
                                                        wire:click="markHead({{ $index }})" class="btn-xs"
                                                        title="Mark as household head" aria-label="Mark as household head" />
                                                    <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-pen"
                                                        wire:click="startEditMember({{ $index }})" class="btn-xs"
                                                        title="Edit member" aria-label="Edit member" />
                                                    <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                                        wire:click="removeMember({{ $index }})" class="btn-xs"
                                                        title="Remove member" aria-label="Remove member" />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4 border rounded">
                            <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                            <p class="mb-0 small">No household members yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Save Members" theme="success" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled" class="btn-sm" />
            <span wire:loading wire:target="save" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
