<div>
    {{-- =================================================================== --}}
    {{-- CARD TABS                                                           --}}
    {{-- =================================================================== --}}
    <div class="card card-tabs">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'billing' ? 'active' : '' }}" href="#"
                    wire:click.prevent="$set('activeTab', 'billing')">
                    <i class="fas fa-fw fa-file-invoice-dollar"></i> Monthly Dues &amp; Billing
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'payments' ? 'active' : '' }}" href="#"
                    wire:click.prevent="$set('activeTab', 'payments')">
                    <i class="fas fa-fw fa-hand-holding-usd"></i> Payments &amp; Receipts
                </a>
            </li>
        </ul>
    </div>


    {{-- ============================================================= --}}
    {{-- TAB 1 — MONTHLY DUES & BILLING                                --}}
    {{-- ============================================================= --}}
    @if ($activeTab === 'billing')
        <div wire:key="tab-billing">
            <form wire:submit.prevent="saveBilling">
                <x-adminlte-card title="Monthly Due Rates" icon="fas fa-fw fa-calculator">
                    {{-- One rate for every property type --}}
                    <div class="row align-items-center mb-2">
                        <div class="col-12 col-sm-6">
                            <span class="font-weight-medium">
                                {{ $billing_method === \App\Enums\BillingMethod::PER_SQM ? 'Rate per SQM' : 'Monthly Due Rate (per property)' }}
                            </span>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">{{ \App\Utility\MoneyHelper::symbol() }}</span>
                                </div>
                                <input type="number" step="0.01" min="0"
                                    class="form-control @error('monthly_rate') is-invalid @enderror"
                                    wire:model.lazy="monthly_rate"
                                    aria-label="Monthly rate">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        {{ $billing_method === \App\Enums\BillingMethod::PER_SQM ? 'per sqm' : 'per month' }}
                                    </span>
                                </div>
                            </div>
                            @error('monthly_rate')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    {{-- Billing method --}}
                    <div class="form-group">
                        <label class="mb-1">Billing Method</label>
                        <div class="row">
                            @foreach ($billingMethods as $method)
                                <div class="col-12 col-md-6 mb-2">
                                    <label class="d-block border rounded p-3 mb-0 h-100
                                        {{ $billing_method === $method->value ? 'border-primary' : '' }}"
                                        style="cursor:pointer;">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="billingMethod{{ $method->name }}"
                                                value="{{ $method->value }}"
                                                class="custom-control-input" wire:model="billing_method">
                                            <label class="custom-control-label font-weight-bold"
                                                for="billingMethod{{ $method->name }}">
                                                {{ $method->label() }}
                                            </label>
                                        </div>
                                        <div class="text-muted small pl-4">{{ $method->description() }}</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-adminlte-card>

                {{-- ========================================================= --}}
                {{-- INVOICE GENERATION                                        --}}
                {{-- ========================================================= --}}
                <x-adminlte-card title="Bill Generation" icon="fas fa-fw fa-calendar-check">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <x-adminlte-select name="generation_mode" label="Generation Mode"
                                wire:model="generation_mode" igroup-size="sm">
                                @foreach ($generationModes as $mode)
                                    <option value="{{ $mode->value }}">{{ $mode->label() }}</option>
                                @endforeach
                            </x-adminlte-select>
                            <p class="text-muted small mb-0">
                                {{ \App\Enums\GenerationMode::tryFrom($generation_mode)?->description() }}
                            </p>
                        </div>

                        <div class="col-12 col-md-4">
                            <x-adminlte-input name="generation_day" label="Generation Day"
                                type="number" min="1" max="28"
                                wire:model.lazy="generation_day" igroup-size="sm"
                                help="Day of the month invoices are raised on." />
                        </div>

                        <div class="col-12 col-md-4">
                            <x-adminlte-input name="grace_period_days" label="Grace Period (Days)"
                                type="number" min="0" max="60"
                                wire:model.lazy="grace_period_days" igroup-size="sm"
                                help="Days after the generation day a bill is still on time." />
                        </div>
                    </div>
                </x-adminlte-card>

                {{-- ========================================================= --}}
                {{-- REMINDERS AND PENALTY                                     --}}
                {{-- ========================================================= --}}
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <x-adminlte-card title="Automated Reminders" icon="fas fa-fw fa-bell">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="autoEmailReminders"
                                    wire:model="auto_email_reminders">
                                <label class="custom-control-label" for="autoEmailReminders">Automated Email Reminders</label>
                            </div>
                        </x-adminlte-card>
                    </div>

                    <div class="col-12 col-lg-6">
                        <x-adminlte-card title="Penalty Settings" icon="fas fa-fw fa-exclamation-triangle">
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-select name="penalty_type" label="Penalty Type"
                                        wire:model="penalty_type" igroup-size="sm">
                                        @foreach ($penaltyTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="penalty_value" label="Penalty"
                                        type="number" step="0.01" min="0"
                                        wire:model.lazy="penalty_value" igroup-size="sm" />
                                </div>
                            </div>
                        </x-adminlte-card>
                    </div>
                </div>

                <div class="text-right mb-4">
                    <x-adminlte-button label="Save Billing Settings" type="submit" theme="primary"
                        icon="fas fa-fw fa-save" wire:loading.attr="disabled" />
                    <span wire:loading wire:target="saveBilling" class="ml-2 text-info small">
                        <i class="fas fa-spinner fa-spin"></i> Saving...
                    </span>
                </div>
            </form>
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- TAB 2 — PAYMENTS & RECEIPTS                                   --}}
    {{-- ============================================================= --}}
    @if ($activeTab === 'payments')
        <div wire:key="tab-payments">
            <x-adminlte-card title="Payment Methods" icon="fas fa-fw fa-credit-card" class="bg-white">
                <p class="text-muted small">
                    The ways homeowners can pay, shown on invoices and in the portal.
                </p>

                <div class="table-responsive border rounded mb-3">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width:18%;">Payment Method</th>
                                <th>Details / Instructions</th>
                                <th style="width:9%;" class="text-center">QR</th>
                                <th style="width:9%;" class="text-center">Active</th>
                                <th style="width:130px;" class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($paymentMethods as $method)
                                <tr>
                                    <td class="align-middle font-weight-medium">{{ $method->name }}</td>
                                    <td class="align-middle">
                                        <span class="small">{{ $method->details ?: '—' }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if ($method->qr_url)
                                            <a href="{{ $method->qr_url }}" target="_blank" rel="noopener">
                                                <span class="badge badge-success">QR</span>
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-{{ $method->is_active ? 'success' : 'secondary' }}">
                                            {{ $method->is_active ? 'On' : 'Off' }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-right text-nowrap">
                                        @can('update', \App\Models\Setting::class)
                                            <div class="btn-group" role="group">
                                                <x-adminlte-button label="" theme="primary" icon="fas fa-fw fa-edit"
                                                    data-toggle="modal" data-target="#paymentMethodFormModal"
                                                    data-id="{{ $method->id }}"
                                                    wire:loading.attr="disabled" class="btn-xs" title="Edit" />
                                                <x-adminlte-button label="" theme="danger" icon="fas fa-fw fa-trash"
                                                    data-toggle="modal" data-target="#paymentMethodDeleteModal"
                                                    data-id="{{ $method->id }}"
                                                    wire:loading.attr="disabled" class="btn-xs" title="Delete" />
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No payment methods yet. Add how residents pay — Cash, GCash, bank transfer.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @can('update', \App\Models\Setting::class)
                    <x-adminlte-button label="Add Payment Method" theme="secondary" icon="fas fa-fw fa-plus"
                        data-toggle="modal" data-target="#paymentMethodFormModal"/>
                @endcan
            </x-adminlte-card>

            {{-- ========================================================= --}}
            {{-- EMAIL CONSTRUCTION                                        --}}
            {{-- ========================================================= --}}
            <form wire:submit.prevent="saveInstructions">
                <x-adminlte-card title="Email Construction" icon="fas fa-fw fa-file-alt">
                    <p class="text-muted small">
                        This text is included in the Statement of Account email sent to homeowners. It is not printed
                        on invoices and does not appear in the homeowner portal.
                    </p>

                    <label class="mb-1">Email Construction</label>
                    <div wire:ignore wire:key="invoice-instructions-editor">
                        <div id="invoiceInstructionsEditor" data-rich-text="invoiceInstructions"
                            data-rich-text-height="200"
                            data-rich-text-placeholder="e.g., Please settle your dues in person at the HOA office during office hours.">{!! $invoice_instructions !!}</div>
                    </div>
                    <textarea id="invoiceInstructions" class="d-none" wire:model.defer="invoice_instructions"></textarea>
                    @error('invoice_instructions')<span class="text-danger small d-block">{{ $message }}</span>@enderror

                    <div class="mb-3 mt-2">
                        <span class="text-muted small d-block mb-1">Available Placeholders:</span>
                        @foreach ($placeholders as $placeholder)
                            <span class="badge badge-light border mr-1 mb-1">{{ $placeholder }}</span>
                        @endforeach
                    </div>

                    <label class="mb-1">Preview</label>
                    <div class="border rounded p-3 bg-light small">{!! $instructionsPreview ?: 'Nothing yet — the instructions are added per invoice as they are raised.' !!}</div>

                    <div class="text-right mt-3 mb-4">
                        <x-adminlte-button label="Save Instructions" type="submit" theme="primary"
                            icon="fas fa-fw fa-save" wire:loading.attr="disabled"/>
                        <span wire:loading wire:target="saveInstructions" class="ml-2 text-info small">
                            <i class="fas fa-spinner fa-spin"></i> Saving...
                        </span>
                    </div>
                </x-adminlte-card>
            </form>
        </div>
    @endif

    @include('livewire.admin.settings.partials.summernote')
</div>
