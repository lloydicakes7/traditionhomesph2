<div wire:ignore.self>
    <x-adminlte-modal id="violationFormModal"
        title="{{ $isEditing ? 'Edit Violation' : 'Issue Violation' }}"
        icon="fas fa-fw {{ $isEditing ? 'fa-edit' : 'fa-exclamation-triangle' }}"
        theme="{{ $isEditing ? 'primary' : 'danger' }}" size="md" scrollable v-centered wire:ignore.self>
        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the violation form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                {{-- =========================================================== --}}
                {{-- WHO AND WHAT                                                --}}
                {{-- =========================================================== --}}
                <div class="row">
                    <div class="col-12 col-xl-7">
                        <x-adminlte-select2 id="violationResident" name="form.resident_id" label="Resident"
                            wire:model="form.resident_id" igroup-size="sm"
                            :config="['placeholder' => 'Search for the resident...']">
                            <option value="">Select the Resident...</option>
                            @foreach ($members as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select2>
                    </div>

                    <div class="col-12 col-xl-5">
                        <x-adminlte-select name="form.violation_type" label="Violation Type"
                            wire:model.defer="form.violation_type" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                            </x-slot>
                            <option value="">Select type...</option>
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                </div>

                {{-- =========================================================== --}}
                {{-- WHEN AND WHERE                                              --}}
                {{-- =========================================================== --}}
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <x-adminlte-input name="form.occurred_on" label="Date" type="date"
                            wire:model.defer="form.occurred_on" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="far fa-fw fa-calendar-alt"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-sm-6">
                        <x-adminlte-input name="form.occurred_time" label="Time" type="time"
                            wire:model.defer="form.occurred_time" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="far fa-fw fa-clock"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                {{-- =========================================================== --}}
                {{-- WHAT WAS SEEN                                               --}}
                {{-- =========================================================== --}}
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-input name="form.location" label="Location" placeholder="e.g., Block 4 open lot"
                            wire:model.defer="form.location" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-map-marker-alt"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12">
                        <x-adminlte-textarea name="form.description" label="Description" rows="4"
                            placeholder="What the officer observed — what happened, who was there, anything the homeowner should know."
                            wire:model.defer="form.description" igroup-size="sm" />
                    </div>
                </div>

                {{-- =========================================================== --}}
                {{-- THE OFFICE'S OWN REPORT — OPTIONAL                          --}}
                {{-- =========================================================== --}}
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-textarea name="form.office_report" label="Report to the HOA Office (optional)" rows="3"
                            placeholder="File a report for the association's own records when the incident calls for one. Leave this blank for an ordinary notice."
                            wire:model.defer="form.office_report" igroup-size="sm" />
                    </div>
                </div>
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $isEditing ? 'Update Violation' : 'Issue Violation' }}"
                theme="{{ $isEditing ? 'primary' : 'danger' }}"
                icon="fas fa-fw {{ $isEditing ? 'fa-save' : 'fa-paper-plane' }}"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
