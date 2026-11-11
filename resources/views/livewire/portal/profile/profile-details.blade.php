{{-- The same layout the office's own Profile Settings page uses: the cards
     stack in one full-width column, and each card's content sits in the left
     half from large sizes up, with its Save button below the fields. --}}
<div class="row">
    <div class="col-md-12">

        {{-- ================================================================= --}}
        {{-- WHAT THE HOMEOWNER KEEPS TO THEMSELVES                            --}}
        {{-- Photo and email address. The contact number is on the record the  --}}
        {{-- association keeps, so it is edited on Personal Information.       --}}
        {{-- ================================================================= --}}
        <x-adminlte-card title="My Details" icon="fas fa-fw fa-id-badge">

            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-12 col-md-6">

                        {{-- Profile Photo --}}
                        <div class="d-flex align-items-center">
                            <div class="mr-4 text-center">
                                <img src="{{ $photo ? $photo->temporaryUrl() : ($photoUrl ?: asset('image/user.png')) }}"
                                    class="img-circle elevation-2" alt="{{ $account->name }}"
                                    style="width:100px;height:100px;object-fit:cover;">
                            </div>
                            <div>
                                <label class="font-weight-bold">Profile Photo</label>
                                <p class="text-muted small mb-2">
                                    Upload a new photo. JPG, PNG, or GIF. Max 2MB.
                                    @unless ($resident && $resident->profile_photo_path)
                                        An officer can also take one when you visit the office.
                                    @endunless
                                </p>

                                <x-adminlte-input-file name="photo" label="Choose file..."
                                    :placeholder="$photo ? $photo->getClientOriginalName() : 'photo.png'"
                                    wire:model="photo" legend="Browse" igroupSize="sm">
                                    @if ($resident && $resident->profile_photo_path)
                                        <x-slot name="prependSlot">
                                            <x-adminlte-button type="button" theme="danger"
                                                icon="fas fa-fw fa-trash-alt" title="Remove photo"
                                                wire:click="removePhoto" wire:loading.attr="disabled" />
                                        </x-slot>
                                    @endif
                                </x-adminlte-input-file>

                                <div wire:loading wire:target="photo" class="mt-1">
                                    <small class="text-info"><i class="fas fa-spinner fa-spin"></i> Uploading...</small>
                                </div>
                            </div>
                        </div>

                        {{-- Email address --}}
                        <x-adminlte-input name="email" label="Email Address" type="email"
                            placeholder="your@email.com" wire:model="email" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-envelope"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                    </div>
                </div>

                <div class="text-right">
                    <x-adminlte-button label="Save Changes" type="submit" theme="primary"
                        icon="fas fa-fw fa-save" class="mt-3" wire:loading.attr="disabled" />
                </div>
            </form>

        </x-adminlte-card>

        {{-- ================================================================= --}}
        {{-- PASSWORD                                                          --}}
        {{-- ================================================================= --}}
        <x-adminlte-card title="Change Password" icon="fas fa-fw fa-lock">
            <p class="text-muted small mb-3">
                Use at least 8 characters, and something you do not use anywhere else.
            </p>

            <form wire:submit.prevent="updatePassword">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <x-adminlte-input name="current_password" label="Current Password" type="password"
                            placeholder="Enter current password" wire:model="current_password" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-key"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="new_password" label="New Password" type="password"
                            placeholder="Enter new password" wire:model="new_password" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-lock"></i></div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="new_password_confirmation" label="Confirm New Password"
                            type="password" placeholder="Confirm new password"
                            wire:model="new_password_confirmation" igroup-size="sm">
                            <x-slot name="prependSlot">
                                <div class="input-group-text"><i class="fas fa-fw fa-check-circle"></i></div>
                            </x-slot>
                        </x-adminlte-input>
                    </div>
                </div>

                <div class="text-right">
                    <x-adminlte-button label="Update Password" type="submit" theme="primary"
                        icon="fas fa-fw fa-save" class="mt-3" wire:loading.attr="disabled" />
                </div>
            </form>
        </x-adminlte-card>

    </div>
</div>
