<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + ACTIONS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search categories"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Expense\ExpenseCategory::class)
                    <x-adminlte-button label="Add Category" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#expenseCategoryFormModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\Expense\ExpenseCategory::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#expenseCategoryMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- TABLE                                                                --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        @can('delete', \App\Models\Expense\ExpenseCategory::class)
                            <th class="text-center align-middle" style="width: 3%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="align-middle" style="width: 30%;">Category</th>
                        <th class="align-middle" style="width: 35%;">Description</th>
                        <th class="text-center align-middle" style="width: 12%;">Lines</th>
                        <th class="text-center align-middle" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr wire:key="expense-category-row-{{ $category->id }}">
                            @can('delete', \App\Models\Expense\ExpenseCategory::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="category-{{ $category->id }}" value="{{ $category->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="category-{{ $category->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle font-weight-medium">{{ $category->name }}</td>
                            <td class="align-middle text-truncate small text-muted" style="max-width: 0;">
                                {{ $category->description ?: 'None' }}
                            </td>
                            <td class="align-middle text-center">{{ $category->items_count }}</td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="categoryActions{{ $category->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="categoryActions{{ $category->id }}">
                                        @can('update', \App\Models\Expense\ExpenseCategory::class)
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseCategoryFormModal" data-id="{{ $category->id }}">
                                                <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                            </a>
                                        @endcan

                                        @can('delete', \App\Models\Expense\ExpenseCategory::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#expenseCategoryDeleteModal" data-id="{{ $category->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-tags fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search
                                        ? 'No categories match your search.'
                                        : 'No expense categories yet. Add one to start filing spending under labels the office keeps.' }}
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
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($categories as $category)
            <div class="card mb-3 shadow-sm" wire:key="expense-category-card-{{ $category->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Expense\ExpenseCategory::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-category-{{ $category->id }}" value="{{ $category->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-category-{{ $category->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $category->name }}
                                </div>
                                <div class="small text-muted">{{ $category->items_count }} line{{ $category->items_count === 1 ? '' : 's' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-muted">
                        {{ $category->description ?: 'No description.' }}
                    </div>
                    <div class="bg-light py-2 text-right">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                id="mobCategoryActions{{ $category->id }}" data-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                Actions
                            </button>
                            <div class="dropdown-menu dropdown-menu-right"
                                aria-labelledby="mobCategoryActions{{ $category->id }}">
                                @can('update', \App\Models\Expense\ExpenseCategory::class)
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#expenseCategoryFormModal" data-id="{{ $category->id }}">
                                        <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                    </a>
                                @endcan
                                @can('delete', \App\Models\Expense\ExpenseCategory::class)
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#expenseCategoryDeleteModal" data-id="{{ $category->id }}">
                                        <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-tags fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search
                            ? 'No categories match your search.'
                            : 'No expense categories yet. Add one to start filing spending under labels the office keeps.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>


    {{-- Pagination --}}
    @if ($categories->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $categories->links() }}
        </div>
    @endif

</div>
