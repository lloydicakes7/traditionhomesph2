<div wire:ignore.self>
    <x-adminlte-modal id="requestFileModal" title="File a Request"
        icon="fas fa-fw fa-clipboard" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Preparing the request form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        <form>
            <x-customs.request-type-fields :form="$form" :selected-type="$selectedType"
                :purpose-options="$purposeOptions" prefix="requestFileType"
                :membership-profile="$membershipProfile" :respondent-unit="$respondentUnit" />
        </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Submit Request" theme="primary" icon="fas fa-fw fa-paper-plane"
                wire:click="save" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="save" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Submitting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
