<div wire:ignore.self>
    <x-adminlte-modal id="requestEditModal" title="Edit Request"
        icon="fas fa-fw fa-edit" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading the request…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($request)
                <form>
                    {{-- WHO FILED IT — shown for reference; ownership is not edited --}}
                    <div class="mb-3">
                        <div class="text-muted small">Homeowner</div>
                        <div class="font-weight-medium text-break">{{ $request->resident_label }}</div>
                    </div>

                    {{-- REQUEST TYPE AND ITS FIELDS — one fragment, shared with
                         the office's own filing form and the portal's --}}
                    <x-customs.request-type-fields :form="$form" :selected-type="$selectedType"
                        :purpose-options="$purposeOptions" prefix="requestEditType"
                        :membership-profile="$membershipProfile" :respondent-unit="$respondentUnit"
                        :supply-missing="true" />
                </form>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Save" theme="primary" icon="fas fa-fw fa-check"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-primary small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
