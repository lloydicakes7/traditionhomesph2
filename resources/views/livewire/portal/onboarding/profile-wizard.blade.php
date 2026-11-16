{{-- ===================================================================== --}}
{{-- COMPLETE YOUR PROFILE                                                 --}}
{{-- ===================================================================== --}}

@php
    $steps = [
        1 => ['Personal information',   'Who you are',                     'fas fa-fw fa-user'],
        2 => ['Address and household',   'Where you live',                  'fas fa-fw fa-map-marker-alt'],
        3 => ['Valid ID',                'The document the office checks',  'fas fa-fw fa-id-card'],
    ];

    $total = count($steps);
    $progress = (int) round(($step / $total) * 100);
    $showSpouse = ($form['civil_status'] ?? '') === \App\Enums\CivilStatus::MARRIED;
@endphp

<div>

    {{-- ===== Progress across the whole page ===== --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="font-weight-bold text-primary">
                    <i class="fas fa-fw fa-user-edit mr-1"></i>Complete your profile
                </span>
                <span class="small text-muted">Step {{ $step }} of {{ $total }}</span>
            </div>

            <div class="progress" style="height: 10px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%"
                    aria-valuenow="{{ $step }}" aria-valuemin="1" aria-valuemax="{{ $total }}"></div>
            </div>

            <div class="d-flex justify-content-between mt-2 small">
                @foreach ($steps as $number => [$label])
                    <span class="{{ $number === $step ? 'font-weight-bold text-primary' : ($number < $step ? 'text-success' : 'text-muted') }}">
                        @if ($number < $step)
                            <i class="fas fa-fw fa-check mr-1"></i>
                        @endif
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ===== Left: the three sections, one open at a time ===== --}}
        <div class="col-12 col-lg-8">

            {{-- ===== Where the profile stands ===== --}}
            @if ($submitted)
                <div class="alert alert-success">
                    <i class="fas fa-fw fa-check-circle mr-1"></i>{{ __('auth.onboarding.submitted') }}
                </div>
            @elseif ($account->profile_status->isSubmitted())
                <div class="alert alert-info">
                    <i class="fas fa-fw fa-clock mr-1"></i>{{ __('auth.onboarding.waiting') }}
                </div>
            @elseif ($account->profile_status->isRejected())
                <div class="alert alert-danger">
                    <i class="fas fa-fw fa-exclamation-triangle mr-1"></i>
                    <strong>{{ __('auth.onboarding.rejected') }}</strong>
                    <p class="mb-0 mt-1">{{ $account->profile_rejection_note }}</p>
                </div>
            @endif

            @if ($errors->has('profile'))
                <div class="alert alert-danger">
                    <i class="fas fa-fw fa-exclamation-triangle mr-1"></i>{{ $errors->first('profile') }}
                </div>
            @endif

            @foreach ($steps as $number => [$label, $hint, $icon])
                <div class="card mb-3 {{ $number === $step ? 'border-primary' : '' }}">
                    <div class="card-header p-0">
                        <button type="button" wire:click="open({{ $number }})"
                            class="btn btn-link btn-block text-left text-decoration-none d-flex align-items-center p-3">
                            <span class="badge badge-pill mr-3 p-2 {{ $number < $step ? 'badge-success' : ($number === $step ? 'badge-primary' : 'badge-secondary') }}"
                                style="min-width: 2rem;">
                                @if ($number < $step)
                                    <i class="fas fa-fw fa-check"></i>
                                @else
                                    {{ $number }}
                                @endif
                            </span>
                            <span class="flex-grow-1">
                                <span class="d-block font-weight-bold text-body">{{ $label }}</span>
                                <small class="text-muted">{{ $hint }}</small>
                            </span>
                            <i class="fas fa-fw {{ $number === $step ? 'fa-chevron-up' : 'fa-chevron-down' }} text-muted"></i>
                        </button>
                    </div>

                    <div class="collapse {{ $number === $step ? 'show' : '' }}">

                        <div class="card-body">

                            {{-- 1 — Personal information --}}
                            @if ($number === 1)
                                @include('partials.personal-fields', ['prefix' => 'form'])
                            @endif

                            {{-- 2 — Address and household --}}
                            @if ($number === 2)
                                @php
                                    $unitNamed = (int) ($property['block'] ?? 0) > 0 && (int) ($property['lot'] ?? 0) > 0;
                                    $note = match (true) {
                                        ! $unitNamed       => __('households.note.name_unit'),
                                        $unit === null     => __('households.note.new_unit'),
                                        $unit['is_occupied'] => __('households.note.occupied'),
                                        default            => __('households.note.vacant'),
                                    };
                                @endphp

                                @include('partials.household-unit-fields', [
                                    'address' => $primaryAddress,
                                    'note' => $note,
                                ])
                            @endif

                            {{-- 3 — Valid ID --}}
                            @if ($number === 3)
                                <div class="custom-file">
                                    <input type="file" accept="image/jpeg,image/png"
                                        class="custom-file-input @error('idFile') is-invalid @enderror" id="onboardingValidId"
                                        wire:model="idFile" data-thha-id-input>
                                    <label class="custom-file-label" for="onboardingValidId">
                                        {{ $idFile ? $idFile->getClientOriginalName() : __('auth.onboarding.choose_id') }}
                                    </label>
                                </div>

                                @error('idFile')
                                    <span class="d-block text-danger small mt-1">{{ $message }}</span>
                                @enderror

                                <div class="d-flex flex-wrap align-items-center mt-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm mr-2"
                                        data-toggle="modal" data-target="#thhaCameraModal" data-thha-camera-open>
                                        <i class="fas fa-fw fa-camera mr-1"></i>Use Camera
                                    </button>

                                    <span wire:loading wire:target="idFile" class="text-info small">
                                        <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>Uploading…
                                    </span>
                                </div>

                                @if ($idFile)
                                    <div class="mt-2">
                                        <img src="{{ $idFile->temporaryUrl() }}" alt="The ID you are about to submit"
                                            class="img-thumbnail" width="240">
                                    </div>
                                @elseif ($resident && $resident->valid_id_path)
                                    <p class="text-muted small mt-2 mb-0">
                                        <i class="fas fa-fw fa-check-circle mr-1"></i>{{ __('auth.onboarding.id_on_file') }}
                                    </p>
                                @endif

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

                        </div>

                        <div class="card-footer d-flex justify-content-end align-items-center">
                            @if ($number > 1)
                                <button type="button" class="btn btn-outline-secondary btn-sm mr-2" wire:click="back"
                                    wire:loading.attr="disabled">
                                    <i class="fas fa-fw fa-arrow-left mr-1"></i>Back
                                </button>
                            @endif

                            @if ($number < $total)
                                <button type="button" class="btn btn-primary btn-sm" wire:click="next"
                                    wire:loading.attr="disabled" wire:target="next">
                                    Continue<i class="fas fa-fw fa-arrow-right ml-1"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-sm" wire:click="submit"
                                    wire:loading.attr="disabled" wire:target="submit">
                                    <i class="fas fa-fw fa-paper-plane mr-1"></i>Submit for Verification
                                </button>
                                <span wire:loading wire:target="submit" class="text-info small ml-2">
                                    <i class="fas fa-fw fa-spinner fa-spin mr-1"></i>Submitting…
                                </span>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===== Right: what the account can and cannot use yet ===== --}}
        <div class="col-12 col-lg-4">

            <div class="card mb-3">
                <div class="card-body">
                    <p class="small text-muted text-uppercase font-weight-bold mb-1">Your profile</p>
                    <span class="badge badge-{{ $account->profile_status->badgeTheme() }}">
                        {{ $account->profile_status->label() }}
                    </span>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header font-weight-bold">
                    <i class="fas fa-fw fa-check-circle text-success mr-1"></i>{{ __('auth.onboarding.available_title') }}
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        @foreach (__('auth.onboarding.available') as $item)
                            <li class="mb-1"><i class="fas fa-fw fa-check text-success mr-1"></i>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header font-weight-bold">
                    <i class="fas fa-fw fa-lock text-muted mr-1"></i>{{ __('auth.onboarding.locked_title') }}
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        @foreach (__('auth.onboarding.locked') as $item)
                            <li class="mb-1 text-muted"><i class="fas fa-fw fa-ban mr-1"></i>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>

    </div>
</div>
