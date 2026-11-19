<div wire:ignore.self>
    <x-adminlte-modal id="advancePaymentModal" title="Advance Payment"
        icon="fas fa-fw fa-money-check-alt" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading advance payment form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <x-adminlte-select2 id="advancePaymentOwner" name="user_id" label="Homeowner"
                wire:model="user_id" igroup-size="sm"
                :config="['placeholder' => 'Select a homeowner...', 'width' => '100%']">
                <option value=""></option>
                @foreach ($owners as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </x-adminlte-select2>

            <x-adminlte-select2 id="advancePaymentDeposit" name="advance_deposit_id" label="Advance Deposit"
                wire:model="advance_deposit_id" igroup-size="sm"
                :config="['placeholder' => 'No advance deposit', 'width' => '100%']">
                <option value="">No advance deposit</option>
                @foreach ($deposits as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </x-adminlte-select2>

            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="from" label="From Month" type="month"
                        wire:model.lazy="from" igroup-size="sm" />
                </div>
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="to" label="To Month" type="month"
                        wire:model.lazy="to" igroup-size="sm" />
                </div>
            </div>

            <div class="border rounded p-3 mb-3 bg-light">
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Months to bill and pay</span>
                    <span class="font-weight-bold">{{ $preview['created'] }}</span>
                </div>
                <div class="d-flex justify-content-between small mt-1">
                    <span class="text-muted">Existing bills to settle</span>
                    <span class="font-weight-bold">{{ $preview['settled'] }}</span>
                </div>
                <div class="d-flex justify-content-between small mt-1">
                    <span class="text-muted">Months already paid</span>
                    <span class="font-weight-bold">{{ $preview['skipped'] }}</span>
                </div>
                <div class="d-flex justify-content-between small mt-1">
                    <span class="text-muted">Total to receive</span>
                    <span class="font-weight-bold">{{ $preview['total_display'] }}</span>
                </div>
            </div>

            <h6 class="text-uppercase text-muted small font-weight-bold">Payment Details</h6>

            @if (! $advance_deposit_id)
                <div class="row">
                    <div class="col-12">
                        <x-adminlte-input name="paid_at" label="Date Received" type="datetime-local"
                            wire:model.defer="paid_at" igroup-size="sm" />
                    </div>

                    <div class="col-12 col-sm-6">
                        <x-adminlte-select name="payment_method_id" label="Payment Method"
                            wire:model="payment_method_id" igroup-size="sm">
                            <option value="">{{ \App\Models\PaymentMethod::IN_PERSON_LABEL }}</option>
                            @foreach ($methods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </x-adminlte-select>

                        <div class="mt-2">
                            <x-adminlte-input name="reference_no" label="OR / Reference No."
                                placeholder="OR or transaction number"
                                wire:model.defer="reference_no" igroup-size="sm" />
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="mb-1">How to Pay</label>
                        <div class="border rounded p-2 bg-light h-100">
                            @if ($selectedMethod)
                                <div class="small font-weight-bold text-break">{{ $selectedMethod->name }}</div>
                                <div class="text-muted small text-break">
                                    {{ $selectedMethod->details ?: 'No instructions.' }}
                                </div>
                                @if ($selectedMethod->qr_url)
                                    <a href="{{ $selectedMethod->qr_url }}" target="_blank" rel="noopener"
                                        title="Open the {{ $selectedMethod->name }} QR code">
                                        <img src="{{ $selectedMethod->qr_url }}"
                                            alt="QR code for {{ $selectedMethod->name }}"
                                            class="rounded bg-white mt-2" style="max-height:110px;">
                                    </a>
                                @else
                                    <div class="text-muted small mt-1">No QR code.</div>
                                @endif
                            @else
                                <div class="small font-weight-bold">{{ \App\Models\PaymentMethod::IN_PERSON_LABEL }}</div>
                                <div class="text-muted small">Face to face transaction.</div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-info py-2 small">
                    <i class="fas fa-fw fa-info-circle mr-1"></i>
                    The selected advance deposit settles these months. No OR number is needed.
                </div>
            @endif

            <x-adminlte-textarea name="notes" label="Notes" rows="2"
                wire:model.defer="notes" igroup-size="sm" placeholder="Optional" />
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Generate and Pay" theme="success" icon="fas fa-fw fa-check"
                wire:click="confirm" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-success small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
