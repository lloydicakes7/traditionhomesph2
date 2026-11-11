<div>
    {{-- =================================================================== --}}
    {{-- SUMMARY CARDS                                                       --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="danger" icon="fas fa-receipt" text="Spent This Month"
                title="{{ \App\Utility\MoneyHelper::format($totals['month_total']) }}" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="success" icon="fas fa-check-circle" text="Approved To Date"
                title="{{ \App\Utility\MoneyHelper::format($totals['approved_total']) }}" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="warning" icon="fas fa-hourglass-half" text="Awaiting Approval"
                title="{{ \App\Utility\MoneyHelper::format($totals['awaiting_total']) }}" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box theme="info" icon="fas fa-file-invoice-dollar" text="Total Expenses"
                title="{{ $totals['total_count'] }}" />
        </div>
    </div>

    <x-adminlte-card>
        <div class="row">
            <div class="col-12 col-sm-auto ml-auto">
                @can('export', \App\Models\Expense\Expense::class)
                    <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-export"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>

    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-3 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search Ref. #, supplier or item"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-6 col-md-2 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterMonth" wire:model="filterMonth" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Months</option>
                    @foreach ($months as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-6 col-md-2 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterStatus" wire:model="filterStatus" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-6 col-md-3 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterCategory" wire:model="filterCategory" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-6 col-md-auto ml-auto">
                @can('create', \App\Models\Expense\Expense::class)
                    <x-adminlte-button label="Record Expense" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#expenseWizardModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- MASS DELETE BAR                                                      --}}
    {{-- =================================================================== --}}
    @if (!empty($selectedIds))
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="text-muted small font-weight-bold">
                <i class="fas fa-fw fa-check-square"></i>
                {{ count($selectedIds) }} selected
            </span>
            @can('delete', \App\Models\Expense\Expense::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#expenseMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        @can('delete', \App\Models\Expense\Expense::class)
                            <th class="text-center align-middle" style="width: 3%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('reference')" style="width: 20%;">
                            Reference / Supplier
                            @if ($sortField === 'reference')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle" style="width: 25%;">Items / Categories</th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('subtotal')" style="width: 14%;">
                            Amount
                            @if ($sortField === 'subtotal')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle text-center" wire:click="sortBy('status')" style="width: 12%;">
                            Status
                            @if ($sortField === 'status')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="text-center align-middle" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr wire:key="expense-row-{{ $expense->id }}">
                            @can('delete', \App\Models\Expense\Expense::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="expense-{{ $expense->id }}" value="{{ $expense->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="expense-{{ $expense->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="font-weight-medium text-truncate">{{ $expense->reference }}</div>
                                <div class="small text-muted text-truncate">
                                    {{ $expense->expense_date_display }} · {{ $expense->supplier_label }}
                                </div>
                            </td>
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="small text-truncate">{{ $expense->description ?: $expense->items->first()?->description }}</div>
                                <div class="text-muted small text-truncate">
                                    {{ $expense->items->pluck('category_name')->unique()->implode(', ') }}
                                </div>
                            </td>
                            <td class="align-middle text-right font-weight-medium">
                                {{ $expense->subtotal_display }}
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-{{ $expense->status_badge }}">
                                    {{ $expense->status_label }}
                                </span>
                                @if ($expense->journalEntry)
                                    <div class="text-muted small mt-1">{{ $expense->journalEntry->entry_no }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="expenseActions{{ $expense->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="expenseActions{{ $expense->id }}">
                                        <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseViewModal" data-id="{{ $expense->id }}">
                                            <i class="fas fa-fw fa-eye mr-1"></i> View Expense
                                        </a>

                                        @can('approve', \App\Models\Expense\Expense::class)
                                            @if ($expense->status_enum && $expense->status_enum->is(\App\Enums\ExpenseStatus::DRAFT()))
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseApproveModal" data-id="{{ $expense->id }}">
                                                    <i class="fas fa-fw fa-clipboard-check mr-1"></i> Approve
                                                </a>
                                            @endif
                                        @endcan

                                        @if ($expense->status_enum && $expense->status_enum->is(\App\Enums\ExpenseStatus::APPROVED()) && ! $expense->journal_entry_id)
                                            @can('create', \App\Models\JournalEntry\JournalEntry::class)
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="postToJournal({{ $expense->id }})">
                                                    <i class="fas fa-fw fa-book mr-1"></i> Post to Journal
                                                </a>
                                            @endcan
                                        @endif

                                        @can('update', \App\Models\Expense\Expense::class)
                                            @if ($expense->status_enum && $expense->status_enum->isEditable())
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseWizardModal" data-id="{{ $expense->id }}">
                                                    <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                                </a>
                                            @endif
                                        @endcan

                                        @can('delete', \App\Models\Expense\Expense::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#expenseDeleteModal" data-id="{{ $expense->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-receipt fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterStatus || $filterCategory || $filterMonth
                                        ? 'No expenses match your filters.'
                                        : 'No expenses recorded yet. Record one to log what the association spends.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS                                                         --}}
    {{-- Same data as the desktop row, laid out as a stacked card so nothing   --}}
    {{-- gets truncated or squeezed on a phone-width screen.                  --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($expenses as $expense)
            <div class="card mb-3 shadow-sm" wire:key="expense-card-{{ $expense->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Expense\Expense::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-expense-{{ $expense->id }}" value="{{ $expense->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-expense-{{ $expense->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $expense->reference }}
                                </div>
                                <div class="small text-muted">{{ $expense->expense_date_display }} · {{ $expense->supplier_label }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $expense->status_badge }} flex-shrink-0">
                            {{ $expense->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate">{{ $expense->description ?: $expense->items->first()?->description }}</div>
                    <div class="text-muted small text-truncate mb-2">
                        {{ $expense->items->pluck('category_name')->unique()->implode(', ') }}
                    </div>

                    <div>
                        <div class="text-muted small" style="font-size:0.7rem;">AMOUNT</div>
                        <div class="small font-weight-medium">{{ $expense->subtotal_display }}</div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobExpenseActions{{ $expense->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobExpenseActions{{ $expense->id }}">
                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseViewModal" data-id="{{ $expense->id }}">
                                <i class="fas fa-fw fa-eye mr-1"></i> View Expense
                            </a>

                            @can('approve', \App\Models\Expense\Expense::class)
                                @if ($expense->status_enum && $expense->status_enum->is(\App\Enums\ExpenseStatus::DRAFT()))
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseApproveModal" data-id="{{ $expense->id }}">
                                        <i class="fas fa-fw fa-clipboard-check mr-1"></i> Approve
                                    </a>
                                @endif
                            @endcan

                            @if ($expense->status_enum && $expense->status_enum->is(\App\Enums\ExpenseStatus::APPROVED()) && ! $expense->journal_entry_id)
                                @can('create', \App\Models\JournalEntry\JournalEntry::class)
                                    <a class="dropdown-item" href="#"
                                        wire:click.prevent="postToJournal({{ $expense->id }})">
                                        <i class="fas fa-fw fa-book mr-1"></i> Post to Journal
                                    </a>
                                @endcan
                            @endif

                            @can('update', \App\Models\Expense\Expense::class)
                                @if ($expense->status_enum && $expense->status_enum->isEditable())
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseWizardModal" data-id="{{ $expense->id }}">
                                        <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                    </a>
                                @endif
                            @endcan

                            @can('delete', \App\Models\Expense\Expense::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#expenseDeleteModal" data-id="{{ $expense->id }}">
                                    <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-receipt fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterStatus || $filterCategory || $filterMonth
                            ? 'No expenses match your filters.'
                            : 'No expenses recorded yet. Record one to log what the association spends.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($expenses->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $expenses->links() }}
        </div>
    @endif

</div>
