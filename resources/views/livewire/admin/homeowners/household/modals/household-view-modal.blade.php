<div wire:ignore.self>
    <x-adminlte-modal id="householdViewModal"
        title="Household Details"
        icon="fas fa-fw fa-users" theme="info" size="lg" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading household details…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            @if ($viewData)
                {{-- ===== Summary ===== --}}
                <div class="rounded border mb-3 p-3" style="background:#fbf7ec;">
                    <div class="d-flex align-items-center">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mr-3 flex-shrink-0"
                            style="width:44px; height:44px;">
                            <i class="fas fa-fw fa-house-user"></i>
                        </span>
                        <div class="min-width-0">
                            <h5 class="mb-0 font-weight-bold text-truncate">{{ $viewData['name'] }}</h5>
                            <small class="text-muted">
                                {{ $viewData['families_count'] }} {{ \Illuminate\Support\Str::plural('family', $viewData['families_count']) }}
                            </small>
                        </div>
                        <span class="badge badge-{{ $viewData['type_badge'] }} ml-auto">
                            {{ $viewData['type'] }}
                        </span>
                    </div>
                </div>

                {{-- ===== Facts ===== --}}
                <div class="row small mb-2">
                    <div class="col-12 col-md-4 mb-2">
                        <div class="text-muted text-uppercase font-weight-bold" style="font-size:.65rem;">Homeowner</div>
                        <div class="font-weight-medium">{{ $viewData['homeowner'] }}</div>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <div class="text-muted text-uppercase font-weight-bold" style="font-size:.65rem;">Tenure</div>
                        <div>{{ $viewData['tenure'] ?: '—' }}</div>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <div class="text-muted text-uppercase font-weight-bold" style="font-size:.65rem;">Created</div>
                        <div>{{ $viewData['created_at'] ?: '—' }}</div>
                    </div>
                </div>

                <div class="small border-top pt-2 mb-2">
                    <div class="text-muted text-uppercase font-weight-bold" style="font-size:.65rem;">Property</div>
                    <div>{{ $viewData['property'] }}@if ($viewData['property_address'] !== '—') — {{ $viewData['property_address'] }}@endif</div>
                </div>

                <div class="small border-top pt-2 mb-5">
                    <div class="text-muted text-uppercase font-weight-bold" style="font-size:.65rem;">Previous Address</div>
                    <div>{{ $viewData['prev_address'] ?: 'Not Provided' }}</div>
                </div>

                {{-- ===== Members ===== --}}
                <h6 class="font-weight-bold mb-2">
                    <i class="fas fa-fw fa-users mr-1"></i>Family Members ({{ count($viewData['members']) }})
                </h6>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:2.5rem;">#</th>
                                <th>Name</th>
                                <th>Relationship</th>
                                <th class="text-right">Age</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($viewData['members'] as $idx => $m)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td class="font-weight-medium">{{ $m['full_name'] }}</td>
                                    <td>
                                        {{ $m['relationship'] }}
                                        @if ($m['is_head'])
                                            <span class="badge badge-success ml-1">Head</span>
                                        @endif
                                    </td>
                                    <td class="text-right">{{ $m['age'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">No household members on record.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Close" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
        </x-slot>
    </x-adminlte-modal>
</div>
