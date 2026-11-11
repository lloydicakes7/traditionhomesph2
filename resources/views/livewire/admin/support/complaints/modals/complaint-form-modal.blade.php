<div wire:ignore.self>
    <x-adminlte-modal id="complaintFormModal"
        title="{{ $isEditing ? 'Edit Complaint' : 'File a Complaint' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-question-circle' }}"
        theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the complaint form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                {{-- =========================================================== --}}
                {{-- WHO AND WHAT                                                --}}
                {{-- =========================================================== --}}
                <div class="row">
                    <div class="col-12 col-md-7">
                        <x-adminlte-select2 id="complaintResident" name="form.resident_id" label="Homeowner"
                            wire:model="form.resident_id" igroup-size="sm"
                            :config="['placeholder' => 'Search for the homeowner...']">
                            <option value="">Select the homeowner...</option>
                            @foreach ($members as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select2>
                    </div>

                    <div class="col-12 col-md-5">
                        <x-adminlte-select name="form.category" label="Category"
                            wire:model.defer="form.category" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                            </x-slot>
                            <option value="">Select a category...</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                </div>

                {{-- =========================================================== --}}
                {{-- WHAT IT IS ABOUT                                            --}}
                {{-- =========================================================== --}}
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-input name="form.subject" label="Subject"
                            placeholder="One line saying what this is about"
                            wire:model.defer="form.subject" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-heading"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <x-adminlte-textarea name="form.description" label="Description" rows="5"
                            placeholder="What was reported — what happened, when and where, and anything else the office needs."
                            wire:model.defer="form.description" igroup-size="sm" />
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Complaint' : 'File Complaint' }}"
                theme="primary" icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled"
                class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
