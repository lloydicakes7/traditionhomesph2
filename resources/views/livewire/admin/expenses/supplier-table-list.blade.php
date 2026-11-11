{{-- resources/views/livewire/admin/expenses/supplier-table-list.blade.php --}}
<div>
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-6 col-lg-4 mb-2 mb-sm-0">
                <x-adminlte-input name="search" placeholder="Search suppliers"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-sm-auto ml-auto">
                @can('create', \App\Models\Supplier::class)
                    <x-adminlte-button label="Add Supplier" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#supplierFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    @if (!empty($selectedIds))
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="text-muted small font-weight-bold">
                <i class="fas fa-fw fa-check-square"></i> {{ count($selectedIds) }} selected
            </span>
            @can('delete', \App\Models\Supplier::class)
                <x-adminlte-button label="Delete Selected" theme="danger" icon="fas fa-fw fa-trash"
                    data-toggle="modal" data-target="#supplierMassDeleteModal"
                    data-id="{{ implode(',', $selectedIds) }}" class="btn-sm" />
            @endcan
        </div>
    @endif

    {{-- DESKTOP TABLE --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        @can('delete', \App\Models\Supplier::class)
                            <th class="text-center align-middle" style="width: 3%;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="selectAll" wire:model="selectAll">
                                    <label class="custom-control-label" for="selectAll"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="align-middle" style="width: 25%;">Supplier</th>
                        <th class="align-middle" style="width: 35%;">Description</th>
                        <th class="text-center align-middle" style="width: 12%;">Expenses</th>
                        <th class="text-center align-middle" style="width: 12%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr wire:key="supplier-row-{{ $supplier->id }}">
                            @can('delete', \App\Models\Supplier::class)
                                <td class="text-center align-middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="supplier-{{ $supplier->id }}" value="{{ $supplier->id }}"
                                            wire:model="selectedIds">
                                        <label class="custom-control-label" for="supplier-{{ $supplier->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="align-middle font-weight-medium">{{ $supplier->name }}</td>
                            <td class="align-middle text-truncate small text-muted" style="max-width: 0;">
                                {{ $supplier->description ?: 'None' }}
                            </td>
                            <td class="align-middle text-center">{{ $supplier->expenses_count }}</td>
                            <td class="align-middle text-center">
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                        id="supplierActions{{ $supplier->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">Actions</button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="supplierActions{{ $supplier->id }}">
                                        @can('update', \App\Models\Supplier::class)
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#supplierFormModal" data-id="{{ $supplier->id }}">
                                                <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                            </a>
                                        @endcan
                                        @can('delete', \App\Models\Supplier::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#supplierDeleteModal" data-id="{{ $supplier->id }}">
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
                                <i class="fas fa-fw fa-truck fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search ? 'No suppliers match your search.' : 'No suppliers yet. Add one to use when logging an expense.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MOBILE CARDS --}}
    <div class="d-block d-lg-none">
        @forelse ($suppliers as $supplier)
            <div class="card mb-3 shadow-sm" wire:key="supplier-card-{{ $supplier->id }}">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="d-flex align-items-start" style="gap:0.6rem; min-width:0;">
                            @can('delete', \App\Models\Supplier::class)
                                <div class="custom-control custom-checkbox flex-shrink-0 mt-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="mob-supplier-{{ $supplier->id }}" value="{{ $supplier->id }}"
                                        wire:model="selectedIds">
                                    <label class="custom-control-label" for="mob-supplier-{{ $supplier->id }}"></label>
                                </div>
                            @endcan
                            <div class="min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">{{ $supplier->name }}</div>
                                <div class="small text-muted">{{ $supplier->expenses_count }} expenses</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-muted">{{ $supplier->description ?: 'No description.' }}</div>
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                            id="mobSupplierActions{{ $supplier->id }}" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">Actions</button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="mobSupplierActions{{ $supplier->id }}">
                            @can('update', \App\Models\Supplier::class)
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#supplierFormModal" data-id="{{ $supplier->id }}">
                                    <i class="fas fa-fw fa-edit mr-1"></i> Edit
                                </a>
                            @endcan
                            @can('delete', \App\Models\Supplier::class)
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#supplierDeleteModal" data-id="{{ $supplier->id }}">
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
                    <i class="fas fa-fw fa-truck fa-2x mb-2"></i>
                    <p class="mb-0">{{ $search ? 'No suppliers match your search.' : 'No suppliers yet. Add one to use when logging an expense.' }}</p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($suppliers->hasPages())
        <div class="w-100 mt-3 mb-5">{{ $suppliers->links() }}</div>
    @endif
</div>
