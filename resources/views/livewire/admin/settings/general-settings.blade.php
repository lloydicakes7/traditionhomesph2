<div class="row">
    {{-- ================================================================ --}}
    {{-- LEFT COLUMN (8 cols on large, full on small)                     --}}
    {{-- ================================================================ --}}
    <div class="col-12 col-lg-8">

        {{-- ================================================================ --}}
        {{-- CARD 1: THHA INFORMATION                                         --}}
        {{-- ================================================================ --}}
        <x-adminlte-card title="THHA Information" icon="fas fa-fw fa-building">
            <form wire:submit.prevent="saveHoaInfo">
                {{-- Logo --}}
                <div class="row">
                    <div class="col-12 col-md-8">

                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center mb-4">
                            <div class="mr-0 mr-sm-4 mb-3 mb-sm-0 text-center" style="min-width:120px;">
                                @if ($logo)
                                    <img src="{{ $logo->temporaryUrl() }}"
                                        class="rounded elevation-2"
                                        style="width:100px;height:100px;object-fit:cover;"
                                        alt="Logo Preview">
                                @elseif ($logo_path && Storage::disk('public')->exists($logo_path))
                                    <img src="{{ Storage::disk('public')->url($logo_path) }}"
                                        class="rounded elevation-2"
                                        style="width:100px;height:100px;object-fit:cover;"
                                        alt="Logo">
                                @else
                                    <img src="{{ asset('image/logo.jpg') }}"
                                        class="rounded elevation-2"
                                        style="width:100px;height:100px;object-fit:cover;"
                                        alt="THHA Logo">
                                @endif
                            </div>
                            <div class="flex-grow-1 w-100">
                                <label class="font-weight-bold">HOA Logo</label>
                                <p class="text-muted small mb-2">Upload your association logo. JPG, PNG, WEBP, or GIF. Max 2MB.</p>
                                <x-adminlte-input-file name="logo" label="Choose file..."
                                    :placeholder="$logo ? $logo->getClientOriginalName() : 'thha-logo.png'"
                                    wire:model="logo" legend="Browse" igroupSize="sm">
                                    @if ($logo_path)
                                        <x-slot name="prependSlot">
                                            <x-adminlte-button
                                                type="button"
                                                theme="danger"
                                                icon="fas fa-fw fa-trash-alt"
                                                wire:click="removeLogo"
                                                wire:loading.attr="disabled"
                                            />
                                        </x-slot>
                                    @endif
                                </x-adminlte-input-file>
                                <div wire:loading wire:target="logo" class="mt-1">
                                    <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                                </div>
                            </div>
                        </div>

                        {{-- HOA Name --}}
                        <x-adminlte-input name="hoa_name" label="HOA Name"
                            placeholder="e.g., Tradition Homes Phase 2 Homeowners Association, Inc"
                            wire:model.defer="hoa_name" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-home"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        {{-- Address — the location the public site maps --}}
                        <x-adminlte-input name="address" label="Address"
                            placeholder="e.g., Maple Street, Tradition Homes Subdivision"
                            wire:model.defer="address" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-map-marker-alt"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        {{-- Website --}}
                        <x-adminlte-input name="website" label="Website" type="url"
                            placeholder="e.g., https://traditionhomeshoa.com"
                            wire:model.defer="website" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-globe"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                    </div>
                </div>

                <div class="text-right">
                    <x-adminlte-button label="Save HOA Information" type="submit" theme="primary"
                        icon="fas fa-fw fa-save" class="mt-3" />
                </div>
            </form>
        </x-adminlte-card>

        {{-- ================================================================ --}}
        {{-- CARD 2: CONTACT INFORMATION                                       --}}
        {{-- ================================================================ --}}
        <x-adminlte-card title="Contact Information" icon="fas fa-fw fa-address-card">
            <form wire:submit.prevent="saveContact">
                <div class="row">
                    <div class="col-12 col-md-8">
                        {{-- Email --}}
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-fw fa-envelope"></i></span>
                                </div>
                                <input type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="e.g., admin@traditionhomeshoa.com"
                                    wire:model.defer="email">
                            </div>
                        </div>

                        {{-- Working Hours --}}
                        <x-adminlte-input name="working_hours" label="Working Hours"
                            placeholder="e.g., Monday – Friday, 8:00 AM – 5:00 PM"
                            wire:model.defer="working_hours" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-clock"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        {{-- Contact Number --}}
                        <x-adminlte-input name="contact_number" label="Contact Number"
                            placeholder="e.g., +63 912 345 6789"
                            wire:model.defer="contact_number" igroup-size="sm" enable-old-support>
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-phone"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="text-right">
                    <x-adminlte-button label="Save Contact Information" type="submit" theme="primary"
                        icon="fas fa-fw fa-save" class="mt-3" />
                </div>
            </form>
        </x-adminlte-card>

    </div>
    {{-- END left column --}}

    {{-- ================================================================ --}}
    {{-- RIGHT COLUMN                                                     --}}
    {{-- ================================================================ --}}
    <div class="col-12 col-lg-4">

        {{-- ================================================================ --}}
        {{-- CARD 4: SYSTEM INFORMATION                                       --}}
        {{-- ================================================================ --}}
        <x-adminlte-card title="System Information" icon="fas fa-fw fa-server" theme="primary" theme-mode="outline">
            <dl class="row mb-0">
                <dt class="col-5 col-sm-4 text-muted small font-weight-normal">App Name</dt>
                <dd class="col-7 col-sm-8 small font-weight-bold">{{ $appName }}</dd>

                <dt class="col-5 col-sm-4 text-muted small font-weight-normal">App URL</dt>
                <dd class="col-7 col-sm-8 small font-weight-bold text-truncate">{{ $appUrl }}</dd>

                <dt class="col-5 col-sm-4 text-muted small font-weight-normal">Environment</dt>
                <dd class="col-7 col-sm-8">
                    <span class="badge badge-{{ $appEnv === 'production' ? 'danger' : 'info' }}">
                        {{ ucfirst($appEnv) }}
                    </span>
                </dd>

                <dt class="col-5 col-sm-4 text-muted small font-weight-normal">Debug Mode</dt>
                <dd class="col-7 col-sm-8">
                    @if ($appDebug)
                        <span class="badge badge-warning">Enabled</span>
                    @else
                        <span class="badge badge-success">Disabled</span>
                    @endif
                </dd>

                <dt class="col-5 col-sm-4 text-muted small font-weight-normal">Laravel</dt>
                <dd class="col-7 col-sm-8"><code>{{ $laravelVersion }}</code></dd>

                <dt class="col-5 col-sm-4 text-muted small font-weight-normal">PHP</dt>
                <dd class="col-7 col-sm-8"><code>{{ $phpVersion }}</code></dd>

                <dt class="col-5 col-sm-4 text-muted small font-weight-normal">Web Server</dt>
                <dd class="col-7 col-sm-8 small text-muted text-truncate">{{ $webhost }}</dd>
            </dl>
        </x-adminlte-card>

    </div>
    {{-- END right column --}}
</div>
