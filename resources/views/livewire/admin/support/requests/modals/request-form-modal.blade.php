<div wire:ignore.self>
    <x-adminlte-modal id="requestFormModal" title="New Request"
        icon="fas fa-fw fa-plus" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Preparing the request form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <form>
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-select2 id="requestFormResident" name="form.resident_id" label="Homeowner"
                            wire:model="form.resident_id" igroup-size="sm"
                            :config="['placeholder' => 'Select a homeowner...', 'width' => '100%']">
                            <option value=""></option>
                            @foreach ($residents as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </x-adminlte-select2>
                        @error('form.resident_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <x-customs.request-type-fields :form="$form" :selected-type="$selectedType"
                    :purpose-options="$purposeOptions" prefix="requestFormType"
                    :membership-profile="$membershipProfile" :respondent-unit="$respondentUnit"
                    :supply-missing="true" />
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="File Request" theme="primary" icon="fas fa-fw fa-paper-plane"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-primary small">
                <i class="fas fa-spinner fa-spin"></i> Filing...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
