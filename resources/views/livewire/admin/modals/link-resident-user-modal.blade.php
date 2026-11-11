<div wire:ignore.self>
    <x-adminlte-modal id="linkResidentUserModal" wire:key="link-resident-user-modal"
        title="{{ $mode === 'to-user' ? 'Link Resident to User Account' : 'Link User Account to Resident' }}"
        icon="fas fa-fw fa-link" theme="success" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading link options…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <p class="text-muted small mb-3">
                @if ($mode === 'to-user')
                    Select an existing resident to link to this user account. Only residents not already linked to another user are shown.
                @else
                    Select an existing user account to link to this resident. Only users not already linked to another resident are shown.
                @endif
            </p>

            <form>
                <div class="{{ $candidates->isNotEmpty() ? '' : 'd-none' }}">
                    <x-adminlte-select2 id="linkResidentUserId" name="selectedId"
                        :label="$mode === 'to-user' ? 'Resident' : 'User Account'"
                        wire:model.defer="selectedId" igroup-size="sm"
                        :config="['placeholder' => $mode === 'to-user' ? '— Select Resident —' : '— Select User —', 'width' => '100%']">
                        <option value="">— Select {{ $mode === 'to-user' ? 'Resident' : 'User' }} —</option>
                        @foreach ($candidates as $candidate)
                            <option value="{{ $candidate->id }}">
                                {{ $mode === 'to-user' ? $candidate->full_name : $candidate->email }}
                            </option>
                        @endforeach
                    </x-adminlte-select2>
                </div>

                @if ($candidates->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-fw fa-info-circle"></i>
                        No unlinked {{ $mode === 'to-user' ? 'residents' : 'user accounts' }} available.
                    </div>
                @endif
            </form>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            @if ($candidates->isNotEmpty())
                <x-adminlte-button label="Link Account" theme="success" icon="fas fa-fw fa-link"
                    wire:click="confirm" wire:loading.attr="disabled" class="btn-sm" />
                <span wire:loading wire:target="confirm" class="ml-2 text-success small">
                    <i class="fas fa-spinner fa-spin"></i> Linking...
                </span>
            @endif
        </x-slot>
    </x-adminlte-modal>
</div>
