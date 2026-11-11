<?php

namespace App\Http\Livewire\Portal\Profile;

use App\Http\Livewire\PortalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Rules\PasswordPolicy;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

/**
 * My Profile — the homeowner's own account.
 *
 * The page keeps two jobs in two forms:
 *   1. The account side of the profile: photo and email address.
 *   2. Their password.
 *
 * The RBI profile the office recorded (name, contact number, birth date, sex,
 * civil status, citizenship, occupation, educational attainment) is not shown
 * here: it is kept up to date on its own page — Personal Information — which
 * edits the same fields the office's own resident form carries. That page owns
 * the record, so a field that is stored on the resident (the contact number
 * among them) is asked for there and not on this one.
 *
 * Every action authorizes ownership first, through the portal side of the
 * policies (`*Own` abilities), which compare the record against the signed-in
 * account instead of a role.
 */
class ProfileDetails extends PortalComponent
{
    use WithFileUploads, ResolvesHomeownerAccount;

    /** @var string The account's contact email */
    public $email = '';

    /** @var mixed A newly chosen profile photo */
    public $photo;

    /** @var string The password being replaced */
    public $current_password = '';

    /** @var string */
    public $new_password = '';

    /** @var string */
    public $new_password_confirmation = '';

    /**
     * Fill the forms with what the account currently holds.
     *
     * @return void
     */
    public function mount(): void
    {
        $this->authorize('viewOwn', $this->account());

        $this->email = $this->account()->email;
    }

    // =====================================================================
    // Contact details
    // =====================================================================

    /**
     * Validation rules for the details the homeowner keeps themselves.
     *
     * @return array
     */
    protected function contactRules(): array
    {
        return [
            'email'     => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->account()->id),
            ],
            'photo'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * Save the contact details and, when one was chosen, the profile photo.
     *
     * @return void
     */
    public function save(): void
    {
        $account = $this->account();
        $resident = $this->resident();

        $this->authorize('updateOwn', $account);

        if ($resident) {
            $this->authorize('updateOwn', $resident);
        }

        $this->validate($this->contactRules());

        $account->email = $this->email;
        $account->save();

        if ($resident) {
            if ($this->photo) {
                $resident->profile_photo_path = StorageHelper::replacePhoto(
                    $this->photo,
                    $resident->profile_photo_path,
                    'photos/residents'
                );
            }

            $resident->save();
        }

        $this->reset('photo');

        $this->emit('notify', 'success', 'Profile saved.');
    }

    /**
     * Remove the photo on file, leaving the account with the placeholder.
     *
     * @return void
     */
    public function removePhoto(): void
    {
        $resident = $this->resident();

        if (! $resident || ! $resident->profile_photo_path) {
            return;
        }

        $this->authorize('updateOwn', $resident);

        StorageHelper::deletePhoto($resident->profile_photo_path);

        $resident->update(['profile_photo_path' => null]);

        $this->reset('photo');

        $this->emit('notify', 'info', 'Profile photo removed.');
    }

    // =====================================================================
    // Password
    // =====================================================================

    /**
     * Validation rules for the password change.
     *
     * @return array
     */
    protected function passwordRules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'confirmed', new PasswordPolicy],
        ];
    }

    /**
     * Replace the password, once the current one has been confirmed.
     *
     * @return void
     */
    public function updatePassword(): void
    {
        $account = $this->account();

        $this->authorize('updateOwn', $account);

        $this->validate($this->passwordRules());

        if (! Hash::check($this->current_password, $account->password)) {
            $this->addError('current_password', 'The current password you entered does not match your password.');

            return;
        }

        $account->update([
            'password'            => Hash::make($this->new_password),
            'password_changed_at' => now(),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->emit('notify', 'success', 'Password updated.');
    }

    // =====================================================================
    // Render
    // =====================================================================

    /**
     * Render the profile page.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $resident = $this->resident();

        return view('livewire.portal.profile.profile-details', [
            'account'  => $this->account(),
            'resident' => $resident,
            'photoUrl' => $resident ? ($resident->photo_url ?: asset('image/user.png')) : null,
        ]);
    }
}
