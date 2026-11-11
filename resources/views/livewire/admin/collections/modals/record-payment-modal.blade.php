<div wire:ignore.self>
    <x-adminlte-modal id="recordPaymentModal" title="Record Payment"
        icon="fas fa-fw fa-hand-holding-usd" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading payment form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($bill)
                <div class="border rounded p-3 mb-3">
                    <div class="font-weight-bold">{{ $selected->reference ?? '—' }}</div>
                    <div class="text-muted small">
                        @if ($selected->billing_period)
                            · {{ $selected->billing_period_display }}
                        @endif
                    </div>
                    <div class="small mt-1">
                        Balance <span class="font-weight-bold">{{ $selected->balance_display ?? '—' }}</span>
                    </div>
                </div>
            @else
                <x-adminlte-select2 id="recordPaymentBill" name="bill" label="Bill" wire:model="bill"
                    igroup-size="sm"
                    :config="['placeholder' => 'Select the bill this payment settles...', 'width' => '100%']">
                    @if (empty($bills))
                        <option value="">There are no bills on the books yet.</option>
                    @else
                         <option value="">Select the bill this payment settles...</option>
                    @endif
                    @foreach ($bills as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select2>
            @endif

            @if ($pending)
                <div class="alert alert-warning py-2 small mb-3">
                    <i class="fas fa-fw fa-hourglass-half mr-1"></i>
                    {{ $pending->amount_display }} filed from the portal on this bill is waiting for approval — approve
                    it on the Payments page instead of recording the same money again.
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="amount" label="Amount Received" type="number" step="0.01" min="0.01"
                        wire:model.lazy="amount" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-money-bill-wave"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="paid_at" label="Date Received" type="datetime-local"
                        wire:model.defer="paid_at" igroup-size="sm" />
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-select name="payment_method_id" label="Payment Method"
                        wire:model="payment_method_id" igroup-size="sm">
                        <option value="">{{ \App\Models\PaymentMethod::IN_PERSON_LABEL }}</option>
                        @foreach ($methods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </x-adminlte-select>
                </div>
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="reference_no" label="Reference No."
                        placeholder="OR / transaction number"
                        wire:model.defer="reference_no" igroup-size="sm" />
                </div>
            </div>

            @if ($selectedMethod && $selectedMethod->qr_url)
                <div class="text-center mb-3">
                    <img src="{{ $selectedMethod->qr_url }}" alt="{{ $selectedMethod->name }} QR code"
                        class="rounded elevation-1" style="max-height:150px;">
                </div>
            @endif

            <label class="mb-1">Proof of Payment</label>
            <div class="d-flex align-items-start mb-3" style="gap:1rem;">
                <div class="border rounded p-2 text-center" style="min-width:110px;">
                    @if ($proof)
                        <img src="{{ $proof->temporaryUrl() }}" alt="Proof of payment preview"
                            style="max-height:90px;max-width:90px;">
                    @else
                        <span class="text-muted small d-inline-block py-4">No image</span>
                    @endif
                </div>

                <div class="flex-grow-1">
                    <x-adminlte-input-file name="proof" label="Attach proof"
                        placeholder="PNG or JPG, up to 2MB" accept="image/*"
                        wire:model="proof" igroup-size="sm" legend="Browse">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-file-invoice-dollar"></i></div>
                        </x-slot>
                    </x-adminlte-input-file>

                    <div wire:loading wire:target="proof" class="mt-1">
                        <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                    </div>
                </div>
            </div>

            <x-adminlte-textarea name="notes" label="Notes" rows="2"
                wire:model.defer="notes" igroup-size="sm" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Record Payment" theme="success" icon="fas fa-fw fa-check"
                wire:click="confirm" wire:loading.attr="disabled" class="btn-sm" :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
