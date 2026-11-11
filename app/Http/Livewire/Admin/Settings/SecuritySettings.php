<?php

namespace App\Http\Livewire\Admin\Settings;

use App\Actions\Settings\Security\UpdateLoginAttempts;
use App\Actions\Settings\Security\UpdatePasswordPolicy;
use App\Actions\Settings\Security\UpdateSessionTimeout;
use App\Http\Livewire\AdminComponent;
use App\Models\Setting;
use App\Queries\Settings\SecuritySettingsQuery;

class SecuritySettings extends AdminComponent
{
    // ===== Password policy =====

    public $password_min_length = 6;

    public $password_require_upper = true;

    public $password_require_lower = true;

    public $password_require_number = true;

    public $password_require_symbol = true;

    public $password_expiry_days = 0;

    // ===== Login attempts =====

    public $login_max_attempts = 5;

    public $login_lockout_minutes = 5;

    // ===== Session =====

    public $session_timeout = 120;

    public $session_expire_on_close = false;

    // ===== Collaborators =====

    /** @var SecuritySettingsQuery */
    protected $query;

    /** @var UpdatePasswordPolicy */
    protected $updatePasswordPolicy;

    /** @var UpdateLoginAttempts */
    protected $updateLoginAttempts;

    /** @var UpdateSessionTimeout */
    protected $updateSessionTimeout;

    public function boot(
        SecuritySettingsQuery $query,
        UpdatePasswordPolicy $updatePasswordPolicy,
        UpdateLoginAttempts $updateLoginAttempts,
        UpdateSessionTimeout $updateSessionTimeout
    ) {
        $this->query = $query;
        $this->updatePasswordPolicy = $updatePasswordPolicy;
        $this->updateLoginAttempts = $updateLoginAttempts;
        $this->updateSessionTimeout = $updateSessionTimeout;
    }

    // ===== Lifecycle =====

    public function mount()
    {
        $this->authorize('update', Setting::class);

        $settings = $this->query->form();

        $this->password_min_length = $settings['password_min_length'];
        $this->password_require_upper = $settings['password_require_upper'];
        $this->password_require_lower = $settings['password_require_lower'];
        $this->password_require_number = $settings['password_require_number'];
        $this->password_require_symbol = $settings['password_require_symbol'];
        $this->password_expiry_days = $settings['password_expiry_days'];
        $this->login_max_attempts = $settings['login_max_attempts'];
        $this->login_lockout_minutes = $settings['login_lockout_minutes'];
        $this->session_timeout = $settings['session_timeout'];
        $this->session_expire_on_close = $settings['session_expire_on_close'];
    }

    // ===== Validation =====

    protected function rulesForPasswordPolicy()
    {
        return [
            'password_min_length'     => ['required', 'integer', 'min:4', 'max:128'],
            'password_require_upper'  => ['boolean'],
            'password_require_lower'  => ['boolean'],
            'password_require_number' => ['boolean'],
            'password_require_symbol' => ['boolean'],
            'password_expiry_days'    => ['required', 'integer', 'min:0', 'max:365'],
        ];
    }

    protected function rulesForLoginAttempts()
    {
        return [
            'login_max_attempts'    => ['required', 'integer', 'min:1', 'max:100'],
            'login_lockout_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }

    protected function rulesForSessionTimeout()
    {
        return [
            'session_timeout'         => ['required', 'integer', 'min:1', 'max:43200'],
            'session_expire_on_close' => ['boolean'],
        ];
    }

    // ===== Actions =====

    public function savePasswordPolicy()
    {
        $this->authorize('update', Setting::class);
        $this->validate($this->rulesForPasswordPolicy());

        $this->updatePasswordPolicy->handle([
            'password_min_length'     => $this->password_min_length,
            'password_require_upper'  => $this->password_require_upper,
            'password_require_lower'  => $this->password_require_lower,
            'password_require_number' => $this->password_require_number,
            'password_require_symbol' => $this->password_require_symbol,
            'password_expiry_days'    => $this->password_expiry_days,
        ], auth('web')->user());

        $this->emit('notify', 'success', 'Password policy updated successfully.');
    }

    public function saveLoginAttempts()
    {
        $this->authorize('update', Setting::class);
        $this->validate($this->rulesForLoginAttempts());

        $this->updateLoginAttempts->handle(
            (int) $this->login_max_attempts,
            (int) $this->login_lockout_minutes,
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Login attempt settings updated successfully.');
    }

    public function saveSessionTimeout()
    {
        $this->authorize('update', Setting::class);
        $this->validate($this->rulesForSessionTimeout());

        $this->updateSessionTimeout->handle(
            (int) $this->session_timeout,
            (bool) $this->session_expire_on_close,
            auth('web')->user()
        );

        $this->emit('notify', 'success', 'Session timeout settings updated successfully.');
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.settings.security-settings');
    }
}
