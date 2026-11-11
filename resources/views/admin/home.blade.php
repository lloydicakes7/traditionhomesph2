@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-content')
    {{-- =================================================================== --}}
    {{-- HEADLINE FIGURES                                                     --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($summary['homeowners']) }}</h3>
                    <p>{{ __('Homeowners') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                @can('viewAny', \App\Models\User::class)
                    <a href="{{ route('homeowners.homeowners') }}" class="small-box-footer">
                        {{ __('More info') }} <i class="fas fa-arrow-circle-right"></i>
                    </a>
                @endcan
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($summary['paid_bills']) }}</h3>
                    <p>{{ __('Paid Bills') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                @can('viewAnyInvoices', \App\Models\Bill\Bill::class)
                    <a href="{{ route('collections.payments') }}" class="small-box-footer">
                        {{ __('More info') }} <i class="fas fa-arrow-circle-right"></i>
                    </a>
                @endcan
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($summary['open_requests']) }}</h3>
                    <p>{{ __('Open Requests') }}</p>
                </div>
                <div class="icon">
                    <i class="far fa-clipboard"></i>
                </div>
                @can('viewAny', \App\Models\Request::class)
                    <a href="{{ route('support.requests', ['filterStatus' => 'pending']) }}" class="small-box-footer">
                        {{ __('More info') }} <i class="fas fa-arrow-circle-right"></i>
                    </a>
                @endcan
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($summary['active_violations']) }}</h3>
                    <p>{{ __('Violations') }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                @can('viewAny', \App\Models\Violation::class)
                    <a href="{{ route('support.violations', ['filterStatus' => \App\Enums\ViolationStatus::ACTIVE]) }}"
                        class="small-box-footer">
                        {{ $summary['active_violations'] > 0 ? __('Unresolved — more info') : __('More info') }}
                        <i class="fas fa-arrow-circle-right"></i>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- THE YEAR IN CHARTS                                                  --}}
    {{-- =================================================================== --}}
    <div class="row mb-3">
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="card h-100 shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title">Homeowners by Sex</h3>
                </div>
                <div class="card-body">
                    <x-reports.pie-chart :data="$charts['gender']"
                        :center="number_format($charts['residents'])"
                        center-label="Residents" />
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100 shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title">Profit and Loss ({{ $charts['year'] }})</h3>
                </div>
                <div class="card-body">
                    <x-reports.line-chart money :series="[
                        ['label' => 'Income', 'theme' => 'success', 'data' => $charts['income']],
                        ['label' => 'Expenses', 'theme' => 'danger', 'data' => $charts['expenses']],
                    ]" />
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title">Monthly Income ({{ $charts['year'] }})</h3>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$charts['income']" theme="success" money height="200px" />
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm mb-3">
                <div class="card-header">
                    <h3 class="card-title">Monthly Expenses ({{ $charts['year'] }})</h3>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$charts['expenses']" money height="200px" />
                </div>
            </div>
        </div>
    </div>

    {{-- =================================================================== --}}
    {{-- WHAT THE OFFICE HAS TO FOLLOW UP                                     --}}
    {{-- =================================================================== --}}
    <div class="row">
        <div class="col-12 col-lg-6 d-flex">
            <div class="card shadow-sm w-100 mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-fw fa-exclamation-triangle text-danger mr-1"></i>
                        Unresolved Violations
                    </h3>
                    @can('viewAny', \App\Models\Violation::class)
                        <div class="card-tools">
                            <a href="{{ route('support.violations') }}" class="btn btn-xs btn-secondary">
                                View All
                            </a>
                        </div>
                    @endcan
                </div>
                @if ($violations->isEmpty())
                    <div class="card-body text-center text-muted py-4 dash-fixed-list">
                        <i class="fas fa-fw fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">No violations are standing against any homeowner.</p>
                    </div>
                @else
                    <div class="card-body p-0 dash-fixed-list">
                        <div class="table-responsive h-100">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    @foreach ($violations as $violation)
                                        <tr>
                                            <td class="dash-truncate">
                                                {{ $violation->type_label }}
                                                <div class="text-muted small dash-truncate">
                                                    {{ $violation->resident_label }}
                                                    · {{ $violation->occurred_display }}
                                                </div>
                                            </td>
                                            <td class="text-center align-middle text-nowrap dash-cell-fixed">
                                                @if ($violation->is_acknowledged)
                                                    <i class="fas fa-fw fa-check-circle text-success"
                                                        title="Acknowledged {{ $violation->acknowledged_display }}"></i>
                                                @else
                                                    <i class="far fa-fw fa-circle text-muted" title="Not yet acknowledged"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-6 d-flex">
            <div class="card shadow-sm w-100 mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="far fa-fw fa-clipboard text-warning mr-1"></i>
                        Requests Awaiting Action
                    </h3>
                    @can('viewAny', \App\Models\Request::class)
                        <div class="card-tools">
                            <a href="{{ route('support.requests') }}" class="btn btn-xs btn-secondary">
                                View All
                            </a>
                        </div>
                    @endcan
                </div>
                @if ($requests->isEmpty())
                    <div class="card-body text-center text-muted py-4 dash-fixed-list">
                        <i class="fas fa-fw fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">No requests are waiting on the office.</p>
                    </div>
                @else
                    <div class="card-body p-0 dash-fixed-list">
                        <div class="table-responsive h-100">
                            <table class="table table-hover mb-0">
                                <tbody>
                                    @foreach ($requests as $request)
                                        <tr>
                                            <td class="dash-truncate">
                                                {{ $request->type_label }}
                                                <div class="text-muted small dash-truncate">
                                                    {{ $request->resident_label }}
                                                    · {{ $request->created_at ? $request->created_at->format('M j, Y') : '—' }}
                                                </div>
                                            </td>
                                            <td class="text-center align-middle text-nowrap dash-cell-fixed">
                                                <span class="badge badge-{{ $request->status_badge_theme }}">
                                                    {{ $request->status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
