<?php

namespace App\Http\Livewire\Portal\Profile;

use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalComponent;
use App\Rules\PasswordPolicy;
use Illuminate\Support\Facades\Hash;

/**
 * The screen a temporary password must pass through before the portal opens.
 */
class PasswordChange extends PortalComponent
{
    use ResolvesHomeownerAccount;

    /** @var string The password the applicant was issued */
    public $current_password = '';

    /** @var string */
    public $new_password = '';

    /** @var string */
    public $new_password_confirmation = '';

    // ===== Boot =====

    public function mount(): void
    {
        $this->authorize('viewOwn', $this->account());
    }

    // ===== Validation =====

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'string', 'confirmed', new PasswordPolicy],
        ];
    }

    // ===== Save =====

    public function save(): void
    {
        $account = $this->account();

        $this->authorize('updateOwn', $account);

        $this->validate();

        if (! Hash::check($this->current_password, $account->password)) {
            $this->addError('current_password', 'The current password you entered does not match your password.');

            return;
        }

        if (Hash::check($this->new_password, $account->password)) {
            $this->addError('new_password', 'Choose a password different from the one you were given.');

            return;
        }

        $account->update([
            'password'            => Hash::make($this->new_password),
            'password_changed_at' => now(),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        $this->redirect(route('portal.dashboard'));
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.portal.profile.password-change');
    }
}
