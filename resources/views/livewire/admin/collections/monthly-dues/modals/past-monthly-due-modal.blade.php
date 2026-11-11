<div wire:ignore.self>
    <x-adminlte-modal id="pastMonthlyDueModal" title="Add Past Monthly Dues"
        icon="fas fa-fw fa-calendar-plus" theme="primary" size="md" scrollable v-centered wire:ignore.self>

        <div data-modal-skeleton class="{{ $loaded ? 'd-none' : '' }}">
            <x-ui.modals.modal-loading message="Loading past dues form…" />
        </div>

        <div data-modal-content class="{{ ! $loaded ? 'd-none' : '' }}">
            <div class="form-group">
                <label class="mb-1">Scope</label>
                <div class="d-flex flex-wrap">
                    <div class="custom-control custom-radio mr-4">
                        <input type="radio" id="pastDuesSingle" value="single"
                            class="custom-control-input" wire:model="scope">
                        <label class="custom-control-label" for="pastDuesSingle">
                            Single Homeowner
                            <br><small>Bill one homeowner's past months</small>
                        </label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input type="radio" id="pastDuesAll" value="all"
                            class="custom-control-input" wire:model="scope">
                        <label class="custom-control-label" for="pastDuesAll">
                            All Homeowners
                            <br><small>Bill every household on file</small>
                        </label>
                    </div>
                </div>
            </div>

            @if ($scope === 'single')
                <x-adminlte-select2 id="pastDuesOwnerSelect" name="user_id" label="Homeowner"
                    wire:model="user_id" igroup-size="sm"
                    :config="['placeholder' => 'Select a homeowner...', 'width' => '100%']">
                    <option value=""></option>
                    @foreach ($owners as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select2>
            @else
                <x-adminlte-input name="all_users_note" label="Homeowner" disabled
                    value="Every homeowners on file will be invoiced" igroup-size="sm" />
            @endif

            <div class="row">
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="from" label="From Month" type="month"
                        max="{{ now()->format('Y-m') }}" wire:model.lazy="from" igroup-size="sm" />
                </div>
                <div class="col-12 col-sm-6">
                    <x-adminlte-input name="to" label="To Month" type="month"
                        max="{{ now()->format('Y-m') }}" wire:model.lazy="to" igroup-size="sm" />
                </div>
            </div>

            <div class="border rounded p-3 mb-0 bg-light">
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Invoices to create</span>
                    <span class="font-weight-bold">{{ $preview['created'] }}</span>
                </div>
                <div class="d-flex justify-content-between small mt-1">
                    <span class="text-muted">Already billed (skipped)</span>
                    <span class="font-weight-bold">{{ $preview['skipped'] }}</span>
                </div>
                <div class="d-flex justify-content-between small mt-1">
                    <span class="text-muted">Total amount</span>
                    <span class="font-weight-bold">{{ $preview['total_display'] }}</span>
                </div>
            </div>
        </div>

        <x-slot name="footerSlot">
            <x-adminlte-button label="Cancel" theme="outline-secondary" data-dismiss="modal" class="btn-sm" />
            <x-adminlte-button label="Create Past Dues" theme="primary" icon="fas fa-fw fa-save"
                wire:click="confirm" wire:loading.attr="disabled" class="btn-sm"
                :disabled="! $loaded" />
            <span wire:loading wire:target="confirm" class="ml-2 text-info small">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </x-slot>
    </x-adminlte-modal>
</div>
