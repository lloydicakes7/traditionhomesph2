<div wire:ignore.self>
    <x-adminlte-modal id="userFormModal"
        title="{{ $isEditing ? 'Edit Admin' : 'Create New Admin' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-user-edit' : 'fa-user-plus' }}"
        theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the account form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form onsubmit="return false;">
                <div class="row">
                    {{-- ========================================================= --}}
                    {{-- WHO THEY ARE                                             --}}
                    {{-- ========================================================= --}}
                    <div class="col-12">
                        <x-adminlte-input name="form.name" label="Full Name" placeholder="Enter full name"
                            wire:model.defer="form.name" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="form.email" label="Email Address" type="email"
                            placeholder="Enter email address" wire:model.defer="form.email" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-select2 name="form.role_id" label="Role" wire:model.defer="form.role_id"
                            igroup-size="sm" :config="['placeholder' => 'Select a role…', 'width' => '100%']">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-user-tag"></i></div>
                            </x-slot>
                            <option value="">Select Role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </x-adminlte-select2>

                        <div class="form-group mb-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="formIsActive"
                                    wire:model.defer="form.is_active">
                                <label class="custom-control-label" for="formIsActive">
                                    {{ $form['is_active'] ? 'Active' : 'Disabled' }}
                                    <br><small>{{ $form['is_active'] ? 'May Sign In' : 'Stop Sign In' }}</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Admin' : 'Create Admin' }}"
                theme="primary" icon="fas fa-fw fa-save"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
