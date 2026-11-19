<div>
    {{-- ===== Header ===== --}}
    <x-adminlte-card>
        <div class="row align-items-center">
            <div class="col-12 col-md-8 mb-2 mb-md-0">
                <h5 class="mb-0 font-weight-bold">{{ __('households.link.heading') }}</h5>
                <small class="text-muted">
                    {{ __('households.link.hint') }}
                </small>
            </div>
        </div>
    </x-adminlte-card>

    {{-- ===== Requests ===== --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('households.link.resident') }}</th>
                            <th scope="col">{{ __('households.link.relationship') }}</th>
                            <th scope="col">{{ __('households.link.requested') }}</th>
                            <th scope="col" class="text-right">{{ __('households.link.answer') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $link)
                            <tr wire:key="household-link-{{ $link->id }}">
                                <td class="align-middle">
                                    <span class="font-weight-bold d-block">{{ $link->resident?->full_name ?? 'No name on record' }}</span>
                                </td>
                                <td class="align-middle">
                                    {{ $link->relationship_label }}
                                    @if ($link->wantsHead())
                                        <span class="badge badge-warning ml-1">{{ __('households.link.head_request') }}</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <small>{{ $link->created_at?->format('M d, Y') ?? 'Date not recorded' }}</small>
                                </td>
                                <td class="text-right align-middle text-nowrap">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-success btn-xs" wire:click="approve({{ $link->id }})"
                                        wire:loading.attr="disabled" wire:target="approve">
                                        <i class="fas fa-fw fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-xs" wire:click="reject({{ $link->id }})"
                                            data-thha-confirm="{{ __('households.link.reject_confirm') }}"
                                            wire:loading.attr="disabled" wire:target="reject">
                                            <i class="fas fa-fw fa-times"></i>
                                        </button>
                                    </div>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-fw fa-users fa-2x mb-2"></i>
                                    <p class="mb-0">{{ __('households.link.empty') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
