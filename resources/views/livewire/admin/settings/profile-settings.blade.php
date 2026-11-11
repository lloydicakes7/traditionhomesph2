<div class="row">
    <div class="col-md-12">

        {{-- ================================================================ --}}
        {{-- SECTION 1: PERSONAL INFORMATION --}}
        {{-- ================================================================ --}}
        <x-adminlte-card title="Personal Information" icon="fas fa-fw fa-user" >
            <form wire:submit.prevent="updatePersonalInfo">
                <div class="row">
                    <div class="col-12 col-md-6">
                        {{-- Profile Photo --}}
                        <div class="d-flex align-items-center">
                            <div class="mr-4 text-center">
                                @if ($profile_photo)
                                    <img src="{{ $profile_photo->temporaryUrl() }}"
                                        class="img-circle elevation-2"
                                        style="width:100px;height:100px;object-fit:cover;"
                                        alt="Preview">
                                @elseif (Auth::user()->adminlte_image())
                                    <img src="{{ Auth::user()->adminlte_image() }}"
                                        class="img-circle elevation-2"
                                        style="width:100px;height:100px;object-fit:cover;"
                                        alt="Current Photo">
                                @else
                                    <img src="{{ asset('image/user.png') }}"
                                        class="img-circle elevation-2"
                                        style="width:100px;height:100px;object-fit:cover;"
                                        alt="User Empty">
                                @endif
                            </div>
                            <div>
                                <label class="font-weight-bold">Profile Photo</label>
                                <p class="text-muted small mb-2">Upload a new photo. JPG, PNG, WEBP, or GIF. Max 1MB.</p>
                                <x-adminlte-input-file name="profile_photo" label="Choose file..."
                                    :placeholder="$profile_photo ? $profile_photo->getClientOriginalName() : 'profile.png'"
                                    wire:model="profile_photo" legend="Browse" igroupSize="sm">
                                    @if (Auth::user()->profile_photo_path)
                                        <x-slot name="prependSlot">
                                            <x-adminlte-button
                                                type="button"
                                                theme="danger"
                                                icon="fas fa-fw fa-trash-alt"
                                                wire:click="removePhoto"
                                                wire:loading.attr="disabled"
                                            />
                                        </x-slot>
                                    @endif
                                </x-adminlte-input-file>
                                <div wire:loading wire:target="profile_photo" class="mt-1">
                                    <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                                </div>
                            </div>
                        </div>

                        {{-- Name --}}
                        <x-adminlte-input name="name" label="Full Name" placeholder="Your full name"
                            wire:model.defer="name" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-user"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        {{-- Email --}}
                        <x-adminlte-input name="email" label="Email Address" placeholder="your@email.com"
                            wire:model.defer="email" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="text-right">
                    <x-adminlte-button label="Save Changes" type="submit" theme="primary"
                    icon="fas fa-fw fa-save" class="mt-3"/>
                </div>
            </form>
        </x-adminlte-card>

        {{-- ================================================================ --}}
        {{-- SECTION 2: CHANGE PASSWORD --}}
        {{-- ================================================================ --}}
        <x-adminlte-card title="Change Password" icon="fas fa-fw fa-lock">
            <p class="text-muted small mb-3">
                Ensure your account is secure by using a strong password with at least 8 characters.
            </p>

            <form wire:submit.prevent="updatePassword">
                <div class="row">
                    <div class="col-12 col-md-6">
                        {{-- Current Password --}}
                        <x-adminlte-input name="current_password" label="Current Password"
                                        type="password" placeholder="Enter current password"
                                        wire:model.defer="current_password" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-key"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        {{-- New Password --}}
                        <x-adminlte-input name="new_password" label="New Password"
                                          type="password" placeholder="Enter new password"
                                          wire:model.defer="new_password" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        {{-- Confirm New Password --}}
                        <x-adminlte-input name="new_password_confirmation" label="Confirm New Password"
                                          type="password" placeholder="Confirm new password"
                                          wire:model.defer="new_password_confirmation" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-check-circle"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="text-right">
                    <x-adminlte-button label="Update Password" type="submit" theme="primary"
                                       icon="fas fa-fw fa-save" class="mt-3"/>
                </div>
            </form>
        </x-adminlte-card>

        {{-- ================================================================ --}}
        {{-- SECTION 3: DELETE ACCOUNT                                        --}}
        {{-- ================================================================ --}}

        @unless(Auth::user()->hasRole(\App\Enums\DefaultRoleSlug::ADMINISTRATOR()))
            <x-adminlte-card title="Delete Account" icon="fas fa-fw fa-exclamation-triangle" theme="danger" theme-mode="outline">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <p class="font-weight-bold text-danger">Danger Zone</p>
                        <p class="text-muted small">
                            Once you delete your account, there is no going back. Please be certain.
                            All of your data will be permanently removed.
                        </p>
                    </div>
                    <div class="col-12 d-flex align-items-center justify-content-end">
                        <x-adminlte-button label="Delete My Account" theme="danger"
                                        icon="fas fa-fw fa-trash"
                                        data-toggle="modal" data-target="#deleteAccountModal"/>
                    </div>
                </div>
            </x-adminlte-card>
        @endunless

    </div>

</div>
