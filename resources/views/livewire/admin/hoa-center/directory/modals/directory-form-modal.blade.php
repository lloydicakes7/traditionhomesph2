<div wire:ignore.self>
    <x-adminlte-modal id="directoryFormModal"
        title="{{ $isEditing ? 'Edit Directory Entry' : 'New Directory Entry' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-plus-circle' }}"
        theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading directory entry…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.name" label="Name"
                            placeholder="e.g., IT Support Desk, Juan Dela Cruz"
                            wire:model.defer="form.name" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.role" label="Role" placeholder="e.g., IT Coordinator (optional)"
                            wire:model.defer="form.role" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-id-badge"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-select2 id="directoryGroupId" name="form.directory_group_id" label="Group"
                            wire:model="form.directory_group_id" igroup-size="sm"
                            :config="['placeholder' => '— Select Group —']">
                            <option value="">— Select Group —</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->entries_count }})</option>
                            @endforeach
                            <option value="{{ \App\Http\Livewire\Admin\HoaCenter\Directory\Modals\DirectoryFormModal::NEW_GROUP }}">
                                + Add new group…
                            </option>
                        </x-adminlte-select2>
                    </div>
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="form.email" label="Email" type="email"
                            placeholder="e.g., itsupport@traditionhomes.ph (optional)"
                            wire:model.defer="form.email" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                @if ($form['directory_group_id'] === \App\Http\Livewire\Admin\HoaCenter\Directory\Modals\DirectoryFormModal::NEW_GROUP)
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-input name="form.new_group_name" label="New group name"
                                placeholder="e.g., IT Support"
                                wire:model.defer="form.new_group_name" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-plus"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>
                @endif

                {{-- ============================================================ --}}
                {{-- PHONE NUMBERS                                                --}}
                {{-- ============================================================ --}}
                <hr class="mt-1">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="font-weight-bold mb-0">
                        <i class="fas fa-fw fa-phone-alt mr-1"></i>Phone Numbers
                    </h6>
                    <x-adminlte-button label="Add Number" theme="success" icon="fas fa-fw fa-plus"
                        wire:click="addPhone" wire:loading.attr="disabled" class="btn-xs" />
                </div>

                @foreach ($phones as $index => $phone)
                    <div class="card bg-light mb-2" wire:key="directory-phone-row-{{ $index }}">
                        <div class="card-body py-2">
                            <div class="row">
                                <div class="col-12 col-md-4">
                                    <x-adminlte-input name="phones.{{ $index }}.label" label="Label (optional)"
                                        placeholder="e.g., Mobile, Hotline, Trunkline"
                                        wire:model.defer="phones.{{ $index }}.label" igroup-size="sm" />
                                </div>

                                <div class="col-12 col-md-6">
                                    <x-adminlte-input name="phones.{{ $index }}.number" label="Number"
                                        placeholder="e.g., 0917 123 4567"
                                        wire:model.defer="phones.{{ $index }}.number" igroup-size="sm" />
                                </div>

                                <div class="col-12 col-md-2 d-flex align-items-end justify-content-center mb-3">
                                    <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                        wire:click="removePhone({{ $index }})" wire:loading.attr="disabled"
                                        class="btn-sm" title="Remove number" />
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <small class="text-muted d-block mb-3">
                    The label is optional.
                </small>

                <div class="row">
                    <div class="col-12">
                        <x-adminlte-textarea name="form.note" label="Note" rows="2"
                            placeholder="Anything worth knowing (optional)"
                            wire:model.defer="form.note" igroup-size="sm" />
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Entry' : 'Create Entry' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
