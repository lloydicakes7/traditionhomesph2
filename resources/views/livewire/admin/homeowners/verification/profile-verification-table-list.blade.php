<div>
    {{-- ===== Search ===== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-sm-6 col-lg-4">
                <x-adminlte-input name="search" placeholder="{{ __('users.verification.search_placeholder') }}"
                    wire:model.debounce.500ms="search" igroup-size="sm" fgroup-class="mb-0">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-search"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>
    </x-adminlte-card>

    {{-- ===== Queue ===== --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('users.verification.account') }}</th>
                            <th>{{ __('users.verification.unit') }}</th>
                            <th>{{ __('users.verification.status') }}</th>
                            <th>{{ __('users.verification.submitted') }}</th>
                            <th class="text-right">{{ __('users.verification.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            <tr wire:key="verification-{{ $account->id }}">
                                <td>
                                    <span class="font-weight-bold d-block">{{ $account->name }}</span>
                                    <small class="text-muted">{{ $account->email }}</small>
                                </td>
                                <td>
                                    {{ $account->resident?->household?->property?->block_lot_label ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $account->profile_status->badgeTheme() }}">
                                        {{ $account->profile_status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $account->profile_submitted_at?->format('M d, Y g:i A') ?? '—' }}</small>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-outline-primary btn-xs"
                                        data-toggle="modal" data-target="#profileVerificationViewModal"
                                        data-id="{{ $account->id }}">
                                        <i class="fas fa-fw fa-user-check mr-1"></i>{{ __('users.verification.review') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-fw fa-user-check fa-2x mb-2"></i><br>
                                    {{ __('users.verification.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== Pagination ===== --}}
    @if ($accounts->hasPages())
        <div class="w-100 mt-3 mb-5">
            {{ $accounts->links() }}
        </div>
    @endif
</div>
