@php
    $selectedSectors = (array) ($this->{$prefix}['sectors'] ?? []);
    $employed = in_array(\App\Enums\SectorType::LABOR_FORCE_EMPLOYED, $selectedSectors, true);
    $unemployed = in_array(\App\Enums\SectorType::UNEMPLOYED, $selectedSectors, true) && ! $employed;
    $married = ($this->{$prefix}['civil_status'] ?? '') === \App\Enums\CivilStatus::MARRIED;
    $soloParent = in_array(\App\Enums\SectorType::SOLO_PARENT, $selectedSectors, true);
    $sectorWarnings = method_exists($this, 'sectoralWarnings') ? $this->sectoralWarnings($prefix) : [];
@endphp

{{-- ===== Identification ===== --}}
<div class="row">
    <div class="col-12 col-sm-8 col-md-6 col-lg-5">
        <x-adminlte-input name="{{ $prefix }}.philsys_card_no" label="PhilSys Card No."
            placeholder="1234-5678-9101-1213" maxlength="19" data-thha-philsys
            wire:model.defer="{{ $prefix }}.philsys_card_no" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-fingerprint"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

{{-- ===== Name ===== --}}
<div class="row">
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <x-adminlte-input name="{{ $prefix }}.surname" label="Surname *" placeholder="Last name"
            maxlength="255" data-thha-uppercase wire:model.defer="{{ $prefix }}.surname" igroup-size="sm" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <x-adminlte-input name="{{ $prefix }}.first_name" label="First Name *" placeholder="Given name"
            maxlength="255" data-thha-uppercase wire:model.defer="{{ $prefix }}.first_name" igroup-size="sm" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <x-adminlte-input name="{{ $prefix }}.middle_name" label="Middle Name" placeholder="Optional"
            maxlength="255" data-thha-uppercase wire:model.defer="{{ $prefix }}.middle_name" igroup-size="sm" />
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <x-adminlte-select name="{{ $prefix }}.suffix" label="Suffix" class="text-uppercase"
            wire:model.defer="{{ $prefix }}.suffix" igroup-size="sm">
            <option value="">Select</option>
            @foreach ($suffixOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>
</div>

{{-- ===== Other informations ===== --}}
<div class="row">
    <div class="col-12 col-sm-6 col-md-4">
        <x-adminlte-input name="{{ $prefix }}.birth_date" label="Birth Date *" type="date"
            wire:model="{{ $prefix }}.birth_date" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <x-adminlte-input name="{{ $prefix }}.birth_place" label="Birth Place *" placeholder="City, Province"
            maxlength="255" data-thha-uppercase wire:model.defer="{{ $prefix }}.birth_place" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-pin"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <x-adminlte-select name="{{ $prefix }}.sex" label="Sex *" class="text-uppercase"
            wire:model.defer="{{ $prefix }}.sex" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-venus-mars"></i></div></x-slot>
            <option value="">Select</option>
            @foreach ($sexOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <x-adminlte-input-contact name="{{ $prefix }}.mobile_no" label="Mobile No. *"
            wire:model.defer="{{ $prefix }}.mobile_no" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-mobile-alt"></i></div></x-slot>
        </x-adminlte-input-contact>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <x-adminlte-input name="{{ $prefix }}.citizenship" label="Citizenship *" placeholder="e.g., Filipino"
            maxlength="255" data-thha-uppercase wire:model.defer="{{ $prefix }}.citizenship" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-globe"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <x-adminlte-input name="{{ $prefix }}.religion" label="Religion" placeholder="Optional"
            maxlength="255" data-thha-uppercase wire:model.defer="{{ $prefix }}.religion" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-church"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

{{-- ===== Civil status ===== --}}
<div class="row">
    <div class="col-12 col-sm-6 col-md-4">
        <x-adminlte-select name="{{ $prefix }}.civil_status" label="Civil Status *" class="text-uppercase"
            wire:model="{{ $prefix }}.civil_status" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ring"></i></div></x-slot>
            <option value="">Select</option>
            @foreach ($civilStatusOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>

    <div class="col-12 col-sm-6 col-md-8">
        <x-adminlte-input name="{{ $prefix }}.spouse_name"
            label="Spouse Name{{ ($spouseRequired ?? false) ? ' *' : '' }}"
            placeholder="Name of spouse" maxlength="255" data-thha-uppercase
            wire:model.defer="{{ $prefix }}.spouse_name" igroup-size="sm"
            :disabled="! ($showSpouse ?? false)">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-heart"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

{{-- ===== Background ===== --}}
<div class="row">
    <div class="col-12 col-sm-6 col-md-5 col-lg-6">
        <x-adminlte-select name="{{ $prefix }}.educational_attainment" label="Educational Attainment"
            class="text-uppercase" wire:model="{{ $prefix }}.educational_attainment" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-graduation-cap"></i></div></x-slot>
            <option value="">Select</option>
            @foreach ($educationalAttainmentOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>
    <div class="col-12 col-sm-6 col-md-7 col-lg-6">
        <x-adminlte-input name="{{ $prefix }}.occupation"
            label="Occupation{{ $employed ? ' *' : '' }}"
            placeholder="{{ $unemployed ? 'Cleared for an unemployed resident' : 'Optional' }}"
            maxlength="255" data-thha-uppercase wire:model="{{ $prefix }}.occupation"
            igroup-size="sm" :disabled="$unemployed">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

{{-- ===== Sectoral tags ===== --}}
<div class="mb-3">
    <label class="small font-weight-bold mb-1">Sectoral Tags</label>
    <div class="row">
        @foreach ($sectorOptions as $value => $label)
            <div class="col-12 col-sm-6 col-md-4 mb-1">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input"
                        id="{{ $prefix }}-sector-{{ $value }}" value="{{ $value }}"
                        wire:model="{{ $prefix }}.sectors">
                    <label class="custom-control-label" for="{{ $prefix }}-sector-{{ $value }}">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>
    @error($prefix . '.sectors')
        <div class="alert alert-danger small py-2 mt-2 mb-0">
            <ul class="mb-0 pl-3">
                <li>{{ $message }}</li>
            </ul>
        </div>
    @enderror

    @if ($married && $soloParent)
        <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="{{ $prefix }}-solo-parent-special"
                wire:model="{{ $prefix }}.solo_parent_special_case">
            <label class="custom-control-label small" for="{{ $prefix }}-solo-parent-special">
                My spouse is incapacitated, detained, or working abroad
            </label>
        </div>
        @error($prefix . '.solo_parent_special_case')
            <div class="alert alert-danger small py-2 mt-2 mb-0">
                <ul class="mb-0 pl-3">
                    <li>{{ $message }}</li>
                </ul>
            </div>
        @enderror
    @endif

    @if ($sectorWarnings)
        <div class="alert alert-warning small py-2 mt-2 mb-0">
            <ul class="mb-0 pl-3">
                @foreach ($sectorWarnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
