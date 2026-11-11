<div wire:ignore.self>
    <x-adminlte-modal id="postToJournalModal" title="Post to Journal"
        icon="fas fa-fw fa-book" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading posting…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="alert alert-light border py-2 px-3 mb-3">
                <div class="font-weight-bold">{{ $sourceLabel }} · {{ $reference }}</div>
                <div class="text-muted small">
                    {{ $party }} · {{ $entryDate }}
                </div>
                @if ($description)
                    <div class="small mt-1">{{ $description }}</div>
                @endif
            </div>

            <dl class="row small mb-3">
                <dt class="col-5 text-muted font-weight-normal">Amount</dt>
                <dd class="col-7 text-right font-weight-bold">{{ $amount }}</dd>
            </dl>

            <x-adminlte-select name="account_id" label="Money Account"
                wire:model.defer="account_id" igroup-size="sm">
                <option value="">Choose the cash or bank account</option>
                @foreach ($accounts as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </x-adminlte-select>

            @error('posting')
                <div class="alert alert-danger py-2 px-3 small mt-3 mb-0">{{ $message }}</div>
            @enderror
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Post to Journal" theme="primary" icon="fas fa-fw fa-book"
                wire:click="confirm" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Posting...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
