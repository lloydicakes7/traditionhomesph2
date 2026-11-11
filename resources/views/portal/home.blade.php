@extends('layouts.portal')

@section('title', 'Dashboard')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/reports.css') }}">
@endsection

@section('page-content')
    {{-- ================================================================== --}}
    {{-- MY FILE — the four things a homeowner files or holds                --}}
    {{-- ================================================================== --}}
    <div class="row mb-3">
        <div class="col-md-3 col-6 mb-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['requests'] ?? '—' }}</h3>
                    <p>Requests</p>
                </div>
                <div class="icon"><i class="fas fa-fw fa-clipboard-list"></i></div>
                <a href="{{ route('portal.requests') }}" class="small-box-footer">
                    View Requests <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['complaints'] ?? '—' }}</h3>
                    <p>Complaints</p>
                </div>
                <div class="icon"><i class="fas fa-fw fa-comment-dots"></i></div>
                <a href="{{ route('portal.complaints') }}" class="small-box-footer">
                    View Complaints <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['violations'] ?? '—' }}</h3>
                    <p>Violations</p>
                </div>
                <div class="icon"><i class="fas fa-fw fa-exclamation-triangle"></i></div>
                <a href="{{ route('portal.violations') }}" class="small-box-footer">
                    View Violations <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-md-3 col-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['bookings'] ?? '—' }}</h3>
                    <p>Bookings</p>
                </div>
                <div class="icon"><i class="far fa-fw fa-calendar-check"></i></div>
                <a href="{{ route('portal.bookings') }}" class="small-box-footer">
                    View Bookings <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">My Filings by Status</h3>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$charts['statuses']" cycle height="200px" />
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="far fa-fw fa-clipboard text-info mr-1"></i>
                        My Recent Requests
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.requests') }}" class="btn btn-xs btn-secondary">
                            View All
                        </a>
                    </div>
                </div>

                @if ($openRequests->isEmpty())
                    <div class="card-body text-center text-muted py-4">
                        <x-adminlte-empty-state icon="far fa-fw fa-file-alt"
                            title="No Requests Yet" />
                    </div>
                @else
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Request</th>
                                        <th>Details</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($openRequests as $request)
                                        <tr wire:key="dash-request-{{ $request->id }}">
                                            <td class="text-truncate" style="max-width:0;">
                                                {{ $request->type_label }}
                                                <div class="text-muted small text-truncate">
                                                    {{ $request->created_at ? $request->created_at->format('M j, Y') : '—' }}
                                                </div>
                                            </td>
                                            <td class="text-truncate" style="max-width:0;">
                                                {{ $request->summary }}
                                                @if ($request->remarks)
                                                    <div class="text-muted small text-truncate">{{ \Illuminate\Support\Str::limit($request->remarks, 60) }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge badge-{{ $request->status_badge_theme }}">
                                                    {{ $request->status_label }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-right py-2">
                            <a href="{{ route('portal.requests') }}" class="small">
                                See all {{ $totalRequests }} request{{ $totalRequests === 1 ? '' : 's' }}
                                <i class="fas fa-fw fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================== --}}
    {{-- MY YEAR AT A GLANCE — what I booked and what I paid, side by side   --}}
    {{-- ================================================================== --}}
    <div class="row mb-5">
        <div class="col-md-6 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="far fa-fw fa-calendar-check text-info mr-1"></i>
                        My Amenity Bookings ({{ $charts['year'] }})
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.bookings') }}" class="btn btn-xs btn-secondary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$charts['bookings']" height="200px" />
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-fw fa-file-invoice-dollar text-success mr-1"></i>
                        My Payments ({{ $charts['year'] }})
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.payments') }}" class="btn btn-xs btn-secondary">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <x-reports.bar-chart :data="$charts['payments']" theme="success" money height="200px" />
                </div>
            </div>
        </div>
    </div>
@endsection
