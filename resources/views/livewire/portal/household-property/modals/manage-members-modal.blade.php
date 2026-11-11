<div wire:ignore.self>
    <x-adminlte-modal id="manageMembersModal" title="Manage Household Members"
        icon="fas fa-fw fa-users" theme="primary" size="lg" v-centered scrollable wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading household members…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="row">
                {{-- ===== Left: the homeowner's own person form ===== --}}
                <div class="col-12 col-lg-7 mb-3 mb-lg-0">
                    <h6 class="font-weight-bold mb-2">
                        <i class="fas fa-fw {{ $mode === 'edit' ? 'fa-user-edit' : 'fa-user-plus' }} mr-1"></i>
                        {{ $mode === 'edit' ? 'Edit Member' : 'Add New Member' }}
                    </h6>

                    <div class="rounded border p-3" style="background:#fbf7ec;">
                        @include('livewire.portal.household-property.modals.partials.member-resident-fields')

                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Relationship to Head</label>
                            <x-adminlte-input name="relationship" placeholder="e.g., Son, Mother, Uncle"
                                wire:model.defer="relationship" igroup-size="sm">
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-link"></i></div></x-slot>
                            </x-adminlte-input>
                            @error('relationship')<span class="text-danger small">{{ $message }}</span>@enderror
                        </div>

                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="portalMemberIsHead"
                                wire:model.defer="isHead">
                            <label class="custom-control-label small" for="portalMemberIsHead">
                                Head of the family
                                <br><small class="text-muted">The head may be the father, the mother, or a child old enough to stand for the household.</small>
                            </label>
                        </div>

                        @if ($mode === 'edit')
                            <div class="d-flex">
                                <x-adminlte-button label="Cancel" theme="default" wire:click="cancelEdit"
                                    wire:loading.attr="disabled" class="btn-sm mr-2" />
                                <x-adminlte-button label="Update Member" theme="primary" icon="fas fa-fw fa-save"
                                    wire:click="updateMember" wire:loading.attr="disabled" class="btn-sm" />
                            </div>
                        @else
                            <x-adminlte-button label="Add Member" theme="success" icon="fas fa-fw fa-plus"
                                wire:click="addMember" wire:loading.attr="disabled" class="btn-sm btn-block" />
                        @endif
                    </div>
                </div>

                {{-- ===== Right: the current list ===== --}}
                <div class="col-12 col-lg-5">
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
                                        <th style="width:96px;" class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($members as $index => $member)
                                        <tr wire:key="member-{{ $member['resident_id'] }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="font-weight-medium small">{{ $member['resident_name'] ?? '—' }}</div>
                                                <small class="text-muted">{{ $member['relationship_label'] ?? '—' }}</small>
                                                @if (! empty($member['is_head']))
                                                    <span class="badge badge-success ml-1">Head</span>
                                                @endif
                                                @if (! empty($member['has_account']))
                                                    <span class="badge badge-info ml-1" title="This resident has a portal account">Account</span>
                                                @endif
                                            </td>
                                            <td class="text-right text-nowrap">
                                                <div class="btn-group btn-group-xs" role="group" aria-label="Actions">
                                                    <x-adminlte-button label="" theme="dark" icon="fas fa-fw fa-crown"
                                                        wire:click="markHead({{ $member['resident_id'] }})"
                                                        wire:loading.attr="disabled" class="btn-xs" title="Make head of the family" />
                                                    <x-adminlte-button label="" theme="info" icon="fas fa-fw fa-pen"
                                                        wire:click="editMember({{ $member['resident_id'] }})"
                                                        wire:loading.attr="disabled" class="btn-xs" title="Edit member" />
                                                    <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                                        wire:click="confirmRemove({{ $member['resident_id'] }})"
                                                        wire:loading.attr="disabled" class="btn-xs" title="Remove member" />
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
                            <p class="mb-0 small">No other members are recorded for your household yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Done" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>

    {{-- =================================================================== --}}
    {{-- CONFIRM REMOVAL                                                     --}}
    {{-- Opened by the server, not by data-toggle: `confirmRemove()` checks   --}}
    {{-- that the member belongs to this household before there is anything   --}}
    {{-- worth confirming, then asks Bootstrap for the shell (modal-open,     --}}
    {{-- handled by public/js/modal-loader.js).                              --}}
    {{-- =================================================================== --}}
    <x-adminlte-modal id="removeHouseholdMemberModal"
        title="Remove Household Member"
        icon="fas fa-fw fa-exclamation-triangle" theme="danger" size="md" scrollable v-centered wire:ignore.self>

        @if ($removingMember)
            <p class="mb-2">Are you sure you want to remove this member from your household?</p>
            <div class="font-weight-bold">{{ $removingMember['resident_name'] }}</div>
            <div class="text-muted small">
                {{ $removingMember['relationship_label'] ?: 'Relationship not recorded' }}
            </div>
            <p class="text-muted small mb-0 mt-2">
                Their record stays with the association — they are only taken off your household.
            </p>
        @endif

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Remove" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="removeMember" wire:loading.attr="disabled" class="btn-sm" />
            <span wire:loading wire:target="removeMember" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Removing...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
