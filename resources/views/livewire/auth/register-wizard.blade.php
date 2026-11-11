{{-- ===================================================================== --}}
{{-- Tradition Homes REGISTRATION                                          --}}
{{-- ===================================================================== --}}

@php
    $steps = [
        1 => ['Account',              'Your email address'],
        2 => ['Personal information', 'Your personal details'],
        3 => ['Address',              'Your unit and household'],
        4 => ['Valid ID',             'A photo of one valid ID'],
        5 => ['Review and consent',   'Check and submit'],
    ];
@endphp

<div>

    @if ($registered)
        <div class="mx-auto" style="max-width: 460px;">
            <div class="card">
                <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                    <a href="{{ route('home') }}" class="mr-2">
                        <img src="{{ asset(config('thha.logo_path', 'image/logo.jpg')) }}"
                            alt="{{ config('thha.hoa_short_name') }}"
                            class="elevation-2 rounded mr-2"
                            width="40"
                            height="40">
                    </a>
                    <h1 class="card-title text-secondary text-uppercase">Tradition Homes</h1>
                </div>
                <div class="card-body">
                    <h5 class="font-weight-bold text-success mb-3">
                        <i class="fas fa-fw fa-check-circle mr-1"></i>Registration received
                    </h5>
                    <p class="mb-5">{{ __('auth.registered_received') }}</p>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-fw fa-sign-in-alt mr-1"></i>Go to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else

        @if ($errors->has('registration'))
            <div class="alert alert-danger">
                <i class="fas fa-fw fa-exclamation-triangle mr-1"></i>{{ $errors->first('registration') }}
            </div>
        @endif

        <div class="row">

            {{-- ===== Left: progress and steps ===== --}}
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="p-3">
                            <p class="text-muted small font-weight-bold mb-1">
                                Step {{ $step }} of {{ count($steps) }} · {{ $steps[$step][0] }}
                            </p>
                            <div class="progress progress-sm mb-0">
                                <div class="progress-bar bg-primary" role="progressbar"
                                    style="width: {{ ($step / count($steps)) * 100 }}%"
                                    aria-valuenow="{{ $step }}" aria-valuemin="1" aria-valuemax="{{ count($steps) }}"></div>
                            </div>
                        </div>

                        <div class="list-group list-group-flush">
                            @foreach ($steps as $number => [$label, $hint])
                                @php $isDone = $step > $number; $isCurrent = $step === $number; @endphp

                                @if ($isDone)
                                    <a href="#" wire:click.prevent="edit({{ $number }})"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                @else
                                    <div class="list-group-item d-flex align-items-center {{ $isCurrent ? 'active' : '' }}">
                                @endif
                                        <span class="badge badge-pill mr-3 p-2 {{ $isDone ? 'badge-success' : ($isCurrent ? 'badge-light text-primary' : 'badge-secondary') }}"
                                            style="min-width: 2rem;">
                                            @if ($isDone)<i class="fas fa-check"></i>@else{{ $number }}@endif
                                        </span>
                                        <span>
                                            <span class="d-block font-weight-bold">{{ $label }}</span>
                                            <small class="{{ $isCurrent ? '' : 'text-muted' }}">{{ $hint }}</small>
                                        </span>
                                @if ($isDone)
                                    </a>
                                @else
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Right: current step ===== --}}
            <div class="col-12 col-lg-8">
                <div class="card">

                    <div class="card-header d-flex justify-content-center align-items-center {{ config('adminlte.classes_auth_header', '') }}">
                        <a href="{{ route('home') }}" class="mr-2">
                            <img src="{{ asset(config('thha.logo_path', 'image/logo.jpg')) }}"
                                alt="{{ config('thha.hoa_short_name') }}"
                                class="elevation-2 rounded mr-2"
                                width="40"
                                height="40">
                        </a>
                        <h1 class="card-title text-secondary text-uppercase">Tradition Homes</h1>
                    </div>

                    <div class="card-body">

                        <h5 class="font-weight-bold text-primary mb-3">{{ $steps[$step][0] }}</h5>

                        {{-- Step 1 — Account details --}}
                        @if ($step === 1)
                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <x-adminlte-input name="email" type="email" label="{{ __('auth.email') }}"
                                        placeholder="you@example.com" wire:model.defer="email" igroup-size="sm" autofocus>
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                                        </x-slot>
                                    </x-adminlte-input>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <x-adminlte-input name="password" type="password" label="{{ __('auth.password') }}"
                                        placeholder="{{ __('auth.password_placeholder') }}"
                                        wire:model.defer="password" igroup-size="sm" autocomplete="new-password">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
                                        </x-slot>
                                    </x-adminlte-input>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <x-adminlte-input name="password_confirmation" type="password"
                                        label="{{ __('auth.password_confirmation') }}"
                                        placeholder="{{ __('auth.password_confirmation_placeholder') }}"
                                        wire:model.defer="password_confirmation" igroup-size="sm"
                                        autocomplete="new-password">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
                                        </x-slot>
                                    </x-adminlte-input>
                                </div>
                            </div>
                        @endif

                        {{-- Step 2 — Personal information --}}
                        @if ($step === 2)
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="form.surname" label="Surname" placeholder="Enter surname"
                                        wire:model.defer="form.surname" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="form.first_name" label="First Name" placeholder="Enter first name"
                                        wire:model.defer="form.first_name" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="form.middle_name" label="Middle Name" placeholder="Optional"
                                        wire:model.defer="form.middle_name" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="form.suffix" label="Suffix" placeholder="e.g., Jr., III"
                                        wire:model.defer="form.suffix" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-5">
                                    <x-adminlte-input name="form.birth_date" type="date" label="Birth Date"
                                        wire:model.defer="form.birth_date" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-md-7">
                                    <x-adminlte-input name="form.birth_place" label="Birth Place" placeholder="Enter birth place"
                                        wire:model.defer="form.birth_place" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-pin"></i></div></x-slot>
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
                                    <x-adminlte-select name="form.sex" label="Sex" wire:model.defer="form.sex" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-venus-mars"></i></div></x-slot>
                                        <option value="">Select sex</option>
                                        @foreach ($sexOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-5">
                                    <x-adminlte-select name="form.civil_status" label="Civil Status"
                                        wire:model="form.civil_status" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ring"></i></div></x-slot>
                                        <option value="">Select civil status</option>
                                        @foreach ($civilStatusOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                                @if (($form['civil_status'] ?? '') === \App\Enums\CivilStatus::MARRIED)
                                    <div class="col-12 col-md-7">
                                        <x-adminlte-input name="form.spouse_name" label="Spouse Name"
                                            placeholder="Required when married" wire:model.defer="form.spouse_name" igroup-size="sm">
                                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-heart"></i></div></x-slot>
                                        </x-adminlte-input>
                                    </div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <x-adminlte-input name="form.religion" label="Religion" placeholder="Optional"
                                        wire:model.defer="form.religion" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-church"></i></div></x-slot>
                                    </x-adminlte-input>
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
                                    <x-adminlte-input name="form.occupation" label="Occupation" placeholder="Optional"
                                        wire:model.defer="form.occupation" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-briefcase"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-adminlte-select name="form.educational_attainment" label="Educational Attainment"
                                        wire:model.defer="form.educational_attainment" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-graduation-cap"></i></div></x-slot>
                                        <option value="">Optional</option>
                                        @foreach ($educationalAttainmentOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="small font-weight-bold mb-1">Sectoral Tags</label>
                                <div class="row">
                                    @foreach ($sectorOptions as $value => $label)
                                        <div class="col-md-4 mb-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input"
                                                    id="register-sector-{{ $value }}" value="{{ $value }}"
                                                    wire:model.defer="form.sectors">
                                                <label class="custom-control-label" for="register-sector-{{ $value }}">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('form.sectors')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>
                        @endif

                        {{-- Step 3 — Address and household --}}
                        @if ($step === 3)
                            @php
                                $unitNamed = $property['block'] !== '' && $property['lot'] !== '';
                                $unitIsNew = $unitNamed && ($address['is_new'] ?? true);
                                $showHousehold = $address ? ! $address['is_occupied'] : $unitNamed;
                            @endphp

                            <div class="form-group">
                                <label>{{ __('auth.address_primary') }}</label>
                                <p class="form-control-plaintext border rounded bg-light px-2 py-1 mb-0 small">
                                    <i class="fas fa-fw fa-map-marker-alt mr-1"></i>{{ $primaryAddress ? $primaryAddress->full_address : '—' }}
                                </p>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <x-adminlte-input name="property.block" type="number" min="1"
                                        label="{{ __('auth.block') }}" placeholder="{{ __('auth.block_placeholder') }}"
                                        wire:model.lazy="property.block" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-th"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-6">
                                    <x-adminlte-input name="property.lot" type="number" min="1"
                                        label="{{ __('auth.lot') }}" placeholder="{{ __('auth.lot_placeholder') }}"
                                        wire:model.lazy="property.lot" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-th-large"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>

                                @if ($unitIsNew)
                                    <div class="col-6">
                                        <x-adminlte-input name="property.sqm" type="number" min="0" step="0.1"
                                            label="{{ __('auth.sqm') }}" placeholder="{{ __('auth.sqm_placeholder') }}"
                                            wire:model.defer="property.sqm" igroup-size="sm">
                                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ruler-combined"></i></div></x-slot>
                                        </x-adminlte-input>
                                    </div>
                                    <div class="col-6">
                                        <x-adminlte-select name="property.type" label="{{ __('auth.property_type') }}"
                                            wire:model.defer="property.type" igroup-size="sm">
                                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-home"></i></div></x-slot>
                                            <option value="">Select type</option>
                                            @foreach ($propertyTypeOptions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </x-adminlte-select>
                                    </div>
                                @endif

                                @if ($showHousehold)
                                    <div class="col-12 col-md-6">
                                        <x-adminlte-select name="household.type" label="Tenure Type"
                                            wire:model.defer="household.type" igroup-size="sm">
                                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-key"></i></div></x-slot>
                                            @foreach ($householdTypeOptions as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </x-adminlte-select>
                                    </div>
                                    <div class="col-12">
                                        <x-adminlte-textarea name="household.prev_address"
                                            label="{{ __('auth.household_prev_address') }}" rows="2" maxlength="500"
                                            placeholder="Optional" wire:model.defer="household.prev_address" igroup-size="sm">
                                            <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-map-signs"></i></div></x-slot>
                                        </x-adminlte-textarea>
                                    </div>
                                @endif
                            </div>

                            @if ($unitIsNew)
                                <div class="alert alert-info small py-2 mb-0">
                                    <i class="fas fa-fw fa-info-circle mr-1"></i>{{ __('auth.address_new_notice') }}
                                </div>
                            @elseif ($address)
                                <div class="alert {{ $address['is_occupied'] ? 'alert-warning' : 'alert-info' }} mb-0">
                                    <p class="font-weight-bold mb-1">{{ $address['address'] }}</p>

                                    @if ($address['is_occupied'])
                                        <p class="mb-2">{{ __('auth.address_occupied') }}</p>

                                        @if ($address['head_initials'])
                                            <p class="mb-2">{{ __('auth.address_head', ['name' => $address['head_initials']]) }}</p>
                                        @endif

                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="linkHousehold"
                                                wire:model.defer="link_household">
                                            <label class="custom-control-label" for="linkHousehold">
                                                {{ __('auth.address_link') }}
                                                <br><small>{{ __('auth.address_link_hint') }}</small>
                                            </label>
                                        </div>

                                        @error('link_household')
                                            <span class="d-block text-danger small mt-1">{{ $message }}</span>
                                        @enderror
                                    @else
                                        <p class="mb-0 small">{{ __('auth.address_free') }}</p>
                                    @endif
                                </div>
                            @endif
                        @endif

                        {{-- Step 4 — Valid ID --}}
                        @if ($step === 4)
                            <div class="custom-file" data-thha-id-input-wrapper>
                                <input type="file" accept="image/jpeg,image/png" capture="environment"
                                    class="custom-file-input @error('idFile') is-invalid @enderror" id="validId"
                                    wire:model="idFile" data-thha-id-input>
                                <label class="custom-file-label" for="validId">
                                    {{ $idFile ? $idFile->getClientOriginalName() : 'Choose a JPG or PNG image' }}
                                </label>
                            </div>

                            @error('idFile')
                                <span class="d-block text-danger small mt-1">{{ $message }}</span>
                            @enderror

                            <div class="mt-3 d-flex flex-wrap align-items-center">
                                <button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-2"
                                    data-toggle="modal" data-target="#thhaCameraModal" data-thha-camera-open>
                                    <i class="fas fa-fw fa-camera mr-1"></i>Use Camera
                                </button>

                                <span wire:loading wire:target="idFile" class="text-info small mb-2">
                                    <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>Uploading…
                                </span>
                            </div>

                            @if ($idFile)
                                <div class="mt-2">
                                    <img src="{{ $idFile->temporaryUrl() }}" alt="The ID you are about to submit"
                                        class="img-thumbnail" width="240">
                                </div>
                            @endif

                            {{-- Camera modal: plain getUserMedia + canvas, no extra package --}}
                            <div class="modal fade" id="thhaCameraModal" tabindex="-1" role="dialog"
                                aria-labelledby="thhaCameraModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="thhaCameraModalLabel">Capture your valid ID</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <video data-thha-camera-video autoplay playsinline
                                                class="w-100 bg-dark rounded"></video>
                                            <p class="text-danger small mb-0 mt-2 d-none" data-thha-camera-error>
                                                The camera is not available. Choose an image from your device instead.
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                                                Cancel
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm" data-thha-camera-capture>
                                                <i class="fas fa-fw fa-camera mr-1"></i>Capture
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Step 5 — Review and consent --}}
                        @if ($step === 5)
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="w-25">
                                                Account
                                                <a href="#" wire:click.prevent="edit(1)" class="small font-weight-normal ml-1">Edit</a>
                                            </th>
                                            <td>{{ $accountEmail }}</td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Personal information
                                                <a href="#" wire:click.prevent="edit(2)" class="small font-weight-normal ml-1">Edit</a>
                                            </th>
                                            <td>
                                                {{ trim(collect([$form['first_name'], $form['middle_name'], $form['surname']])->filter()->implode(' ')) }}
                                                {{ $form['suffix'] ? ', ' . $form['suffix'] : '' }}<br>
                                                <span class="text-muted small">
                                                    Born {{ $form['birth_date'] }} in {{ $form['birth_place'] }} ·
                                                    {{ $sexOptions[$form['sex']] ?? '—' }} ·
                                                    {{ $civilStatusOptions[$form['civil_status']] ?? '—' }} ·
                                                    {{ $form['mobile_no'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Household and property
                                                <a href="#" wire:click.prevent="edit(3)" class="small font-weight-normal ml-1">Edit</a>
                                            </th>
                                            <td>
                                                {{ $address['address'] ?? '—' }}
                                                <span class="text-muted small d-block">
                                                    @if ($address && $address['is_occupied'])
                                                        {{ __('auth.review_linked') }}
                                                    @elseif ($address && $address['is_new'])
                                                        {{ __('auth.review_new_unit') }}
                                                    @else
                                                        {{ __('auth.review_head') }}
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Valid ID
                                                <a href="#" wire:click.prevent="edit(4)" class="small font-weight-normal ml-1">Edit</a>
                                            </th>
                                            <td>{{ $idFile ? $idFile->getClientOriginalName() : '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <hr>

                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="consentTerms"
                                    wire:model.defer="consent.terms">
                                <label class="custom-control-label" for="consentTerms">
                                    {{ __('auth.terms.accept_prefix') }}
                                    <a href="#" data-toggle="modal" data-target="#registrationTermsModal">
                                        {{ __('auth.terms.accept_link') }}
                                    </a>.
                                </label>
                            </div>
                            @error('consent.terms')
                                <span class="d-block text-danger small mb-2">{{ $message }}</span>
                            @enderror

                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="consentPrivacy"
                                    wire:model.defer="consent.privacy">
                                <label class="custom-control-label" for="consentPrivacy">
                                    {{ __('auth.privacy.accept_prefix') }}
                                    <a href="#" data-toggle="modal" data-target="#registrationPrivacyModal">
                                        {{ __('auth.privacy.accept_link') }}
                                    </a>.
                                </label>
                            </div>
                            @error('consent.privacy')
                                <span class="d-block text-danger small mb-2">{{ $message }}</span>
                            @enderror
                        @endif

                        {{-- ===== Navigation (bottom right of the body) ===== --}}
                        <div class="d-flex justify-content-end align-items-center mt-4">
                            @if ($step > 1)
                                <button type="button" class="btn btn-outline-secondary btn-sm mr-2" wire:click="back"
                                    wire:loading.attr="disabled">
                                    <i class="fas fa-fw fa-arrow-left mr-1"></i>Back
                                </button>
                            @endif

                            @if ($step < 5)
                                <button type="button" class="btn btn-primary btn-sm" wire:click="next"
                                    wire:loading.attr="disabled" wire:target="next,idFile">
                                    Continue<i class="fas fa-fw fa-arrow-right ml-1"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-sm" wire:click="submit"
                                    wire:loading.attr="disabled" wire:target="submit" {{ $processing ? 'disabled' : '' }}>
                                    <i class="fas fa-fw fa-paper-plane mr-1"></i>Submit Registration
                                </button>
                                <span wire:loading wire:target="submit" class="text-info small mr-2">
                                    <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>Submitting…
                                </span>
                            @endif
                        </div>

                    </div>

                    {{-- ===== Footer ===== --}}
                    <div class="card-footer text-center">
                        <span class="text-muted small">
                            Already have an account?
                            <a href="{{ route('login') }}" class="font-weight-bold">Sign in</a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Terms modal ===== --}}
    <div class="modal fade" id="registrationTermsModal" tabindex="-1" role="dialog"
        aria-labelledby="registrationTermsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationTermsModalLabel">{{ __('auth.terms.title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('auth.terms.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach (__('auth.terms.sections') as $section)
                        <p><strong>{{ $section['heading'] }}.</strong> {{ $section['body'] }}</p>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                        {{ __('auth.terms.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Data privacy modal ===== --}}
    <div class="modal fade" id="registrationPrivacyModal" tabindex="-1" role="dialog"
        aria-labelledby="registrationPrivacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationPrivacyModalLabel">{{ __('auth.privacy.title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('auth.privacy.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach (__('auth.privacy.sections') as $section)
                        <p><strong>{{ $section['heading'] }}.</strong> {{ $section['body'] }}</p>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                        {{ __('auth.privacy.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
