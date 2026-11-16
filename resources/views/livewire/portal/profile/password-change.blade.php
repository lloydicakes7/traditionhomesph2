<div class="row">
    <div class="col-12 col-lg-8 col-xl-6">

        <x-adminlte-card theme="primary" icon="fas fa-fw fa-key" title="Choose a New Password">
            <p class="text-muted small mb-3">
                Your account was issued a temporary password. Choose your own before using the portal.
            </p>

            <form wire:submit.prevent="save">
                <x-adminlte-input-password name="current_password" label="Temporary Password"
                    wire:model.defer="current_password" igroup-size="sm" autocomplete="current-password">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
                    </x-slot>
                </x-adminlte-input-password>

                <x-adminlte-input-password name="new_password" label="New Password"
                    wire:model.defer="new_password" igroup-size="sm" autocomplete="new-password">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-key"></i></div>
                    </x-slot>
                </x-adminlte-input-password>

                <x-adminlte-input-password name="new_password_confirmation" label="Confirm New Password"
                    wire:model.defer="new_password_confirmation" igroup-size="sm" autocomplete="new-password">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-key"></i></div>
                    </x-slot>
                </x-adminlte-input-password>

                <x-adminlte-button type="submit" theme="primary" icon="fas fa-fw fa-save"
                    label="Save Password" class="btn-sm" wire:loading.attr="disabled" wire:target="save" />

                <span wire:loading wire:target="save" class="text-info small ml-2">
                    <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>Saving…
                </span>
            </form>
        </x-adminlte-card>

    </div>
</div>
