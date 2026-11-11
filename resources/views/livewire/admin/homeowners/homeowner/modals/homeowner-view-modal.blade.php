<div wire:ignore.self>
    <x-adminlte-modal id="homeownerViewModal"
        title="{{ $viewHomeowner->full_name ?? 'Homeowner Details' }}"
        icon="fas fa-fw fa-user" theme="info" size="xl" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading homeowner details…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
        @if ($viewHomeowner)
        <div class="row">

            {{-- =========================================================== --}}
            {{-- TABS: horizontal + swipeable below lg, left sidebar on lg+   --}}
            {{-- =========================================================== --}}
            <div class="col-lg-3 mb-3 mb-lg-0">
                <div class="bg-light rounded p-2">
                    <ul class="nav nav-pills flex-nowrap flex-lg-column overflow-auto" id="homeownerViewTabs" role="tablist">

                        <li class="nav-item flex-shrink-0">
                            <a class="nav-link text-nowrap d-flex align-items-center {{ $activeViewTab === 'details' ? 'active' : '' }}"
                               wire:click.prevent="switchViewTab('details')" href="#ho-details" role="tab">
                                <i class="fas fa-fw fa-info-circle mr-2"></i> Profile
                            </a>
                        </li>

                        <li class="nav-item flex-shrink-0">
                            <a class="nav-link text-nowrap d-flex align-items-center {{ $activeViewTab === 'household' ? 'active' : '' }}"
                               wire:click.prevent="switchViewTab('household')" href="#ho-household" role="tab">
                                <i class="fas fa-fw fa-users mr-2"></i> Household
                            </a>
                        </li>

                        @if ($canViewRequests)
                            <li class="nav-item flex-shrink-0">
                                <a class="nav-link text-nowrap d-flex align-items-center {{ $activeViewTab === 'requests' ? 'active' : '' }}"
                                   wire:click.prevent="switchViewTab('requests')" href="#ho-requests" role="tab">
                                    <i class="fas fa-fw fa-file-alt mr-2"></i> Request Documents
                                    @if ($viewRequests->isNotEmpty())
                                        <span class="badge badge-pill ml-auto pl-2 {{ $activeViewTab === 'requests' ? 'badge-light' : 'badge-secondary' }}">
                                            {{ $viewRequests->count() }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endif

                        @if ($canViewComplaints)
                            <li class="nav-item flex-shrink-0">
                                <a class="nav-link text-nowrap d-flex align-items-center {{ $activeViewTab === 'complaints' ? 'active' : '' }}"
                                   wire:click.prevent="switchViewTab('complaints')" href="#ho-complaints" role="tab">
                                    <i class="fas fa-fw fa-comment-dots mr-2"></i> Complaints
                                    @if ($viewComplaints->isNotEmpty())
                                        <span class="badge badge-pill ml-auto pl-2 {{ $activeViewTab === 'complaints' ? 'badge-light' : 'badge-secondary' }}">
                                            {{ $viewComplaints->count() }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endif

                        @if ($canViewInvoices)
                            <li class="nav-item flex-shrink-0">
                                <a class="nav-link text-nowrap d-flex align-items-center {{ $activeViewTab === 'invoices' ? 'active' : '' }}"
                                   wire:click.prevent="switchViewTab('invoices')" href="#ho-invoices" role="tab">
                                    <i class="fas fa-fw fa-file-invoice-dollar mr-2"></i> Invoices
                                    @if ($viewInvoices->isNotEmpty())
                                        <span class="badge badge-pill ml-auto pl-2 {{ $activeViewTab === 'invoices' ? 'badge-light' : 'badge-secondary' }}">
                                            {{ $viewInvoices->count() }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endif

                        @if ($canViewPayments)
                            <li class="nav-item flex-shrink-0">
                                <a class="nav-link text-nowrap d-flex align-items-center {{ $activeViewTab === 'payments' ? 'active' : '' }}"
                                   wire:click.prevent="switchViewTab('payments')" href="#ho-payments" role="tab">
                                    <i class="fas fa-fw fa-receipt mr-2"></i> Payments
                                    @if ($viewPayments->isNotEmpty())
                                        <span class="badge badge-pill ml-auto pl-2 {{ $activeViewTab === 'payments' ? 'badge-light' : 'badge-secondary' }}">
                                            {{ $viewPayments->count() }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endif

                        @if ($canViewViolations)
                            <li class="nav-item flex-shrink-0">
                                <a class="nav-link text-nowrap d-flex align-items-center {{ $activeViewTab === 'violations' ? 'active' : '' }}"
                                   wire:click.prevent="switchViewTab('violations')" href="#ho-violations" role="tab">
                                    <i class="fas fa-fw fa-exclamation-triangle mr-2"></i> Violations
                                    @if ($viewViolations && $viewViolations['active']->isNotEmpty())
                                        <span class="badge badge-pill badge-danger ml-auto pl-2">
                                            {{ $viewViolations['active']->count() }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endif

                    </ul>
                </div>
            </div>

            {{-- =========================================================== --}}
            {{-- CONTENT                                                      --}}
            {{-- =========================================================== --}}
            <div class="col-lg-9">
                <div class="tab-content">

                    {{-- Tab 1: Details — Personal + Account Info --}}
                    <div class="tab-pane fade {{ $activeViewTab === 'details' ? 'show active' : '' }}" id="ho-details" role="tabpanel">
                        <div class="text-center mb-4">
                            @if ($viewHomeowner->photo_url)
                                <img src="{{ $viewHomeowner->photo_url }}" class="rounded-circle shadow-sm"
                                    style="width:96px; height:96px; object-fit:cover; border:3px solid #fff;">
                            @else
                                <img src="{{ asset('image/user.png') }}" class="rounded-circle flex-shrink-0"
                                    style="width:96px; height:96px; object-fit:cover; border:3px solid #fff;">
                            @endif
                        </div>

                        <div class="row">
                            {{-- Left: Personal Information --}}
                            <div class="col-xl-6">
                                <h6 class="font-weight-bold mb-2"><i class="fas fa-fw fa-id-card mr-1"></i>Personal Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td class="text-muted" style="width:40%;">Full Name</td><td class="font-weight-medium">{{ $viewHomeowner->full_name }}</td></tr>
                                    <tr><td class="text-muted">Surname</td><td>{{ $viewHomeowner->surname ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">First Name</td><td>{{ $viewHomeowner->first_name ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Middle Name</td><td>{{ $viewHomeowner->middle_name ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Suffix</td><td>{{ $viewHomeowner->suffix ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Mobile No.</td><td>{{ $viewHomeowner->mobile_no ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Sex</td><td>{{ $viewHomeowner->sex_label }}</td></tr>
                                    <tr><td class="text-muted">Birth Date</td><td>{{ $viewHomeowner->birth_date?->format('M d, Y') ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Birth Place</td><td>{{ $viewHomeowner->birth_place ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Civil Status</td><td>{{ $viewHomeowner->civil_status_label }}</td></tr>
                                    <tr><td class="text-muted">Religion</td><td>{{ $viewHomeowner->religion ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Citizenship</td><td>{{ $viewHomeowner->citizenship ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Occupation</td><td>{{ $viewHomeowner->occupation ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Educational Attainment</td><td>{{ $viewHomeowner->educational_attainment_label }}</td></tr>
                                </table>
                            </div>

                            {{-- Right: Account Information --}}
                            <div class="col-xl-6">
                                <h6 class="font-weight-bold mb-2"><i class="fas fa-fw fa-user-circle mr-1"></i>Account Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td class="text-muted" style="width:40%;">Email</td><td>{{ $viewHomeowner->user?->email ?? '—' }}</td></tr>
                                    <tr><td class="text-muted">Account Status</td>
                                        <td>
                                            @if ($viewHomeowner->user && $viewHomeowner->user->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @elseif ($viewHomeowner->user && !$viewHomeowner->user->is_active)
                                                <span class="badge badge-warning">Inactive</span>
                                            @else
                                                <span class="badge badge-secondary">No Account</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr><td class="text-muted">Last Login</td><td><small>{{ $viewHomeowner->user && $viewHomeowner->user->last_login_at ? $viewHomeowner->user->last_login_at->format('M d, Y h:i A') : 'Never' }}</small></td></tr>
                                    <tr><td class="text-muted">Recorded Since</td><td><small>{{ $viewHomeowner->created_at ? $viewHomeowner->created_at->format('M d, Y') : '—' }}</small></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Tab 2: Household --}}
                    <div class="tab-pane fade {{ $activeViewTab === 'household' ? 'show active' : '' }}" id="ho-household" role="tabpanel">
                        @if ($viewHouseholdData)
                            <h6 class="font-weight-bold mb-2"><i class="fas fa-fw fa-home mr-1"></i>Household</h6>
                            <table class="table table-sm table-borderless">
                                <tr><td class="text-muted" style="width:30%;">Property</td><td>{{ $viewHouseholdData['property_label'] }} — {{ $viewHouseholdData['property_address'] }}</td></tr>
                                <tr><td class="text-muted">Property Type</td><td>{{ $viewHouseholdData['property_type_label'] }}</td></tr>
                                <tr><td class="text-muted">Type</td><td><span class="badge badge-primary">{{ $viewHouseholdData['type_label'] }}</span></td></tr>
                                @if (!empty($viewHouseholdData['prev_address']))
                                <tr><td class="text-muted">Previous Address</td><td>{{ $viewHouseholdData['prev_address'] }}</td></tr>
                                @endif
                            </table>

                            {{-- Household Members --}}
                            <h6 class="font-weight-bold mt-3 mb-2"><i class="fas fa-fw fa-users mr-1"></i>Members</h6>
                            @if (count($viewHouseholdData['members']) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead><tr><th>#</th><th>Name</th><th>Relationship</th><th>Age</th></tr></thead>
                                        <tbody>
                                            @foreach ($viewHouseholdData['members'] as $idx => $m)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>{{ $m['full_name'] }}</td>
                                                <td>{{ $m['relationship'] }}</td>
                                                <td>{{ $m['age'] ?? '—' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small">No household members.</p>
                            @endif
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-fw fa-home fa-2x mb-2"></i>
                                <p class="mb-0">This homeowner is not assigned to any property yet.</p>
                            </div>
                        @endif
                    </div>

                    {{-- =========================================================== --}}
                    {{-- Requests: the documents the homeowner asked the office for   --}}
                    {{-- =========================================================== --}}
                    @if ($canViewRequests)
                        <div class="tab-pane fade {{ $activeViewTab === 'requests' ? 'show active' : '' }}"
                            id="ho-requests" role="tabpanel">

                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-1">
                                    <i class="fas fa-fw fa-file-alt text-primary mr-1"></i>Request Documents
                                </h6>
                                <a href="{{ route('support.requests', ['search' => $viewHomeowner->full_name]) }}"
                                    class="btn btn-xs btn-secondary" target="_blank">
                                    <i class="fas fa-fw fa-external-link-alt mr-1"></i> View All
                                </a>
                            </div>

                            @if ($viewRequests->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Reference</th>
                                                <th>Document</th>
                                                <th class="d-none d-sm-table-cell">Purpose</th>
                                                <th>Filed</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($viewRequests as $request)
                                                <tr>
                                                    <td class="text-nowrap">{{ $request->reference ?: '—' }}</td>
                                                    <td>{{ $request->type_label }}</td>
                                                    <td class="d-none d-sm-table-cell text-muted small">{{ $request->purpose_display }}</td>
                                                    <td class="text-nowrap small">{{ $request->created_at?->format('M d, Y') }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $request->status_badge_theme }}">
                                                            {{ $request->status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0">No requests filed by this homeowner.</p>
                            @endif
                        </div>
                    @endif

                    {{-- =========================================================== --}}
                    {{-- Complaints: what the homeowner reported to the office        --}}
                    {{-- =========================================================== --}}
                    @if ($canViewComplaints)
                        <div class="tab-pane fade {{ $activeViewTab === 'complaints' ? 'show active' : '' }}"
                            id="ho-complaints" role="tabpanel">

                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-1">
                                    <i class="fas fa-fw fa-comment-dots text-primary mr-1"></i>Complaints
                                </h6>
                                <a href="{{ route('support.complaints', ['search' => $viewHomeowner->full_name]) }}"
                                    class="btn btn-xs btn-secondary" target="_blank">
                                    <i class="fas fa-fw fa-external-link-alt mr-1"></i> View All
                                </a>
                            </div>

                            @if ($viewComplaints->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th class="d-none d-sm-table-cell">Category</th>
                                                <th>Filed</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($viewComplaints as $complaint)
                                                <tr>
                                                    <td class="text-truncate" style="max-width:220px;">{{ $complaint->subject }}</td>
                                                    <td class="d-none d-sm-table-cell text-muted small">{{ $complaint->category_label }}</td>
                                                    <td class="text-nowrap small">{{ $complaint->created_at?->format('M d, Y') }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $complaint->status_badge_theme }}">
                                                            {{ $complaint->status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0">No complaints on record for this homeowner.</p>
                            @endif
                        </div>
                    @endif

                    {{-- =========================================================== --}}
                    {{-- Invoices: every bill raised against the account              --}}
                    {{-- =========================================================== --}}
                    @if ($canViewInvoices)
                        <div class="tab-pane fade {{ $activeViewTab === 'invoices' ? 'show active' : '' }}"
                            id="ho-invoices" role="tabpanel">

                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-1">
                                    <i class="fas fa-fw fa-file-invoice-dollar text-primary mr-1"></i>Invoices
                                </h6>
                                <a href="{{ route('collections.invoices', ['search' => $viewHomeowner->full_name]) }}"
                                    class="btn btn-xs btn-secondary" target="_blank">
                                    <i class="fas fa-fw fa-external-link-alt mr-1"></i> View All
                                </a>
                            </div>

                            @if ($viewInvoices->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Reference</th>
                                                <th class="d-none d-sm-table-cell">Type</th>
                                                <th class="text-right">Total</th>
                                                <th class="text-right">Balance</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($viewInvoices as $invoice)
                                                <tr>
                                                    <td class="text-truncate" style="max-width:160px;">
                                                        <div class="text-truncate">{{ $invoice->reference }}</div>
                                                        <div class="small text-muted text-nowrap">{{ $invoice->bill_date_display }}</div>
                                                    </td>
                                                    <td class="d-none d-sm-table-cell small">{{ $invoice->type_label }}</td>
                                                    <td class="text-right small text-nowrap">{{ $invoice->total_display }}</td>
                                                    <td class="text-right small text-nowrap">{{ $invoice->balance_display }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $invoice->status_badge }}">
                                                            {{ $invoice->status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0">No invoices raised against this account.</p>
                            @endif
                        </div>
                    @endif

                    {{-- =========================================================== --}}
                    {{-- Payments: what the office recorded against those invoices    --}}
                    {{-- =========================================================== --}}
                    @if ($canViewPayments)
                        <div class="tab-pane fade {{ $activeViewTab === 'payments' ? 'show active' : '' }}"
                            id="ho-payments" role="tabpanel">

                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold mb-1">
                                    <i class="fas fa-fw fa-receipt text-primary mr-1"></i>Payments
                                </h6>
                                <a href="{{ route('collections.payments', ['search' => $viewHomeowner->full_name]) }}"
                                    class="btn btn-xs btn-secondary" target="_blank">
                                    <i class="fas fa-fw fa-external-link-alt mr-1"></i> View All
                                </a>
                            </div>

                            @if ($viewPayments->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Receipt No.</th>
                                                <th class="d-none d-sm-table-cell">Method</th>
                                                <th>Paid</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($viewPayments as $payment)
                                                <tr>
                                                    <td class="text-nowrap">{{ $payment->receipt_no ?: $payment->reference_no ?: '—' }}</td>
                                                    <td class="d-none d-sm-table-cell small">{{ $payment->method_label }}</td>
                                                    <td class="text-nowrap small">{{ $payment->paid_at_display }}</td>
                                                    <td class="text-right small text-nowrap">{{ $payment->amount_display }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $payment->status_badge }}">
                                                            {{ $payment->status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0">No payments recorded for this account.</p>
                            @endif
                        </div>
                    @endif

                    {{-- Violations --}}
                    @if ($canViewViolations)
                        <div class="tab-pane fade {{ $activeViewTab === 'violations' ? 'show active' : '' }}"
                            id="ho-violations" role="tabpanel">

                            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                                <h6 class="font-weight-bold">
                                    <i class="fas fa-fw fa-exclamation-circle text-danger mr-1"></i>Active Violations
                                </h6>
                                <a href="{{ route('support.violations', ['search' => $viewHomeowner->full_name]) }}"
                                    class="btn btn-xs btn-secondary" target="_blank">
                                    <i class="fas fa-fw fa-external-link-alt mr-1"></i> View All
                                </a>
                            </div>

                            @if ($viewViolations && $viewViolations['active']->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Violation</th>
                                                <th>When</th>
                                                <th>Location</th>
                                                <th class="text-center">Acknowledged</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($viewViolations['active'] as $violation)
                                                <tr>
                                                    <td>{{ $violation->type_label }}</td>
                                                    <td class="text-nowrap">{{ $violation->occurred_display }}</td>
                                                    <td>{{ $violation->location }}</td>
                                                    <td class="text-center">
                                                        @if ($violation->is_acknowledged)
                                                            <i class="fas fa-fw fa-check-circle text-success"
                                                                title="Acknowledged {{ $violation->acknowledged_display }}"></i>
                                                        @else
                                                            <i class="far fa-fw fa-circle text-muted" title="Not yet acknowledged"></i>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-{{ $violation->status_badge_theme }}">
                                                            {{ $violation->status_label }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-4">No violations are standing against this homeowner.</p>
                            @endif

                            <h6 class="font-weight-bold mb-2 mt-4">
                                <i class="fas fa-fw fa-history text-secondary mr-1"></i>Violation History
                            </h6>

                            @if ($viewViolations && $viewViolations['history']->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Violation</th>
                                                <th>When</th>
                                                <th>Resolved</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($viewViolations['history'] as $violation)
                                                <tr>
                                                    <td>{{ $violation->type_label }}</td>
                                                    <td class="text-nowrap">{{ $violation->occurred_display }}</td>
                                                    <td class="text-nowrap">
                                                        {{ $violation->resolved_at ? $violation->resolved_at->format('M j, Y') : '—' }}
                                                    </td>
                                                    <td class="text-muted small">{{ $violation->resolution_notes ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted small mb-0">This homeowner has no resolved violations on record.</p>
                            @endif
                        </div>
                    @endif

                </div>
            </div>

        </div>
        @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
