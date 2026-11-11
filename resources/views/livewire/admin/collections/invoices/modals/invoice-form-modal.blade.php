<div wire:ignore.self>
    <x-adminlte-modal id="invoiceFormModal"
        title="{{ $invoiceId ? 'Edit Invoice' : 'Add Invoice' }}"
        icon="fas fa-fw fa-file-invoice" theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading invoice form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="row">
                {{-- ========================================================= --}}
                {{-- WHO IS BEING BILLED                                        --}}
                {{-- ========================================================= --}}
                <div class="col-12 col-lg-6">
                    @if ($bookingId)
                        @if ($isGuest)
                            <x-adminlte-input name="form.billed_to_name" label="Billed To"
                                placeholder="Guest name" wire:model.defer="form.billed_to_name" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user-friends"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        @else
                            <x-adminlte-input name="booking_homeowner" label="Homeowner" disabled
                                value="{{ $currentBooking->booker_name ?? '—' }}" igroup-size="sm" />
                        @endif
                    @else
                        @unless ($invoiceId)
                            <div class="form-group mb-3">
                                <label class="mb-1">Bill</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <div class="custom-control custom-radio mr-4">
                                        <input type="radio" id="scopeSingle" value="single"
                                            class="custom-control-input" wire:model="scope">
                                        <label class="custom-control-label" for="scopeSingle">
                                            Single Homeowner
                                            <br><small>Select to a single homeowner</small>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="scopeAll" value="all"
                                            class="custom-control-input" wire:model="scope">
                                        <label class="custom-control-label" for="scopeAll">
                                            All Homeowners
                                            <br><small>Selected every homeowners in file</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endunless

                        @if ($scope === 'single' || $invoiceId)
                            <x-adminlte-select2 id="invoicePropertySelect" name="form.user_id" label="Homeowner"
                                wire:model="form.user_id" igroup-size="sm"
                                :config="['placeholder' => 'Select a homeowner...', 'width' => '100%']">
                                <option value=""></option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->resident?->full_name }}
                                    </option>
                                @endforeach
                            </x-adminlte-select2>
                        @else
                            <x-adminlte-input name="all_users_note" label="Homeowner" disabled
                                value="Every homeowners on file will be invoiced" igroup-size="sm" />
                        @endif
                    @endif
                </div>

                {{-- ========================================================= --}}
                {{-- WHEN IT IS DUE                                             --}}
                {{-- ========================================================= --}}
                <div class="col-12 col-lg-6">
                    <div class="row">
                        <div class="col-12 col-sm-6">
                            <x-adminlte-input name="form.bill_date" label="Bill Date" type="date"
                                wire:model.defer="form.bill_date" igroup-size="sm" />
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-adminlte-input name="form.due_date" label="Due Date" type="date"
                                wire:model.defer="form.due_date" igroup-size="sm" />
                        </div>
                    </div>

                    <x-adminlte-input name="form.discount" label="Discount" type="number" step="0.01" min="0"
                        wire:model.lazy="form.discount" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>

                <div class="col-12">
                    <x-adminlte-textarea name="form.notes" label="Notes" rows="3"
                        placeholder="Anything the homeowner should know about this charge"
                        wire:model.defer="form.notes" igroup-size="sm" />
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- ITEMS                                                          --}}
            {{-- ============================================================= --}}
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="mb-0">Items</label>
                @unless ($bookingId)
                    @can('create', \App\Models\Product::class)
                        <button type="button" class="btn btn-link btn-sm p-0" wire:click="quickAddProduct"
                            wire:loading.attr="disabled">
                            <i class="fas fa-fw fa-plus-circle"></i> New
                        </button>
                    @endcan
                @endunless
            </div>

            <div class="table-responsive border rounded">
                <table class="table table-sm mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th style="min-width:220px;">Product / Service</th>
                            <th style="min-width:100px;">Qty</th>
                            <th style="min-width:100px;">Unit Price</th>
                            <th style="min-width:100px;">Amount</th>
                            <th style="min-width:50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $index => $item)
                            <tr>
                                <td>
                                    @if ($bookingId)
                                        <input type="text" class="form-control"
                                            value="{{ $item['name'] ?? '' }}" disabled>
                                    @else
                                        <select class="form-control form-control @error('items.' . $index . '.product_id') is-invalid @enderror"
                                            wire:model="items.{{ $index }}.product_id">
                                            <option value="">Select an item…</option>
                                            @foreach ($products->groupBy(function ($product) {
                                                return $product->category?->name ?? 'Uncategorised';
                                            }) as $categoryName => $group)
                                                <optgroup label="{{ $categoryName }}">
                                                    @foreach ($group as $product)
                                                        <option value="{{ $product->id }}">
                                                            {{ $product->name }} — {{ $product->price_display }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        @error('items.' . $index . '.product_id')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    @endif
                                </td>
                                <td>
                                    <input type="number" step="1" min="1"
                                        class="form-control form-control @error('items.' . $index . '.quantity') is-invalid @enderror"
                                        wire:model.lazy="items.{{ $index }}.quantity" {{ $bookingId ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <input type="number" step="1" min="0"
                                        class="form-control form-control @error('items.' . $index . '.unit_price') is-invalid @enderror"
                                        wire:model.lazy="items.{{ $index }}.unit_price" {{ $bookingId ? 'disabled' : '' }}>
                                </td>
                                <td>
                                    <span class="font-weight-medium">{{ \App\Utility\MoneyHelper::format($item['amount'] ?? 0) }}</span>
                                </td>
                                <td class="text-right">
                                    @unless ($bookingId)
                                        <button type="button" class="btn btn-danger btn-sm"
                                            wire:click="removeItem({{ $index }})"
                                            wire:loading.attr="disabled" title="Remove item">
                                            <i class="fas fa-fw fa-trash"></i>
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">
                                    Add at least one item to bill.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @error('items')
                <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror

            @unless ($bookingId)
                <button type="button" class="btn btn-primary btn-sm mt-2" wire:click="addItem"
                    wire:loading.attr="disabled">
                    <i class="fas fa-fw fa-plus"></i> Add Item
                </button>
            @else
                <p class="text-muted small mb-0 mt-2">
                    The lines follow the reservation. Edit the booking to change its rate or deposit before payment.
                </p>
            @endunless

            {{-- ============================================================= --}}
            {{-- TOTALS                                                         --}}
            {{-- ============================================================= --}}
            <div class="row justify-content-end mt-3">
                <div class="col-12 col-sm-5">
                    <dl class="row mb-0 small">
                        <dt class="col-7 text-muted font-weight-normal">Subtotal</dt>
                        <dd class="col-5 text-right">{{ $subtotalDisplay }}</dd>

                        <dt class="col-7 text-muted font-weight-normal">Discount</dt>
                        <dd class="col-5 text-right">-{{ \App\Utility\MoneyHelper::format($form['discount'] ?: 0) }}</dd>

                        <dt class="col-7 font-weight-bold border-top pt-2">Total</dt>
                        <dd class="col-5 text-right font-weight-bold border-top pt-2">{{ $totalDisplay }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="{{ $invoiceId ? 'Save Invoice' : 'Raise Invoice' }}" theme="primary"
                icon="fas fa-fw fa-save" wire:click="confirm" wire:loading.attr="disabled" class="btn-sm"
                :data-thha-progress="! $invoiceId && $scope === 'all' ? 'Raising invoices for all homeowners…' : null"
                :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
