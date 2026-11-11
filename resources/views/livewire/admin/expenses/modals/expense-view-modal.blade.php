<div wire:ignore.self>
    <x-adminlte-modal id="expenseViewModal" title="Expense Details"
        icon="fas fa-fw fa-receipt" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading expense…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($expense)
                {{-- ========================================================= --}}
                {{-- HEADER: reference, status, what it was for                  --}}
                {{-- ========================================================= --}}
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="h5 mb-0 font-weight-bold">{{ $expense->reference }}</div>
                        <div class="text-muted small">Recorded {{ $expense->expense_date_display }} · {{ $expense->supplier_label }}</div>
                    </div>
                    <span class="badge badge-{{ $expense->status_badge }}">
                        {{ $expense->status_label }}
                    </span>
                </div>

                {{-- ========================================================= --}}
                {{-- FINANCIAL SUMMARY                                           --}}
                {{-- ========================================================= --}}
                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="text-muted small">Total</div>
                            <div class="h5 mb-0 font-weight-bold">{{ $expense->subtotal_display }}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="text-muted small">Logged By</div>
                            <div class="small font-weight-medium mt-1">{{ $expense->createdByAdmin?->name ?: '—' }}</div>
                        </div>
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- DRAFT-TO-APPROVED PROGRESS                                  --}}
                {{-- The two states an expense moves through.                    --}}
                {{-- ========================================================= --}}
                <label class="mb-2">Progress</label>
                <div class="mb-3 px-2">
                    @php
                        $steps = [
                            ['key' => 'draft', 'label' => 'Draft', 'icon' => 'fas fa-file-alt'],
                            ['key' => 'approved', 'label' => 'Approved', 'icon' => 'fas fa-check-circle'],
                        ];
                        $order = ['draft' => 1, 'approved' => 2];
                        $current = $order[$expense->status] ?? 1;
                    @endphp
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-success"
                            style="width: {{ $current / 2 * 100 }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        @foreach ($steps as $index => $step)
                            @php($stepNo = $index + 1)
                            <div class="text-center" style="width: 50%;">
                                <i class="{{ $step['icon'] }} {{ $current >= $stepNo ? 'text-success' : 'text-muted' }}"
                                    title="{{ $step['label'] }}"></i>
                                <div class="small {{ $current >= $stepNo ? 'font-weight-bold' : 'text-muted' }}"
                                    style="font-size: 0.72rem;">
                                    {{ $step['label'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ========================================================= --}}
                {{-- CATEGORY BREAKDOWN                                          --}}
                {{-- ========================================================= --}}
                <label class="mb-1">Line Items</label>
                <div class="table-responsive border rounded mb-3">
                    <table class="table table-sm mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Category</th>
                                <th>Item</th>
                                <th style="width:120px;" class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expense->items as $item)
                                <tr>
                                    <td class="small">
                                        <span class="badge badge-light border">{{ $item->category_label }}</span>
                                    </td>
                                    <td class="small">{{ $item->description }}</td>
                                    <td class="text-right">{{ $item->amount_display }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No line items on this expense.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">Total</td>
                                <td class="text-right font-weight-bold">{{ $expense->subtotal_display }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if (count($breakdown) > 1)
                    <label class="mb-1">Category Breakdown</label>
                    <div class="mb-3">
                        @foreach ($breakdown as $group)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small">
                                    <span class="font-weight-medium">{{ $group['category'] }}</span>
                                    <span class="text-muted">{{ $group['display'] }} · {{ $group['percent'] }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $group['percent'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ========================================================= --}}
                {{-- JOURNAL ENTRY — the posting accounting made                 --}}
                {{-- ========================================================= --}}
                <label class="mb-1">Journal Entry</label>
                @if ($expense->journalEntry)
                    <div class="table-responsive border rounded mb-3">
                        <table class="table table-sm mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th colspan="3" class="small">
                                        {{ $expense->journalEntry->entry_no }}
                                        · {{ $expense->journalEntry->entry_date_display }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expense->journalEntry->lines as $line)
                                    <tr>
                                        <td class="small">
                                            <span class="badge badge-{{ $line->side === 'debit' ? 'danger' : 'success' }}">
                                                {{ ucfirst($line->side) }}
                                            </span>
                                        </td>
                                        <td class="small">{{ $line->account_name }}</td>
                                        <td class="text-right small">{{ $line->amount_display }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted small mb-3">
                        <i class="fas fa-fw fa-balance-scale"></i>
                        No journal entry yet — accounting posts it from the expenses list.
                    </p>
                @endif
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
