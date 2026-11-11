<?php

namespace App\Http\Livewire\Admin\Settings;

use App\Actions\Settings\Authentication\UpdateAuthenticationSettings;
use App\Http\Livewire\AdminComponent;
use App\Models\Setting;
use App\Queries\Settings\AuthenticationSettingsQuery;

class AuthenticationSettings extends AdminComponent
{
    // ===== Registration =====

    public $allow_admin_registration = false;

    public $allow_resident_registration = true;

    public $auto_accept_registration = false;

    // ===== Collaborators =====

    /** @var AuthenticationSettingsQuery */
    protected $query;

    /** @var UpdateAuthenticationSettings */
    protected $updateSettings;

    public function boot(AuthenticationSettingsQuery $query, UpdateAuthenticationSettings $updateSettings)
    {
        $this->query = $query;
        $this->updateSettings = $updateSettings;
    }

    // ===== Lifecycle =====

    public function mount()
    {
        $this->authorize('viewAny', Setting::class);

        $settings = $this->query->form();

        $this->allow_admin_registration = $settings[AuthenticationSettingsQuery::ALLOW_ADMIN_REGISTRATION];
        $this->allow_resident_registration = $settings[AuthenticationSettingsQuery::ALLOW_RESIDENT_REGISTRATION];
        $this->auto_accept_registration = $settings[AuthenticationSettingsQuery::AUTO_ACCEPT_REGISTRATION];
    }

    // ===== Validation =====

    protected function rules()
    {
        return [
            'allow_admin_registration'    => ['boolean'],
            'allow_resident_registration' => ['boolean'],
            'auto_accept_registration'    => ['boolean'],
        ];
    }

    // ===== Actions =====

    public function save()
    {
        $this->authorize('update', Setting::class);
        $this->validate();

        $this->updateSettings->handle([
            AuthenticationSettingsQuery::ALLOW_ADMIN_REGISTRATION    => $this->allow_admin_registration,
            AuthenticationSettingsQuery::ALLOW_RESIDENT_REGISTRATION => $this->allow_resident_registration,
            AuthenticationSettingsQuery::AUTO_ACCEPT_REGISTRATION    => $this->auto_accept_registration,
        ], auth('web')->user());

        $this->emit('notify', 'success', 'Registration settings updated successfully.');
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.settings.authentication-settings');
    }
}
