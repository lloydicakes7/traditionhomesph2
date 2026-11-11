<div>
    @if ($sent)
        <x-adminlte-alert theme="success" icon="fas fa-fw fa-check" dismissable>
            {{ __('public.contact.sent') }}
        </x-adminlte-alert>
    @endif

    @if ($failed)
        <x-adminlte-alert theme="danger" icon="fas fa-fw fa-exclamation-triangle">
            {{ __('public.contact.unavailable') }}
        </x-adminlte-alert>
    @endif

    <form wire:submit.prevent="send">
        <div class="row">
            <div class="col-12 col-md-6">
                <x-adminlte-input name="form.name" label="{{ __('public.contact.name') }}" maxlength="120"
                    placeholder="e.g., Juan dela Cruz" wire:model.defer="form.name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <x-adminlte-input name="form.email" type="email" label="{{ __('public.contact.sender_email') }}"
                    maxlength="150" placeholder="e.g., juan@example.com"
                    wire:model.defer="form.email" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <x-adminlte-input name="form.contact_number" type="tel"
                    label="{{ __('public.contact.contact_number') }}" maxlength="30"
                    placeholder="e.g., 0917 123 4567" wire:model.defer="form.contact_number" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-phone"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <x-adminlte-input name="form.subject" label="{{ __('public.contact.subject') }}" maxlength="150"
                    placeholder="e.g., Follow-up on my request" wire:model.defer="form.subject" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12">
                <x-adminlte-textarea name="form.message" label="{{ __('public.contact.message') }}"
                    rows="6" maxlength="3000" placeholder="Write your message here."
                    wire:model.defer="form.message" igroup-size="sm" />
            </div>
        </div>

        <div class="text-right">
            <x-adminlte-button label="{{ __('public.contact.send') }}" type="submit" theme="primary"
                icon="fas fa-fw fa-paper-plane" wire:loading.attr="disabled" />
            <span wire:loading wire:target="send" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Sending...
            </span>
        </div>
    </form>
</div>
