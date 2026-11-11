<div>
    {{-- =================================================================== --}}
    {{-- PAGE ACTIONS: the doors out of the generator, inside their own card  --}}
    {{-- like every other collections page                                    --}}
    {{-- =================================================================== --}}
    <x-adminlte-card class="mb-3">
        <div class="row justify-content-end">
            <div class="col-12 col-sm-auto mb-1 mb-sm-0">
                @can('viewAnyMonthlyDues', \App\Models\Bill\Bill::class)
                    <a href="{{ route('collections.monthly-dues-invoices') }}" class="btn btn-primary btn-sm btn-block mb-1">
                        <i class="fas fa-fw fa-list"></i> Monthly Dues Invoices
                    </a>
                @endcan
            </div>
            <div class="col-12 col-sm-auto">
                @can('viewAny', \App\Models\Setting::class)
                    <a href="{{ route('settings.collections') }}" class="btn btn-secondary btn-sm btn-block mb-1 mr-1">
                        <i class="fas fa-fw fa-cog"></i> Settings
                    </a>
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- CURRENT RATE CONFIGURATION                                           --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box title="{{ $method->label() }}" text="Billing Method"
                icon="fas fa-fw fa-calculator" theme="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box title="{{ $mode->label() }}" text="Generation Mode"
                icon="fas fa-fw fa-cogs" theme="info" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box title="{{ $penalty }}" text="Overdue Penalty"
                icon="fas fa-fw fa-exclamation-triangle" theme="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box title="{{ $grace_days }} {{ $grace_days === 1 ? 'day' : 'days' }}"
                text="Grace Period" icon="fas fa-fw fa-hourglass-half" theme="success" />
        </div>

        @foreach ($rate_cards as $card)
            <div class="col-6 col-md-4 col-xl">
                <x-adminlte-small-box title="{{ $card['rate'] }}" text="{{ $card['label'] }}"
                    icon="fas fa-fw fa-tag" theme="secondary" />
            </div>
        @endforeach
    </div>

    {{-- =================================================================== --}}
    {{-- GENERATE                                                             --}}
    {{-- =================================================================== --}}
    <x-adminlte-card>
        <div class="row align-items-center gap-2">
            <div class="col-12 col-md-5">
                <x-adminlte-select name="month" label="Billing Month" wire:model="month" igroup-size="sm">
                    @foreach ($month_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
                <p class="mb-0">
                    <span class="text-muted">Total Expected:</span>
                    <span class="font-weight-bold">{{ $total_display }}</span>
                </p>
                <p class="text-muted small mb-0">
                    {{ $property_count }} {{ $property_count === 1 ? 'property' : 'properties' }} with a household
                </p>
                <p class="text-muted small mb-0">
                    {{ $generated_count }} already billed for {{ $month_display }}
                </p>
                <p class="text-muted small mb-0">
                    {{ $pending_count }} to bill
                </p>
            </div>
            <div class="col-12 col-md-auto ml-auto">
                @can('generateMonthlyDues', \App\Models\Bill\Bill::class)
                    <x-adminlte-button label="Generate Invoices" theme="primary" icon="fas fa-fw fa-file-invoice"
                        wire:click="generate" wire:loading.attr="disabled"
                        data-thha-progress="Generating the month's invoices…" class="btn-sm btn-block" />
                @endcan
                @can('applyAdvancePayment', \App\Models\Bill\Bill::class)
                    <x-adminlte-button label="Apply Advance Payment" theme="success" icon="fas fa-fw fa-hand-holding-usd"
                        wire:click="applyAdvancePayment" wire:loading.attr="disabled"
                        data-thha-progress="Applying advance payments to the month…" class="btn-sm btn-block" />
                @endcan
            </div>
        </div>
    </x-adminlte-card>

    {{-- =================================================================== --}}
    {{-- WHAT THE MONTH COMES TO, PROPERTY BY PROPERTY                        --}}
    {{-- =================================================================== --}}
    <div class="card d-none d-lg-block">
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-hover mb-0" style="table-layout:fixed; width:100%;">
                <thead>
                    <tr>
                        <th style="width:28%;">Property / Phase</th>
                        <th style="width:22%;">Owner</th>
                        <th style="width:18%;">Type / SQM</th>
                        <th class="text-right" style="width:14%;">Rate{{ $method->isPerSquareMeter() ? '/SQM' : '' }}</th>
                        <th class="text-right" style="width:18%;">Monthly Due</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <div class="font-weight-medium text-truncate">Blk {{ $row['block'] }} Lot {{ $row['lot'] }}</div>
                                <div class="text-muted small text-truncate">{{ $row['location'] }}</div>
                                @if ($row['generated'])
                                    <span class="badge badge-success mt-1">Billed</span>
                                @else
                                    <span class="badge badge-warning mt-1">To bill</span>
                                @endif
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <span class="small">{{ $row['owner'] }}</span>
                            </td>
                            <td class="align-middle text-truncate" style="max-width:0;">
                                <span class="small">{{ $row['type'] }}</span><br>
                                <span class="text-muted small">{{ $row['sqm'] }} sqm</span>
                            </td>
                            <td class="align-middle text-right"><span class="small">{{ $row['rate'] }}</span></td>
                            <td class="align-middle text-right font-weight-medium">{{ $row['amount_display'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No properties with a household on file yet — there is nothing to bill.
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
        <x-adminlte-card>
            <h5><i class="fas fa-fw fa-list-ol mr-1"></i> Properties to Bill ({{ $property_count }})</h5>
        </x-adminlte-card>

        @forelse ($rows as $row)
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-white px-3 py-2">
                    <div class="d-flex align-items-start justify-content-between" style="gap:0.5rem;">
                        <div class="min-width-0">
                            <div class="font-weight-bold text-truncate" style="font-size:0.95rem;">
                                Blk {{ $row['block'] }} Lot {{ $row['lot'] }}
                            </div>
                            <div class="small text-muted text-truncate">{{ $row['location'] }}</div>
                        </div>
                        @if ($row['generated'])
                            <span class="badge badge-success flex-shrink-0">Billed</span>
                        @else
                            <span class="badge badge-warning flex-shrink-0">To bill</span>
                        @endif
                    </div>
                </div>
                <div class="card-body px-3 py-3">
                    <div class="small text-truncate">{{ $row['owner'] }}</div>
                    <div class="text-muted small text-truncate mb-2">
                        {{ $row['type'] }} · {{ $row['sqm'] }} sqm
                    </div>

                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:0.5rem;">
                        <div>
                            <div class="text-muted small" style="font-size:0.7rem;">
                                RATE{{ $method->isPerSquareMeter() ? '/SQM' : '' }}
                            </div>
                            <div class="small">{{ $row['rate'] }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-muted small" style="font-size:0.7rem;">MONTHLY DUE</div>
                            <div class="small font-weight-medium">{{ $row['amount_display'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    No properties with a household on file yet — there is nothing to bill.
                </div>
            </div>
        @endforelse
    </div>

    @if ($rows->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $rows->links() }}
        </div>
    @endif
</div>
