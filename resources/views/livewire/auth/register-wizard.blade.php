{{-- ===================================================================== --}}
{{-- Tradition Homes REGISTRATION                                          --}}
{{-- ===================================================================== --}}

@php
    $steps = [
        1 => ['Personal information', 'Your personal details'],
        2 => ['Account',              'Your email and password'],
        3 => ['Review and consent',   'Check and submit'],
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
                        <i class="fas fa-fw fa-envelope-open-text mr-1"></i>Verify your email
                    </h5>
                    <p class="mb-3">{{ __('auth.registered_verify_body', ['email' => $accountEmail]) }}</p>
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

                        {{-- Step 1: Personal information --}}
                        @if ($step === 1)
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="form.surname" label="Surname *" placeholder="Enter surname"
                                        maxlength="255" data-thha-uppercase wire:model.defer="form.surname" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="form.first_name" label="First Name *" placeholder="Enter first name"
                                        maxlength="255" data-thha-uppercase wire:model.defer="form.first_name" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-input name="form.middle_name" label="Middle Name" placeholder="Optional"
                                        maxlength="255" data-thha-uppercase wire:model.defer="form.middle_name" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <x-adminlte-select class="text-uppercase" name="form.suffix" label="Suffix"
                                        wire:model.defer="form.suffix" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-user"></i></div></x-slot>
                                        <option value="">Select</option>
                                        @foreach ($suffixOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <x-adminlte-input name="form.birth_date" type="date" label="Birth Date *"
                                        wire:model.defer="form.birth_date" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-calendar"></i></div></x-slot>
                                    </x-adminlte-input>
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-adminlte-input-contact name="form.mobile_no" label="Mobile No. *"
                                        wire:model.defer="form.mobile_no" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-mobile-alt"></i></div></x-slot>
                                    </x-adminlte-input-contact>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <x-adminlte-select class="text-uppercase" name="form.sex" label="Sex *" wire:model.defer="form.sex" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-venus-mars"></i></div></x-slot>
                                        <option value="">Select sex</option>
                                        @foreach ($sexOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <x-adminlte-select class="text-uppercase" name="form.civil_status" label="Civil Status *"
                                        wire:model.defer="form.civil_status" igroup-size="sm">
                                        <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-fw fa-ring"></i></div></x-slot>
                                        <option value="">Select civil status</option>
                                        @foreach ($civilStatusOptions as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </x-adminlte-select>
                                </div>
                            </div>
                        @endif

                        {{-- Step 2: Account --}}
                        @if ($step === 2)
                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <x-adminlte-input name="email" type="email" label="{{ __('auth.email') }} *"
                                        placeholder="you@example.com" maxlength="255"
                                        wire:model.defer="email" igroup-size="sm" autofocus>
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                                        </x-slot>
                                    </x-adminlte-input>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <x-adminlte-input-password name="password" label="{{ __('auth.password') }} *"
                                        placeholder="{{ __('auth.password_placeholder') }}"
                                        wire:model.defer="password" igroup-size="sm" autocomplete="new-password">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
                                        </x-slot>
                                    </x-adminlte-input-password>

                                    <ul class="small text-muted mt-n2 mb-3 pl-4">
                                        @foreach (__('auth.password_requirements') as $requirement)
                                            <li>{{ $requirement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-8">
                                    <x-adminlte-input-password name="password_confirmation"
                                        label="{{ __('auth.password_confirmation') }} *"
                                        placeholder="{{ __('auth.password_confirmation_placeholder') }}"
                                        wire:model.defer="password_confirmation" igroup-size="sm"
                                        autocomplete="new-password">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
                                        </x-slot>
                                    </x-adminlte-input-password>
                                </div>
                            </div>
                        @endif

                        {{-- Step 3: Review and consent --}}
                        @if ($step === 3)
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="w-25">
                                                Personal information
                                                <a href="#" wire:click.prevent="edit(1)" class="small font-weight-normal ml-1">Edit</a>
                                            </th>
                                            <td>
                                                {{ trim(collect([$form['first_name'], $form['middle_name'], $form['surname']])->filter()->implode(' ')) }}
                                                {{ $form['suffix'] ? ', ' . $form['suffix'] : '' }}<br>
                                                <span class="text-muted small">
                                                    Born in {{ $form['birth_date'] }} ·
                                                    {{ $sexOptions[$form['sex']] ?? 'Not provided' }} ·
                                                    {{ $civilStatusOptions[$form['civil_status']] ?? 'Not provided' }} ·
                                                    {{ $form['mobile_no'] }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>
                                                Account
                                                <a href="#" wire:click.prevent="edit(2)" class="small font-weight-normal ml-1">Edit</a>
                                            </th>
                                            <td>{{ $accountEmail }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="custom-control custom-checkbox mt-4 mb-2">
                                <input type="checkbox" class="custom-control-input" id="consent"
                                    wire:model.defer="consent">
                                <label class="custom-control-label" for="consent">
                                    {{ __('auth.consent.accept_prefix') }}
                                    <a href="#" data-toggle="modal" data-target="#registrationConsentModal">
                                        {{ __('auth.consent.accept_link') }}
                                    </a>.
                                </label>
                            </div>
                            @error('consent')
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

                            @if ($step < 3)
                                <button type="button" class="btn btn-primary btn-sm" wire:click="next"
                                    wire:loading.attr="disabled" wire:target="next">
                                    Continue<i class="fas fa-fw fa-arrow-right ml-1"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-sm" wire:click="submit"
                                    wire:loading.attr="disabled" wire:target="submit" {{ $processing ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="submit">
                                        <i class="fas fa-fw fa-paper-plane mr-1"></i>Submit Registration
                                    </span>
                                    <span wire:loading wire:target="submit">
                                        <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>Submitting…
                                    </span>
                                </button>
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

    {{-- ===== Consent modal ===== --}}
    <div class="modal fade" id="registrationConsentModal" tabindex="-1" role="dialog"
        aria-labelledby="registrationConsentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationConsentModalLabel">{{ __('auth.consent.title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('auth.consent.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach (__('auth.consent.paragraphs') as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">
                        {{ __('auth.consent.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
