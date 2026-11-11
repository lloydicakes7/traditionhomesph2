<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS                                                     --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search categories..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Product\ProductCategory::class)
                    <x-adminlte-button label="Add Category" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#productCategoryFormModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\Product\ProductCategory::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#productCategoryMassDeleteModal"
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
                        @can('delete', \App\Models\Product\ProductCategory::class)
                            <th class="text-center align-middle" style="width: 4%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="align-middle" style="width: 64%;">Category</th>
                        <th class="align-middle text-center" style="width: 16%;">Items</th>
                        <th class="text-center align-middle" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr wire:key="product-category-row-{{ $category->id }}">
                            @can('delete', \App\Models\Product\ProductCategory::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="category-{{ $category->id }}" value="{{ $category->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="category-{{ $category->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="font-weight-medium text-truncate">{{ $category->name }}</div>
                                @if ($category->description)
                                    <div class="text-muted small text-truncate">{{ \Illuminate\Support\Str::limit($category->description, 90) }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-info">{{ $category->products_count }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="categoryActions{{ $category->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="categoryActions{{ $category->id }}">
                                        @can('update', \App\Models\Product\ProductCategory::class)
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#productCategoryFormModal" data-id="{{ $category->id }}">
                                                <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                            </a>
                                        @endcan
                                        @can('delete', \App\Models\Product\ProductCategory::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#productCategoryDeleteModal" data-id="{{ $category->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search ? 'No categories match your search.' : 'No categories yet. Group the price list — Fees, IDs, Clearances.' }}
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
            <div class="card mb-3 shadow-sm" wire:key="product-category-card-{{ $category->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Product\ProductCategory::class)
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
                                @if ($category->description)
                                    <div class="small text-muted text-truncate">
                                        {{ \Illuminate\Support\Str::limit($category->description, 60) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="text-muted small" style="font-size:0.7rem;">ITEMS</div>
                    <span class="badge badge-info">{{ $category->products_count }}</span>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobCategoryActions{{ $category->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobCategoryActions{{ $category->id }}">
                            @can('update', \App\Models\Product\ProductCategory::class)
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#productCategoryFormModal" data-id="{{ $category->id }}">
                                    <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                </a>
                            @endcan

                            @can('delete', \App\Models\Product\ProductCategory::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#productCategoryDeleteModal" data-id="{{ $category->id }}">
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
                    <i class="fas fa-fw fa-folder-open fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search ? 'No categories match your search.' : 'No categories yet. Group the price list — Fees, IDs, Clearances.' }}
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
