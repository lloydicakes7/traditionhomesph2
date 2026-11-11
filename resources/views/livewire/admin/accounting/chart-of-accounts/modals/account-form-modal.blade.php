<div wire:ignore.self>
    <x-adminlte-modal id="accountFormModal"
        title="{{ $isEditing ? 'Edit Account' : 'Add Account' }}"
        icon="fas fa-fw fa-balance-scale" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading account form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($readOnly)
                <div class="alert alert-info py-2 px-3 small">
                    <i class="fas fa-fw fa-lock mr-1"></i>
                    @if ($systemAccount)
                        This is a system account. The books keep it, so it cannot be changed here.
                    @else
                        This account is managed from <strong>{{ $managedBy }}</strong>.
                        Rename the category there; it is read-only here.
                    @endif
                </div>
            @endif

            @error('account')
                <div class="alert alert-danger py-2 px-3 small">{{ $message }}</div>
            @enderror

            <div class="row">
                <div class="col-12">
                    <x-adminlte-input name="form.name" label="Account Name"
                        placeholder="e.g., BPI Checking — Operating"
                        wire:model.defer="form.name" igroup-size="sm"
                        :disabled="$readOnly" />
                </div>

                <div class="col-12 col-sm-6">
                    <x-adminlte-select name="form.kind" label="Kind"
                        wire:model="form.kind" igroup-size="sm"
                        :disabled="$readOnly || $kindLocked">
                        @foreach ($kinds as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>

                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="form.code" label="Code"
                        wire:model.defer="form.code" igroup-size="sm"
                        maxlength="6" inputmode="numeric"
                        :disabled="$readOnly" />
                </div>
            </div>

            @if ($form['kind'] === \App\Enums\AccountKind::MONEY && ! $isEditing)
                <div class="row">
                    <div class="col-12 col-sm-6">
                        <x-adminlte-input name="form.balance_as_of" label="As of Date" type="date"
                            wire:model.defer="form.balance_as_of" igroup-size="sm"
                            :disabled="$readOnly" />
                    </div>

                    <div class="col-12 col-sm-6">
                        <x-adminlte-input name="form.starting_balance" label="Starting Balance"
                            type="number" step="0.01" min="0"
                            wire:model.defer="form.starting_balance" igroup-size="sm"
                            :disabled="$readOnly" />
                    </div>
                </div>
            @endif

            <x-adminlte-textarea name="form.description" label="Description" rows="2"
                placeholder="What this account is for..."
                wire:model.defer="form.description" igroup-size="sm"
                :disabled="$readOnly" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />

            @unless ($readOnly)
                <x-adminlte-button label="{{ $isEditing ? 'Save' : 'Add Account' }}" theme="primary"
                    icon="fas fa-fw fa-save" wire:click="save" wire:loading.attr="disabled" class="btn-sm"
                    :disabled="! $loaded" />
                <span wire:loading wire:target="save" class="ml-2 text-info small">
                    <i class="fas fa-spinner fa-spin"></i> Saving...
                </span>
            @endunless
        </x-slot>
    </x-adminlte-modal>
</div>
