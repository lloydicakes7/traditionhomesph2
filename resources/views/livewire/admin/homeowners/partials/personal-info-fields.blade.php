<div>

    {{-- ───────── Personal Information ───────── --}}
    <div class="mb-4">
        <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3">
            <i class="fas fa-fw fa-user mr-1"></i>Personal Information
        </h6>

        {{-- Identification --}}
        <div class="row">
            <div class="col-12 col-md-4">
                <x-adminlte-input name="{{ $prefix }}.philsys_card_no" label="PhilSys Card No."
                    placeholder="1234-5678-9101-1213" wire:model.defer="{{ $prefix }}.philsys_card_no" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-fingerprint"></i></div></x-slot>
                </x-adminlte-input>
            </div>
        </div>

        {{-- Name --}}
        <div class="row">
            <div class="col-12 col-md-3">
                <x-adminlte-input name="{{ $prefix }}.surname" label="Surname *" placeholder="Last name"
                    wire:model.defer="{{ $prefix }}.surname" igroup-size="sm" />
            </div>
            <div class="col-12 col-md-3">
                <x-adminlte-input name="{{ $prefix }}.first_name" label="First Name *" placeholder="Given name"
                    wire:model.defer="{{ $prefix }}.first_name" igroup-size="sm" />
            </div>
            <div class="col-12 col-md-3">
                <x-adminlte-input name="{{ $prefix }}.middle_name" label="Middle Name" placeholder="Optional"
                    wire:model.defer="{{ $prefix }}.middle_name" igroup-size="sm" />
            </div>
            <div class="col-12 col-md-3">
                <x-adminlte-input name="{{ $prefix }}.suffix" label="Suffix" placeholder="Jr., III"
                    wire:model.defer="{{ $prefix }}.suffix" igroup-size="sm" />
            </div>
        </div>

        {{-- Birth & sex --}}
        <div class="row">
            <div class="col-12 col-md-4">
                <x-adminlte-input name="{{ $prefix }}.birth_date" label="Birth Date *" type="date"
                    wire:model.defer="{{ $prefix }}.birth_date" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div></x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-md-4">
                <x-adminlte-input name="{{ $prefix }}.birth_place" label="Birth Place" placeholder="City, Province"
                    wire:model.defer="{{ $prefix }}.birth_place" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-pin"></i></div></x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-md-4">
                <x-adminlte-select name="{{ $prefix }}.sex" label="Sex *"
                    wire:model.defer="{{ $prefix }}.sex" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-venus-mars"></i></div></x-slot>
                    <option value="">— Select —</option>
                    @foreach ($sexOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
        </div>

        {{-- Civil status --}}
        <div class="row">
            <div class="col-12 col-md-4">
                <x-adminlte-select name="{{ $prefix }}.civil_status" label="Civil Status *"
                    wire:model="{{ $prefix }}.civil_status" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ring"></i></div></x-slot>
                    @foreach ($civilStatusOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>

            @if (! empty($showSpouse))
                <div class="col-12 col-md-8">
                    <x-adminlte-input name="{{ $prefix }}.spouse_name" label="Spouse Name" placeholder="If married"
                        wire:model.defer="{{ $prefix }}.spouse_name" igroup-size="sm">
                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-heart"></i></div></x-slot>
                    </x-adminlte-input>
                </div>
            @endif
        </div>
    </div>

    {{-- ───────── Contact & Background ───────── --}}
    <div class="mb-4">
        <h6 class="text-uppercase text-muted font-weight-bold small border-bottom pb-1 mb-3">
            <i class="fas fa-fw fa-address-card mr-1"></i>Contact &amp; Background
        </h6>

        <div class="row">
            <div class="col-12 col-md-4">
                <x-adminlte-input-contact name="{{ $prefix }}.mobile_no" label="Mobile No."
                    wire:model.defer="{{ $prefix }}.mobile_no" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-mobile-alt"></i></div></x-slot>
                </x-adminlte-input-contact>
            </div>
            <div class="col-12 col-md-4">
                <x-adminlte-input name="{{ $prefix }}.citizenship" label="Citizenship *" placeholder="e.g., Filipino"
                    wire:model.defer="{{ $prefix }}.citizenship" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-globe"></i></div></x-slot>
                </x-adminlte-input>
            </div>
            <div class="col-12 col-md-4">
                <x-adminlte-input name="{{ $prefix }}.religion" label="Religion" placeholder="Optional"
                    wire:model.defer="{{ $prefix }}.religion" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-church"></i></div></x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-6">
                <x-adminlte-select name="{{ $prefix }}.educational_attainment" label="Educational Attainment"
                    wire:model.defer="{{ $prefix }}.educational_attainment" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-graduation-cap"></i></div></x-slot>
                    <option value="">— Select —</option>
                    @foreach ($educationalAttainmentOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-12 col-md-6">
                <x-adminlte-input name="{{ $prefix }}.occupation" label="Occupation" placeholder="Optional"
                    wire:model.defer="{{ $prefix }}.occupation" igroup-size="sm">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></div></x-slot>
                </x-adminlte-input>
            </div>
        </div>
    </div>
</div>
