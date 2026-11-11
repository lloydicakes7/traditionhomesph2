<div wire:ignore.self>
    <x-adminlte-modal id="expenseWizardModal"
        title="{{ $editing ? 'Edit Expense' : 'Record Expense' }}"
        icon="fas fa-fw fa-receipt" theme="primary" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading expense form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            {{-- ============================================================= --}}
            {{-- STEP INDICATOR                                                 --}}
            {{-- ============================================================= --}}
            <div class="d-flex justify-content-between mb-4 px-2">
                @foreach ([1 => 'Details', 2 => 'Line Items'] as $stepNo => $stepLabel)
                    <div class="text-center" style="width: 50%;">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center {{ $step >= $stepNo ? 'bg-primary' : 'bg-light border' }}"
                            style="width: 32px; height: 32px;">
                            @if ($step > $stepNo)
                                <i class="fas fa-check text-light"></i>
                            @else
                                <span class="{{ $step === $stepNo ? 'text-white font-weight-bold' : 'text-muted font-weight-bold' }}">{{ $stepNo }}</span>
                            @endif
                        </div>
                        <div class="small {{ $step >= $stepNo ? 'font-weight-bold' : 'text-muted' }}">{{ $stepLabel }}</div>
                    </div>
                @endforeach
            </div>

            {{-- ============================================================= --}}
            {{-- STEP 1 — DETAILS                                               --}}
            {{-- The reference number is the module's own sequence, assigned on --}}
            {{-- save and shown here once the expense exists.                  --}}
            {{-- ============================================================= --}}
            @if ($step === 1)
                @if ($editing)
                    <div class="border bg-light rounded p-3 mb-3">
                        <div class="small text-muted">Reference Number</div>
                        <div class="font-weight-bold">{{ $expenseReference }}</div>
                    </div>
                @else
                    <div class="border bg-light rounded mb-3">
                        <p class="text-muted small p-3 mb-0">
                            <i class="fas fa-fw fa-hashtag"></i>
                            A reference number (EXP-{{ now()->year }}-####) is assigned when the expense is saved.
                        </p>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12 col-sm-6">
                        <x-adminlte-input name="form.expense_date" label="Expense Date" type="date"
                            wire:model.defer="form.expense_date" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                    <div class="col-12 col-sm-6">
                        <x-adminlte-select name="form.supplier_id" label="Supplier / Payee"
                            wire:model.defer="form.supplier_id" igroup-size="sm">
                            <option value="">Not specified</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </x-adminlte-select>
                    </div>
                </div>

                <x-adminlte-textarea name="form.description" label="General Description" rows="3"
                    placeholder="What this expense is for — e.g. gate guard wages for June"
                    wire:model.defer="form.description" igroup-size="sm" />
            @endif

            {{-- ============================================================= --}}
            {{-- STEP 2 — LINE ITEMS BY CATEGORY                                --}}
            {{-- ============================================================= --}}
            @if ($step === 2)
                <div class="table-responsive border rounded">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="min-width:200px;">Category</th>
                                <th style="min-width:220px;">Description</th>
                                <th style="min-width:120px;">Amount</th>
                                <th style="min-width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td>
                                        <select class="form-control form-control @error('items.' . $index . '.expense_category_id') is-invalid @enderror"
                                            wire:model="items.{{ $index }}.expense_category_id">
                                            <option value="">Uncategorised</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('items.' . $index . '.expense_category_id')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control"
                                            placeholder="What was bought"
                                            wire:model.lazy="items.{{ $index }}.description">
                                        @error('items.' . $index . '.description')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" class="form-control form-control text-right"
                                            wire:model.lazy="items.{{ $index }}.amount">
                                        @error('items.' . $index . '.amount')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td class="text-right">
                                        <button type="button" class="btn btn-danger btn-sm"
                                            wire:click="removeItem({{ $index }})"
                                            wire:loading.attr="disabled" title="Remove line">
                                            <i class="fas fa-fw fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Add at least one line item.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="2" class="text-right font-weight-bold">Total</td>
                                <td class="text-right font-weight-bold">{{ $subtotalDisplay }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @error('items')
                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                @enderror

                <button type="button" class="btn btn-primary btn-sm mt-2" wire:click="addItem"
                    wire:loading.attr="disabled">
                    <i class="fas fa-fw fa-plus"></i> Add Line Item
                </button>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            @if ($loaded && $step > 1)
                <x-adminlte-button label="Back" theme="secondary" icon="fas fa-fw fa-arrow-left"
                    wire:click="previousStep" class="btn-sm" />
            @endif
            @if ($loaded && $step < 2)
                <x-adminlte-button label="Next" theme="primary" icon="fas fa-fw fa-arrow-right"
                    wire:click="nextStep" wire:loading.attr="disabled" class="btn-sm" />
            @elseif ($loaded)
                <x-adminlte-button label="Save Expense" theme="success" icon="fas fa-fw fa-save"
                    wire:click="save" wire:loading.attr="disabled" class="btn-sm" />
                <span wire:loading wire:target="save" class="ml-2 text-success small">
                    <i class="fas fa-spinner fa-spin"></i> Saving...
                </span>
            @endif
        </x-slot>
    </x-adminlte-modal>
</div>
