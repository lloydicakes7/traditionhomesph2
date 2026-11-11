{{-- The person's own profile, as the association records it: the same fields,
     in the same layout, the office's own member form carries. Shared by the
     "new resident" and "edit member" sides of the members modal, so a member
     is asked for the same record either way. --}}
<div class="row">
    <div class="col-12 col-md-6">
        <x-adminlte-input name="form.surname" label="Surname" placeholder="Last name"
            wire:model.defer="form.surname" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-md-6">
        <x-adminlte-input name="form.first_name" label="First Name" placeholder="Given name"
            wire:model.defer="form.first_name" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <x-adminlte-input name="form.middle_name" label="Middle Name" placeholder="Optional"
            wire:model.defer="form.middle_name" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-md-6">
        <x-adminlte-input name="form.suffix" label="Suffix" placeholder="e.g., Jr., III"
            wire:model.defer="form.suffix" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-4">
        <x-adminlte-input name="form.birth_date" label="Birth Date" type="date"
            wire:model.defer="form.birth_date" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-md-4">
        <x-adminlte-input name="form.birth_place" label="Birth Place" placeholder="City, Province"
            wire:model.defer="form.birth_place" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-pin"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-md-4">
        <x-adminlte-select name="form.sex" label="Sex" wire:model.defer="form.sex" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-venus-mars"></i></div></x-slot>
            <option value="">— Select —</option>
            @foreach ($sexOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <x-adminlte-select name="form.civil_status" label="Civil Status"
            wire:model.defer="form.civil_status" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ring"></i></div></x-slot>
            <option value="">— Select —</option>
            @foreach ($civilStatusOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>
    <div class="col-12 col-md-6">
        <x-adminlte-input name="form.citizenship" label="Citizenship" placeholder="e.g., Filipino"
            wire:model.defer="form.citizenship" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-globe"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <x-adminlte-input name="form.religion" label="Religion" placeholder="Optional"
            wire:model.defer="form.religion" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-church"></i></div></x-slot>
        </x-adminlte-input>
    </div>
    <div class="col-12 col-md-6">
        <x-adminlte-input name="form.occupation" label="Occupation" placeholder="Optional"
            wire:model.defer="form.occupation" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></div></x-slot>
        </x-adminlte-input>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <x-adminlte-input-contact name="form.mobile_no" label="Mobile No."
            wire:model.defer="form.mobile_no" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-mobile-alt"></i></div></x-slot>
        </x-adminlte-input-contact>
    </div>
    <div class="col-12 col-md-6">
        <x-adminlte-select name="form.educational_attainment" label="Educational Attainment"
            wire:model.defer="form.educational_attainment" igroup-size="sm">
            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-graduation-cap"></i></div></x-slot>
            <option value="">— Select —</option>
            @foreach ($educationalAttainmentOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </x-adminlte-select>
    </div>
</div>
