<div wire:ignore.self>
    <x-adminlte-modal id="advanceDepositApplyModal" title="Apply Advance Payment"
        icon="fas fa-fw fa-hand-holding-usd" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading advance deposit…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($deposit)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold">{{ $deposit->holder_name }}</div>
                    <div class="small mt-1">
                        Credit available
                        <span class="font-weight-bold">{{ $deposit->balance_display }}</span>
                    </div>
                </div>

                @if ($deposit->is_open && ! empty($bills))
                    <x-adminlte-select name="bill" label="Bill to Settle" wire:model.lazy="bill" igroup-size="sm">
                        <option value="">Select the bill this credit settles…</option>
                        @foreach ($bills as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-adminlte-select>

                    <x-adminlte-input name="amount" label="Amount to Apply" type="number" step="0.01" min="0.01"
                        wire:model.defer="amount" igroup-size="sm"
                        help="No more than the credit available and no more than the bill's balance.">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">{{ \App\Utility\MoneyHelper::symbol() }}</div>
                        </x-slot>
                    </x-adminlte-input>
                @else
                    <p class="text-muted small mb-0">
                        {{ $deposit->is_open
                            ? 'This homeowner has nothing outstanding to apply the credit to.'
                            : 'This deposit holds no credit to apply.' }}
                    </p>
                @endif
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Apply Credit" theme="success" icon="fas fa-fw fa-check"
                wire:click="confirm" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $canApply" />
            <span wire:loading wire:target="confirm" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Applying...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
