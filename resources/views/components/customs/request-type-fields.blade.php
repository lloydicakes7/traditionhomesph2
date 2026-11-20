@props([
    'form' => [],
    'selectedType' => null,
    'purposeOptions' => [],
    'prefix' => 'requestType',
    'membershipProfile' => null,
    'respondentUnit' => null,
    'detailsInformation' => [],
    'supplyMissing' => false,
])

{{-- WHAT IS BEING ASKED FOR — decides which fields follow --}}
<div class="row">
    <div class="col-12">
        <x-adminlte-select name="{{ $prefix }}_type" label="Request Type"
            wire:model="form.type" igroup-size="sm">
            <x-slot name="prependSlot">
                <div class="input-group-text"><i class="fas fa-fw fa-clipboard-list"></i></div>
            </x-slot>
            <option value="">Select a request type...</option>
            @foreach (\App\Enums\RequestType::labels() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>
</div>

@if ($selectedType)
    <div class="row">
        {{-- CERTIFICATION / BUSINESS PERMIT — why it is being asked --}}
        @if ($selectedType->hasPurpose())
            <div class="col-12">
                <x-adminlte-select name="{{ $prefix }}_purpose" label="Purpose"
                    wire:model="form.purpose" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-tag"></i></div>
                    </x-slot>
                    <option value="">Select a purpose...</option>
                    @foreach ($purposeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
        @endif

        {{-- BUSINESS PERMIT --}}
        @if ($selectedType->hasBusinessDetails())
            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}_business_name" label="Business Name"
                    wire:model.defer="form.business_name" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-store"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}_nature_of_business" label="Nature of Business"
                    wire:model.defer="form.nature_of_business" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}_hours_of_operation" label="Hours of Operation"
                    placeholder="e.g. 8:00 AM – 5:00 PM"
                    wire:model.defer="form.hours_of_operation" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-clock"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            @if (($form['purpose'] ?? '') === \App\Enums\BusinessPermitPurpose::OTHER)
                <div class="col-12 col-md-6">
                    <x-adminlte-input name="{{ $prefix }}_purpose_other" label="Specify Purpose"
                        wire:model.defer="form.purpose_other" igroup-size="sm">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-fw fa-pen"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>
            @endif
        @endif

        {{-- HOA PERMIT --}}
        @if ($selectedType->hasPermitDetails())
            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}_permit_type" label="Permit Type"
                    placeholder="e.g. Construction, Renovation"
                    wire:model.defer="form.permit_type" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-hard-hat"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}_location" label="Location"
                    placeholder="Where the work or activity will take place"
                    wire:model.defer="form.location" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-map-marker-alt"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12">
                <x-adminlte-textarea name="{{ $prefix }}_scope_of_work" label="Purpose / Scope of Work" rows="3"
                    placeholder="What the work is, and anything the office needs to know to act on it."
                    wire:model.defer="form.scope_of_work" igroup-size="sm" />
            </div>
        @endif

        {{-- ENDORSEMENT LETTER --}}
        @if ($selectedType->hasAddressee())
            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}_addressee" label="Addressee"
                    placeholder="Who the letter is addressed to"
                    wire:model.defer="form.addressee" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}_letter_date" type="date" label="Date"
                    wire:model.defer="form.letter_date" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-calendar-alt"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        @endif

        {{-- WHY IT IS BEING REQUESTED — optional, for every type --}}
        <div class="col-12">
            <x-adminlte-textarea name="{{ $prefix }}_reason" label="Reason for Request" rows="3"
                placeholder="Optional — explain why you are requesting this document."
                wire:model.defer="form.reason" igroup-size="sm" />
            @error('form.reason') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        {{-- MEMBERSHIP FORM — nothing to type: the whole form is on file. --}}
        @if ($selectedType->usesProfileRecord())
            <div class="col-12">
                <x-customs.membership-form-profile :profile="$membershipProfile" :supply="$supplyMissing"
                    :prefix="$prefix" />
            </div>
        @else
            <div class="col-12">
                <x-customs.request-details-information :rows="$detailsInformation" />
            </div>
        @endif
    </div>
@endif
