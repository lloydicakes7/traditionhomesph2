<div>
    {{-- =================================================================== --}}
    {{-- TOOLBAR                                                             --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-lg-5 mb-2 mb-lg-0">
                <x-adminlte-input name="search" placeholder="Search accounts..."
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-sm-auto ml-sm-auto mt-2 mt-sm-0">
                @can('export', \App\Models\Account\Account::class)
                    <x-adminlte-button label="Export CSV" theme="info" icon="fas fa-fw fa-file-export"
                        wire:click="export" wire:loading.attr="disabled"
                        data-thha-progress="Exporting CSV…" class="btn-sm btn-block" />
                @endcan
            </div>

            <div class="col-12 col-sm-auto mt-2 mt-sm-0">
                @can('create', \App\Models\Account\Account::class)
                    <x-adminlte-button label="Add Account" theme="primary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#accountFormModal" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- THE CHART                                                           --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="align-middle" style="width: 46%;">Account</th>
                        <th class="align-middle" style="width: 20%;">Kind</th>
                        <th class="align-middle text-right" style="width: 22%;">Amount</th>
                        <th class="text-center align-middle" style="width: 12%;">Actions</th>
                    </tr>
                </thead>

                @forelse ($groups as $group)
                    <tbody>
                        <tr class="table-light">
                            <td colspan="2" class="align-middle">
                                <span class="font-weight-bold">{{ $group['label'] }}</span>
                                <span class="badge badge-{{ $group['kind'] ? $group['kind']->badgeTheme() : 'secondary' }} ml-1">
                                    {{ count($group['rows']) }}
                                </span>
                                @if (! $group['kind'])
                                    <span class="small text-muted ml-2">Kept by the books</span>
                                @endif
                            </td>
                            <td class="text-right align-middle">
                                <div class="small text-muted">
                                    {{ ! $group['kind'] || $group['kind']->is(\App\Enums\AccountKind::MONEY()) ? 'Balance' : 'Total This Year' }}
                                </div>
                                <div class="font-weight-bold">{{ \App\Utility\MoneyHelper::format($group['total']) }}</div>
                            </td>
                            <td></td>
                        </tr>

                        @foreach ($group['rows'] as $row)
                            <tr>
                                <td class="align-middle">
                                    <div class="font-weight-medium">{{ $row['account']->name }}</div>
                                    <div class="small text-muted">{{ $row['account']->code }}</div>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $row['account']->kind() ? $row['account']->kind()->badgeTheme() : 'secondary' }}">
                                        {{ $row['account']->kind_label }}
                                    </span>
                                    @if ($row['account']->managedByLabel())
                                        <div class="small text-muted mt-1">
                                            Managed from {{ $row['account']->managedByLabel() }}
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle text-right font-weight-medium">
                                    {{ \App\Utility\MoneyHelper::format($row['amount']) }}
                                </td>
                                <td class="align-middle text-center">
                                    @if ($row['account']->isSystem())
                                        <span class="small text-muted">Read-only</span>
                                    @else
                                        <div class="dropdown">
                                            <button class="btn btn-xs btn-secondary dropdown-toggle" type="button"
                                                id="accountActions{{ $row['account']->id }}" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right"
                                                aria-labelledby="accountActions{{ $row['account']->id }}">
                                                @can('update', \App\Models\Account\Account::class)
                                                    <a class="dropdown-item" href="#"
                                                        data-toggle="modal" data-target="#accountFormModal" data-id="{{ $row['account']->id }}">
                                                        <i class="fas fa-fw fa-edit mr-1"></i> Edit Account
                                                    </a>
                                                @endcan
                                                @can('delete', \App\Models\Account\Account::class)
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        data-toggle="modal" data-target="#accountDeleteModal" data-id="{{ $row['account']->id }}">
                                                        <i class="fas fa-fw fa-trash mr-1"></i> Remove Account
                                                    </a>
                                                @endcan
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-balance-scale fa-2x mb-2"></i>
                                <p class="mb-0">
                                    {{ $search
                                        ? 'No accounts match that search.'
                                        : 'The chart is empty — add the income, expense and cash accounts the association keeps its books in.' }}
                                </p>
                            </td>
                        </tr>
                    </tbody>
                @endforelse

                @if (! empty($groups))
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="2" class="text-right font-weight-bold align-middle">Total Income This Year</td>
                            <td class="text-right font-weight-bold align-middle">
                                {{ \App\Utility\MoneyHelper::format($summary['income'] ?? 0) }}
                            </td>
                            <td></td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="2" class="text-right font-weight-bold align-middle">Total Expenses This Year</td>
                            <td class="text-right font-weight-bold align-middle">
                                {{ \App\Utility\MoneyHelper::format($summary['expense'] ?? 0) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- MOBILE CARDS — the chart, one account at a time                      --}}
    {{-- =================================================================== --}}
    <div class="d-block d-lg-none">
        @forelse ($groups as $group)
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center px-1 mb-2">
                    <span class="font-weight-bold small">
                        {{ $group['label'] }}
                        <span class="badge badge-{{ $group['kind'] ? $group['kind']->badgeTheme() : 'secondary' }} ml-1">{{ count($group['rows']) }}</span>
                    </span>
                    <span class="font-weight-bold small">{{ \App\Utility\MoneyHelper::format($group['total']) }}</span>
                </div>

                @foreach ($group['rows'] as $row)
                    <div class="card mb-2 shadow-sm">
                        <div class="card-body px-3 py-2">
                            <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                                <div class="min-width-0">
                                    <div class="font-weight-bold text-truncate" style="font-size:0.9rem;">
                                        {{ $row['account']->name }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $row['account']->code }} · {{ $row['account']->kind_label }}
                                        @if ($row['account']->managedByLabel())
                                            · Managed from {{ $row['account']->managedByLabel() }}
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <div class="font-weight-medium">{{ \App\Utility\MoneyHelper::format($row['amount']) }}</div>
                                    <div class="small text-muted">{{ $row['label'] }}</div>
                                </div>
                            </div>
                        </div>
                        @unless ($row['account']->isSystem())
                            <div class="card-footer bg-light py-2 text-right">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                        id="mobAccountActions{{ $row['account']->id }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="mobAccountActions{{ $row['account']->id }}">
                                        @can('update', \App\Models\Account\Account::class)
                                            <a class="dropdown-item" href="#"
                                                data-toggle="modal" data-target="#accountFormModal" data-id="{{ $row['account']->id }}">
                                                <i class="fas fa-fw fa-edit mr-1"></i> Edit Account
                                            </a>
                                        @endcan
                                        @can('delete', \App\Models\Account\Account::class)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#"
                                                data-toggle="modal" data-target="#accountDeleteModal" data-id="{{ $row['account']->id }}">
                                                <i class="fas fa-fw fa-trash mr-1"></i> Remove Account
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endunless
                    </div>
                @endforeach
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-balance-scale fa-2x mb-2"></i>
                    <p class="mb-0">
                        {{ $search
                            ? 'No accounts match that search.'
                            : 'The chart is empty — add the income, expense and cash accounts the association keeps its books in.' }}
                    </p>
                </div>
            </div>
        @endforelse

        @if (! empty($groups))
            <div class="card">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between">
                        <span class="font-weight-bold small">Total Income This Year</span>
                        <span class="font-weight-bold small">{{ \App\Utility\MoneyHelper::format($summary['income'] ?? 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <span class="font-weight-bold small">Total Expenses This Year</span>
                        <span class="font-weight-bold small">{{ \App\Utility\MoneyHelper::format($summary['expense'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
