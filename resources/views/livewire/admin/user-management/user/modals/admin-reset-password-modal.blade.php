<div wire:ignore.self>
    <x-adminlte-modal id="adminResetPasswordModal"
        title="{{ __('users.reset.title') }}"
        icon="fas fa-fw fa-key" theme="warning" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="{{ __('users.reset.loading') }}" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="mb-2">
                {{ __('users.reset.confirm', ['email' => $resettingAdminEmail ?? '']) }}
            </p>

            <p class="small font-weight-bold mb-1">{{ __('users.reset.steps') }}</p>
            <ol class="small pl-3 mb-2">
                <li>
                    <strong>{{ __('users.reset.step_1') }}</strong>
                </li>
                <li>
                    <strong>{{ __('users.reset.step_2', ['email' => $resettingAdminEmail ?? 'the account']) }}</strong>
                </li>
                <li>
                    <strong>{{ __('users.reset.step_3') }}</strong>
                </li>
            </ol>

            <p class="small mb-0">
                {{ __('users.reset.note') }}
            </p>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="{{ __('users.dialog.cancel') }}" theme="outline-secondary"
                data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ __('users.reset.action') }}" theme="warning" icon="fas fa-fw fa-key"
                wire:click="resetPassword" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="resetPassword" class="ml-2 text-warning small">
                <i class="fas fa-spinner fa-spin"></i> {{ __('users.reset.working') }}
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
