<div>
    {{-- =================================================================== --}}
    {{-- SEARCH + FILTERS + ADD BUTTON                                        --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search products and services..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterType" wire:model="filterType" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Types</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-sm-6 col-md-4 col-lg-2 mb-2 mb-lg-0">
                <x-adminlte-select name="filterCategory" wire:model="filterCategory" igroup-size="sm" fgroup-class="mb-0">
                    <option value="">All Categories</option>
                    @foreach ($categories as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            <div class="col-12 col-md-auto ml-auto">
                @can('create', \App\Models\Product\Product::class)
                    <x-adminlte-button label="Add Product / Service" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#productFormModal" class="btn-sm btn-block" />
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
            @can('delete', \App\Models\Product\Product::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#productMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- Category folds into the name cell so the table holds six columns on   --}}
    {{-- any screen instead of overflowing it.                                 --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        @can('delete', \App\Models\Product\Product::class)
                            <th class="text-center align-middle" style="width: 4%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll"
                                        wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="cursor-pointer align-middle" wire:click="sortBy('name')" style="width: 40%;">
                            Item
                            @if ($sortField === 'name')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle" wire:click="sortBy('type')" style="width: 14%;">
                            Type
                            @if ($sortField === 'type')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="cursor-pointer align-middle text-right" wire:click="sortBy('price')" style="width: 18%;">
                            Price
                            @if ($sortField === 'price')
                                <i class="fas fa-fw fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                            @endif
                        </th>
                        <th class="align-middle text-center" style="width: 12%;">Status</th>
                        <th class="text-center align-middle" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr wire:key="product-row-{{ $product->id }}">
                            @can('delete', \App\Models\Product\Product::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="product-{{ $product->id }}" value="{{ $product->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="product-{{ $product->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle text-truncate" style="max-width: 0;">
                                <div class="font-weight-medium text-truncate">{{ $product->name }}</div>
                                <div class="text-muted small text-truncate">
                                    {{ $product->category?->name ?: 'Uncategorised' }}
                                    @if ($product->description)
                                        · {{ \Illuminate\Support\Str::limit($product->description, 60) }}
                                    @endif
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge badge-{{ $product->type_enum?->badgeTheme() ?? 'secondary' }}">
                                    {{ $product->type_enum?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="align-middle text-right">
                                <span class="small font-weight-medium">{{ $product->price_with_unit }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="productActions{{ $product->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="productActions{{ $product->id }}">
                                        @can('update', \App\Models\Product\Product::class)
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#productFormModal" data-id="{{ $product->id }}">
                                                <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                            </a>
                                            <a class="dropdown-item" href="#" wire:click.prevent="toggleActive({{ $product->id }})">
                                                <i class="fas fa-fw fa-{{ $product->is_active ? 'toggle-on' : 'toggle-off' }} mr-1"></i>
                                                {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                            </a>
                                        @endcan

                                        @can('delete', \App\Models\Product\Product::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#productDeleteModal" data-id="{{ $product->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Delete
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-tags fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search || $filterType || $filterCategory
                                        ? 'No products or services match your filters.'
                                        : 'Nothing billable yet. Add the things the association charges for — stickers, IDs, clearances, fees.' }}
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
        @forelse ($products as $product)
            <div class="card mb-3 shadow-sm" wire:key="product-card-{{ $product->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Product\Product::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-product-{{ $product->id }}" value="{{ $product->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-product-{{ $product->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                    {{ $product->name }}
                                </div>
                                <div class="small text-muted text-truncate">
                                    {{ $product->category?->name ?: 'Uncategorised' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $product->is_active ? 'success' : 'secondary' }} flex-shrink-0">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    @if ($product->description)
                        <div class="small text-muted text-truncate mb-2">
                            {{ \Illuminate\Support\Str::limit($product->description, 60) }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">TYPE</div>
                            <span class="badge badge-{{ $product->type_enum?->badgeTheme() ?? 'secondary' }}">
                                {{ $product->type_enum?->label() ?? '—' }}
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">PRICE</div>
                            <div class="small font-weight-medium">{{ $product->price_with_unit }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobProductActions{{ $product->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                            Actions
                        </button>
                        <div class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="mobProductActions{{ $product->id }}">
                            @can('update', \App\Models\Product\Product::class)
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#productFormModal" data-id="{{ $product->id }}">
                                    <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                </a>
                                <a class="dropdown-item" href="#" wire:click.prevent="toggleActive({{ $product->id }})">
                                    <i class="fas fa-fw fa-{{ $product->is_active ? 'toggle-on' : 'toggle-off' }} mr-1"></i>
                                    {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                </a>
                            @endcan

                            @can('delete', \App\Models\Product\Product::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"
                                    data-toggle="modal" data-target="#productDeleteModal" data-id="{{ $product->id }}">
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
                    <i class="fas fa-fw fa-tags fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search || $filterType || $filterCategory
                            ? 'No products or services match your filters.'
                            : 'Nothing billable yet. Add the things the association charges for — stickers, IDs, clearances, fees.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($products->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $products->links() }}
        </div>
    @endif

</div>
