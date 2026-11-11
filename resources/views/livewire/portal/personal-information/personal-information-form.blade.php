{{-- =================================================================== --}}
{{-- THE PROFILE THE ASSOCIATION KEEPS                                  --}}
{{-- Three sections: the person, how the office reaches them, and the   --}}
{{-- sectoral tags their record carries.                                --}}
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
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-input name="form.philsys_card_no" label="PhilSys Card No."
                                placeholder="1234-5678-9101-1213" wire:model.defer="form.philsys_card_no" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-fingerprint"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-input name="form.surname" label="Surname" placeholder="Enter surname"
                                wire:model.defer="form.surname" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-input name="form.first_name" label="First Name" placeholder="Enter first name"
                                wire:model.defer="form.first_name" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-adminlte-input name="form.middle_name" label="Middle Name" placeholder="Optional"
                                wire:model.defer="form.middle_name" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-3">
                            <x-adminlte-input name="form.suffix" label="Suffix" placeholder="e.g., Jr., III"
                                wire:model.defer="form.suffix" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-md-5">
                            <x-adminlte-input name="form.birth_date" label="Birth Date" type="date"
                                wire:model.defer="form.birth_date" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-md-4">
                            <x-adminlte-input name="form.birth_place" label="Birth Place" placeholder="Enter birth place"
                                wire:model.defer="form.birth_place" igroup-size="sm" enable-old-support>
                                <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-pin"></i></div></x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-select name="form.sex" label="Sex" wire:model.defer="form.sex" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-venus-mars"></i></div>
                                </x-slot>
                                @foreach ($sexOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                        <div class="col-12 col-md-6">
                            <x-adminlte-select name="form.civil_status" label="Civil Status"
                                wire:model="form.civil_status" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-ring"></i></div>
                                </x-slot>
                                @foreach ($civilStatusOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </x-adminlte-select>
                        </div>
                    </div>

                    @if ($showSpouse)
                        <div class="row">
                            <div class="col-12 col-md-7">
                                <x-adminlte-input name="form.spouse_name" label="Spouse Name" placeholder="Name of spouse"
                                    wire:model.defer="form.spouse_name" igroup-size="sm">
                                    <x-slot name="prependSlot">
                                        <div class="input-group-text"><i class="fas fa-fw fa-heart"></i></div>
                                    </x-slot>
                                </x-adminlte-input>
                            </div>
                        </div>
                    @endif

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
                                wire:model.defer="form.citizenship" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-globe"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-input name="form.religion" label="Religion" placeholder="Optional"
                                wire:model.defer="form.religion" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-church"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                        <div class="col-12 col-md-6">
                            <x-adminlte-input name="form.occupation" label="Occupation" placeholder="Optional"
                                wire:model.defer="form.occupation" igroup-size="sm">
                                <x-slot name="prependSlot">
                                    <div class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></div>
                                </x-slot>
                            </x-adminlte-input>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <x-adminlte-select name="form.educational_attainment" label="Educational Attainment"
                                wire:model.defer="form.educational_attainment" igroup-size="sm">
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
                                        value="{{ $value }}" wire:model.defer="form.sectors">
                                    <label class="custom-control-label" for="portal-sector-{{ $value }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('form.sectors')<span class="text-danger small">{{ $message }}</span>@enderror

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
                        The office profiles every homeowner before it issues a portal account — contact the office
                        if this looks wrong.
                    </small>
                </div>
            </div>

        @endif

    </div>
</div>
