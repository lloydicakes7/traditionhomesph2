@props([
    'form' => [],
    'selectedType' => null,
    'purposeOptions' => [],
    'prefix' => 'requestType',
    'membershipProfile' => null,
    'respondentUnit' => null,
    'supplyMissing' => false,
])

{{--
    The Request Type control and the fields that type asks for — the one place
    that markup lives, so the portal's filing form, the office's own filing form
    and the office's edit form cannot drift apart.

    Every type's shape comes from RequestType itself (`hasPurpose()`,
    `hasBusinessDetails()`, …), so the form asks for exactly what the selected
    type carries and nothing else.

    Three things the hosting components own and this fragment does not:
      • the wire:model bindings are always `form.*` — that is the contract all
        three forms share;
      • `$prefix` namespaces the HTML ids, because more than one of those forms
        can sit on the same page (the office's own form and its edit dialog);
      • the submit button, which lives in each dialog's own footer.

    A Membership Form asks for nothing typed: its details, household members and
    owner are shown back from the records on file (`membershipProfile`), and the
    Endorsement Letter's respondent unit is read from the same household rather
    than entered (`respondentUnit`). Only the office's dialogs set `supplyMissing`,
    which lets them fill a detail the record leaves blank without changing the
    homeowner's form.
--}}

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
            <div class="col-12">
                <x-adminlte-input name="{{ $prefix }}_permit_type" label="Permit Type"
                    placeholder="e.g. Construction, Renovation"
                    wire:model.defer="form.permit_type" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-hard-hat"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12">
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
            <div class="col-12">
                <x-adminlte-input name="{{ $prefix }}_addressee" label="Addressee"
                    placeholder="Who the letter is addressed to"
                    wire:model.defer="form.addressee" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12">
                <x-adminlte-input name="{{ $prefix }}_letter_date" type="date" label="Date"
                    wire:model.defer="form.letter_date" igroup-size="sm">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-fw fa-calendar-alt"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>

            <div class="col-12 col-md-6">
                <label class="small mb-1" for="{{ $prefix }}_respondent_block">Block</label>
                <input type="text" id="{{ $prefix }}_respondent_block"
                    class="form-control form-control-sm bg-light"
                    value="{{ $respondentUnit['block'] ?? '' }}" readonly>
            </div>

            <div class="col-12 col-md-6">
                <label class="small mb-1" for="{{ $prefix }}_respondent_lot">Lot</label>
                <input type="text" id="{{ $prefix }}_respondent_lot"
                    class="form-control form-control-sm bg-light"
                    value="{{ $respondentUnit['lot'] ?? '' }}" readonly>
            </div>

            <div class="col-12">
                <div class="alert alert-light border mb-0 small">
                    <i class="fas fa-fw fa-map-marker-alt mr-1"></i>
                    The respondent's unit is read from the household on file — it is never typed in.
                </div>
            </div>
        @endif

        {{-- MEMBERSHIP FORM — nothing to type: the whole form is on file. --}}
        @if ($selectedType->usesProfileRecord())
            <div class="col-12">
                <x-customs.membership-form-profile :profile="$membershipProfile" :supply="$supplyMissing" />
            </div>
        @endif
    </div>
@endif
