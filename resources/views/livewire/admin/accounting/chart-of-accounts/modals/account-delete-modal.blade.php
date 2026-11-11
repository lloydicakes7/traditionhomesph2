<div wire:ignore.self>
    <x-adminlte-modal id="accountDeleteModal"
        title="Remove Account" icon="fas fa-fw fa-exclamation-triangle" theme="danger"
        size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading account…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p>Remove <strong>{{ $deletingAccountLabel }}</strong> from the chart?</p>

            <p class="small text-muted">
                An account can be removed only while nothing has been posted to it and no category,
                product, payment method or transfer points at it. Once the books name it, it stays.
            </p>

            @if ($deleteBlocker)
                <div class="alert alert-danger py-2 px-3 small mb-2">
                    <i class="fas fa-fw fa-ban mr-1"></i> {{ $deleteBlocker }}
                </div>
            @endif

            @if (! empty($deleteSummary))
                <p class="small text-muted mb-1">Records that name this account:</p>
                <ul class="list-unstyled small mb-0">
                    @foreach ($deleteSummary as $row)
                        <li class="d-flex justify-content-between align-items-start border-top py-1">
                            <span>
                                {{ $row['label'] }}
                                <span class="d-block text-muted">{{ $row['note'] }}</span>
                            </span>
                            <span class="badge badge-secondary ml-2">{{ $row['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Remove Account" theme="danger" icon="fas fa-fw fa-trash"
                wire:click="delete" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded || $deleteBlocker !== null" />
            <span wire:loading wire:target="delete" class="ml-2 text-danger small">
                <i class="fas fa-spinner fa-spin"></i> Removing...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
