{{-- =================================================================== --}}
{{-- THE PROFILE THE ASSOCIATION KEEPS                                  --}}
{{-- Three sections: the person, how the office reaches them, and the   --}}
{{-- sectoral tags their record carries.                                --}}
@php
    $selectedSectors = (array) ($this->form['sectors'] ?? []);
    $employed = in_array(\App\Enums\SectorType::LABOR_FORCE_EMPLOYED, $selectedSectors, true);
    $unemployed = in_array(\App\Enums\SectorType::UNEMPLOYED, $selectedSectors, true) && ! $employed;
    $married = ($this->form['civil_status'] ?? '') === \App\Enums\CivilStatus::MARRIED;
    $soloParent = in_array(\App\Enums\SectorType::SOLO_PARENT, $selectedSectors, true);
    $sectorWarnings = $this->sectoralWarnings('form');
@endphp

{{-- =================================================================== --}}
<div class="row">
    <div class="col-md-12">

        @if ($resident)
            <form wire:submit.prevent="save">

                {{-- ========================================================= --}}
                {{-- A. PERSONAL INFORMATION                                    --}}
                {{-- ========================================================= --}}
                <x-adminlte-card title="Personal Information" icon="fas fa-fw fa-user-edit">

                    <div class="row">
                        <div class="col-12 col-sm-8 col-md-6 col-lg-5">
                            <x-adminlte-input name="form.philsys_card_no" label="PhilSys Card No."
                                placeholder="1234-5678-9101-1213" maxlength="19" data-thha-philsys
                                wire:model.defer="form.philsys_card_no" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-fingerprint"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-input name="form.surname" label="Surname" placeholder="Enter surname"
                                maxlength="255" data-thha-uppercase wire:model.defer="form.surname" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-input name="form.first_name" label="First Name" placeholder="Enter first name"
                                maxlength="255" data-thha-uppercase wire:model.defer="form.first_name" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-input name="form.middle_name" label="Middle Name" placeholder="Optional"
                                maxlength="255" data-thha-uppercase wire:model.defer="form.middle_name" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-select name="form.suffix" label="Suffix" class="text-uppercase"
                                wire:model.defer="form.suffix" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                                <option value="">Select</option>
                                @foreach ($suffixOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
                            <x-adminlte-select name="form.sex" label="Sex" class="text-uppercase"
                                wire:model.defer="form.sex" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-venus-mars"></i></div>
                                </x-slot>
                                @foreach ($sexOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <x-adminlte-input name="form.birth_date" label="Birth Date" type="date"
                                wire:model="form.birth_date" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4">
                            <x-adminlte-input name="form.birth_place" label="Birth Place" placeholder="Enter birth place"
                                maxlength="255" data-thha-uppercase
                                wire:model.defer="form.birth_place" igroup-size="sm" enable-old-support>
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-pin"></i></div></x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-adminlte-select name="form.civil_status" label="Civil Status" class="text-uppercase"
                                wire:model="form.civil_status" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-ring"></i></div>
                                </x-slot>
                                @foreach ($civilStatusOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <x-adminlte-input name="form.spouse_name"
                                label="Spouse Name"
                                placeholder="Name of spouse" maxlength="255" data-thha-uppercase
                                wire:model.defer="form.spouse_name" igroup-size="sm"
                                :disabled="! $showSpouse">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-heart"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>
                </x-adminlte-card>

                {{-- ========================================================= --}}
                {{-- B. CONTACT & BACKGROUND                                    --}}
                {{-- ========================================================= --}}
                <x-adminlte-card title="Contact &amp; Background" icon="fas fa-fw fa-address-card">

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-input-contact name="form.mobile_no" label="Mobile No."
                                wire:model.defer="form.mobile_no" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-mobile-alt"></i></div>
                                </x-slot>
                            </x-adminlte-input-contact>
                        </div>
                        <div class="col-12 col-md-6">
                            <x-adminlte-input name="form.citizenship" label="Citizenship" placeholder="e.g., Filipino"
                                maxlength="255" data-thha-uppercase wire:model.defer="form.citizenship" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-globe"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-input name="form.religion" label="Religion" placeholder="Optional"
                                maxlength="255" data-thha-uppercase wire:model.defer="form.religion" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-church"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-md-6">
                            <x-adminlte-input name="form.occupation"
                                label="Occupation{{ $employed ? ' *' : '' }}"
                                placeholder="{{ $unemployed ? 'Cleared for an unemployed resident' : 'Optional' }}"
                                maxlength="255" data-thha-uppercase wire:model="form.occupation"
                                igroup-size="sm" :disabled="$unemployed">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-select name="form.educational_attainment" label="Educational Attainment"
                                class="text-uppercase" wire:model="form.educational_attainment" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-graduation-cap"></i></div>
                                </x-slot>
                                @foreach ($educationalAttainmentOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                    </div>

                    <p class="text-muted small mb-0">
                        <i class="fas fa-fw fa-info-circle mr-1"></i>
                        Your photo and email address are on <a href="{{ route('portal.profile') }}">Profile</a>.
                    </p>

                </x-adminlte-card>

                {{-- ========================================================= --}}
                {{-- C. SECTORAL TAGS                                           --}}
                {{-- ========================================================= --}}
                <x-adminlte-card title="Sectoral Tags" icon="fas fa-fw fa-tags">
                    <div class="row">
                        @foreach ($sectorOptions as $value => $label)
                            <div class="col-md-4 mb-1">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="portal-sector-{{ $value }}"
                                        value="{{ $value }}" wire:model="form.sectors">
                                    <label class="custom-control-label" for="portal-sector-{{ $value }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('form.sectors')
                        <div class="alert alert-danger small py-2 mt-3 mb-0">
                            <ul class="mb-0 pl-3">
                                <li>{{ $message }}</li>
                            </ul>
                        </div>
                    @enderror

                    @if ($married && $soloParent)
                        <div class="custom-control custom-checkbox mt-2">
                            <input type="checkbox" class="custom-control-input" id="portal-solo-parent-special"
                                wire:model="form.solo_parent_special_case">
                            <label class="custom-control-label small" for="portal-solo-parent-special">
                                My spouse is incapacitated, detained, or working abroad
                            </label>
                        </div>
                        @error('form.solo_parent_special_case')
                            <div class="alert alert-danger small py-2 mt-2 mb-0">
                                <ul class="mb-0 pl-3">
                                    <li>{{ $message }}</li>
                                </ul>
                            </div>
                        @enderror
                    @endif

                    @if ($sectorWarnings)
                        <div class="alert alert-warning small py-2 mt-3 mb-0">
                            <ul class="mb-0 pl-3">
                                @foreach ($sectorWarnings as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="text-right mt-3">
                        <x-adminlte-button type="submit" label="Save Changes" theme="primary"
                            icon="fas fa-fw fa-save" class="btn-sm" wire:loading.attr="disabled" />
                        <span wire:loading wire:target="save" class="ml-2 text-info small">
                            <i class="fas fa-spinner fa-spin"></i> Saving…
                        </span>
                    </div>
                </x-adminlte-card>

            </form>

        @else

            <div class="card shadow-sm">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-fw fa-user-edit fa-2x mb-2"></i>
                    <p class="mb-1">No profile has been recorded for your account yet.</p>
                    <small>
                        The office profiles every homeowner before it issues a portal account. Contact the office
                        if this looks wrong.
                    </small>
                </div>
            </div>

        @endif

    </div>
</div>
