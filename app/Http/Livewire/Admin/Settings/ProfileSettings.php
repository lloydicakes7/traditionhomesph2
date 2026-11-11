<?php

namespace App\Http\Livewire\Admin\Settings;

use App\Actions\Settings\Profile\ChangeAdminPassword;
use App\Actions\Settings\Profile\RemoveAdminProfilePhoto;
use App\Actions\Settings\Profile\UpdateAdminProfile;
use App\Http\Livewire\AdminComponent;
use App\Queries\Settings\ProfileSettingsQuery;
use App\Rules\PasswordPolicy;
use Livewire\WithFileUploads;

class ProfileSettings extends AdminComponent
{
    use WithFileUploads;

    // Personal Information
    public $name;

    public $email;

    public $profile_photo;

    // Change Password
    public $current_password;

    public $new_password;

    public $new_password_confirmation;

    /** @var ProfileSettingsQuery */
    private $query;

    /** @var UpdateAdminProfile */
    private $updateProfile;

    /** @var RemoveAdminProfilePhoto */
    private $removeProfilePhoto;

    /** @var ChangeAdminPassword */
    private $changePassword;

    public function boot(
        ProfileSettingsQuery $query,
        UpdateAdminProfile $updateProfile,
        RemoveAdminProfilePhoto $removeProfilePhoto,
        ChangeAdminPassword $changePassword
    ) {
        $this->query = $query;
        $this->updateProfile = $updateProfile;
        $this->removeProfilePhoto = $removeProfilePhoto;
        $this->changePassword = $changePassword;
    }

    /**
     * Validation rules for personal information update.
     *
     * @return array
     */
    protected function rulesForPersonalInfo()
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:admins,email,' . auth('web')->id()],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:1024'],
        ];
    }

    /**
     * Validation rules for password change.
     *
     * @return array
     */
    protected function rulesForPassword()
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'confirmed', new PasswordPolicy],
        ];
    }

    public function mount()
    {
        $profile = $this->query->form(auth('web')->user());

        $this->name = $profile['name'];
        $this->email = $profile['email'];
    }

    // ===== Actions =====

    /**
     * Update the officer's personal information (name, email, profile photo).
     */
    public function updatePersonalInfo()
    {
        $this->authorize('update', auth('web')->user());

        $this->validate($this->rulesForPersonalInfo());

        $this->updateProfile->handle(
            auth('web')->user(),
            ['name' => $this->name, 'email' => $this->email],
            $this->profile_photo
        );

        $this->profile_photo = null;

        $this->emit('notify', 'success', 'Personal information updated successfully.');
    }

    /**
     * Remove the current profile photo.
     */
    public function removePhoto()
    {
        $this->authorize('update', auth('web')->user());

        $this->removeProfilePhoto->handle(auth('web')->user());

        $this->profile_photo = null;

        $this->emit('notify', 'info', 'Profile photo removed.');
    }

    /**
     * Update the officer's password after verifying the current password.
     */
    public function updatePassword()
    {
        $this->authorize('update', auth('web')->user());

        $this->validate($this->rulesForPassword());

        if (! $this->changePassword->handle(auth('web')->user(), $this->current_password, $this->new_password)) {
            $this->addError('current_password', 'The current password you entered does not match your password.');

            return;
        }

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->emit('notify', 'success', 'Password updated successfully.');
    }

    public function render()
    {
        return view('livewire.admin.settings.profile-settings');
    }
}
