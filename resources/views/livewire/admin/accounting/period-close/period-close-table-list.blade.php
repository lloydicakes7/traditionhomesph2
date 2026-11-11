<div>
    {{-- =================================================================== --}}
    {{-- WHAT THE LOCK MEANS                                                  --}}
    {{-- =================================================================== --}}
    <div class="alert alert-info py-2 px-3 small">
        <i class="fas fa-fw fa-lock text-dark mr-1"></i>
        Closing a month stops every module from posting into it — no charge, receipt, payout or transfer
        dated inside it — so the figures a report reads for that month stay still. Reopen it to correct something.
    </div>

    {{-- =================================================================== --}}
    {{-- YEAR + LEGEND                                                        --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-end">
            <div class="col-12 col-sm-4 col-lg-3">
                <x-adminlte-select name="filterYear" label="Year" wire:model="filterYear"
                    igroup-size="sm" fgroup-class="mb-0">
                    @foreach ($years as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-12 col-sm-8 col-lg-9 text-sm-right mt-2 mt-sm-0">
                <span class="badge badge-success">Open</span>
                <span class="badge badge-dark">Closed</span>
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- DESKTOP TABLE                                                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th class="align-middle" style="width:22%;">Month</th>
                        <th class="align-middle text-center" style="width:12%;">Status</th>
                        <th class="align-middle text-center" style="width:12%;">Entries</th>
                        <th class="align-middle" style="width:30%;">Closed / Reopened</th>
                        <th class="text-center align-middle" style="width:24%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($months as $month)
                        <tr>
                            <td class="align-middle font-weight-medium">{{ $month['label'] }}</td>
                            <td class="align-middle text-center">
                                <span class="badge badge-{{ $month['status']->badgeTheme() }}">
                                    {{ $month['status']->label() }}
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="small">{{ $month['count'] }}</span>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                @if ($month['record'] && $month['record']->closed_at)
                                    <div class="small text-truncate">
                                        Closed {{ $month['record']->closed_at->format('M j, Y') }}
                                        @if ($month['record']->closedByAdmin)
                                            by {{ $month['record']->closedByAdmin->name }}
                                        @endif
                                    </div>
                                @else
                                    <div class="small text-muted">Never closed</div>
                                @endif
                                @if ($month['record'] && $month['record']->reopened_at)
                                    <div class="small text-warning text-truncate">
                                        Reopened {{ $month['record']->reopened_at->format('M j, Y') }}
                                        @if ($month['record']->reopenedByAdmin)
                                            by {{ $month['record']->reopenedByAdmin->name }}
                                        @endif
                                    </div>
                                @endif
                                @if ($month['record'] && $month['record']->notes)
                                    <div class="small text-muted text-truncate">{{ $month['record']->notes }}</div>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                @can('transaction.viewAny')
                                    <a class="btn btn-xs btn-outline-secondary"
                                        href="{{ route('accounting.transactions', ['filterMonth' => $month['period']]) }}"
                                        title="Read the month's movements">
                                        <i class="fas fa-fw fa-list-ol"></i> Movements
                                    </a>
                                @endcan

                                @if ($month['status']->isClosed())
                                    @can('reopen', \App\Models\Account\AccountingPeriod::class)
                                        <x-adminlte-button label="Reopen" theme="outline-warning" icon="fas fa-fw fa-lock-open"
                                            data-toggle="modal" data-target="#periodReopenModal" data-id="{{ $month['period'] }}"
                                            class="btn-xs ml-1" />
                                    @endcan
                                @else
                                    @can('close', \App\Models\Account\AccountingPeriod::class)
                                        <x-adminlte-button label="Close Month" theme="dark" icon="fas fa-fw fa-lock"
                                            data-toggle="modal" data-target="#periodCloseModal" data-id="{{ $month['period'] }}"
                                            class="btn-xs ml-1" />
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-lock fa-2x mb-2"></i>
                                <p class="mb-0">No months to show for that year yet.</p>
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
        @forelse ($months as $month)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div>
                            <div class="font-weight-bold">{{ $month['label'] }}</div>
                            <div class="small text-muted">{{ $month['count'] }} entries</div>
                        </div>
                        <span class="badge badge-{{ $month['status']->badgeTheme() }} flex-shrink-0">
                            {{ $month['status']->label() }}
                        </span>
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    @if ($month['record'] && $month['record']->closed_at)
                        <div class="small">
                            Closed {{ $month['record']->closed_at->format('M j, Y') }}
                            @if ($month['record']->closedByAdmin)
                                by {{ $month['record']->closedByAdmin->name }}
                            @endif
                        </div>
                    @else
                        <div class="small text-muted">Never closed</div>
                    @endif
                    @if ($month['record'] && $month['record']->reopened_at)
                        <div class="small text-warning">
                            Reopened {{ $month['record']->reopened_at->format('M j, Y') }}
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light py-2 text-right">
                    @can('transaction.viewAny')
                        <a class="btn btn-sm btn-outline-secondary"
                            href="{{ route('accounting.transactions', ['filterMonth' => $month['period']]) }}">
                            <i class="fas fa-fw fa-list-ol mr-1"></i> Movements
                        </a>
                    @endcan

                    @if ($month['status']->isClosed())
                        @can('reopen', \App\Models\Account\AccountingPeriod::class)
                            <x-adminlte-button label="Reopen" theme="outline-warning" icon="fas fa-fw fa-lock-open"
                                data-toggle="modal" data-target="#periodReopenModal" data-id="{{ $month['period'] }}"
                                class="btn-sm" />
                        @endcan
                    @else
                        @can('close', \App\Models\Account\AccountingPeriod::class)
                            <x-adminlte-button label="Close Month" theme="dark" icon="fas fa-fw fa-lock"
                                data-toggle="modal" data-target="#periodCloseModal" data-id="{{ $month['period'] }}"
                                class="btn-sm" />
                        @endcan
                    @endif
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-lock fa-2x mb-2"></i>
                    <p class="mb-0">No months to show for that year yet.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
