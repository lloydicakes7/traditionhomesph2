<div wire:ignore.self>
    <x-adminlte-modal id="homeownerResetPasswordModal"
        title="Reset Password"
        icon="fas fa-fw fa-key" theme="warning" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading homeowner details…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="mb-2">
                Reset the password for <strong>{{ $resettingHomeownerEmail ?? '' }}</strong>?
            </p>

            <p class="small font-weight-bold mb-1">When you confirm:</p>
            <ol class="small pl-3 mb-2">
                <li>
                    <strong>The system will reset the password.</strong>
                </li>
                <li>
                    <strong>An email is sent to {{ $resettingHomeownerEmail ?? 'the homeowner' }}.</strong>
                </li>
                <li>
                    <strong>The homeowner sets the new password.</strong>
                </li>
            </ol>

            <p class="small mb-0">
                Make sure the email address above is correct and that the homeowner is expecting this email.
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Reset Password" theme="warning" icon="fas fa-fw fa-key"
                wire:click="resetPassword" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="resetPassword" class="ml-2 text-warning small">
                <i class="fas fa-spinner fa-spin"></i> Resetting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
