<?php

namespace App\Http\Livewire\Admin\Settings;

use App\Actions\Settings\General\RemoveHoaLogo;
use App\Actions\Settings\General\UpdateContactInformation;
use App\Actions\Settings\General\UpdateHoaInformation;
use App\Http\Livewire\AdminComponent;
use App\Models\Setting;
use App\Queries\Settings\GeneralSettingsQuery;
use Livewire\WithFileUploads;

class GeneralSettings extends AdminComponent
{
    use WithFileUploads;

    // ===== Association =====

    public $hoa_name = '';

    public $address = '';

    public $website = '';

    public $logo;

    public $logo_path = null;

    // ===== Contact =====

    public $email = '';

    public $contact_number = '';

    public $working_hours = '';

    // ===== Colloquies =====

    /** @var GeneralSettingsQuery */
    protected $query;

    /** @var UpdateHoaInformation */
    protected $updateHoa;

    /** @var UpdateContactInformation */
    protected $updateContact;

    /** @var RemoveHoaLogo */
    protected $removeLogo;

    public function boot(
        GeneralSettingsQuery $query,
        UpdateHoaInformation $updateHoa,
        UpdateContactInformation $updateContact,
        RemoveHoaLogo $removeLogo
    ) {
        $this->query = $query;
        $this->updateHoa = $updateHoa;
        $this->updateContact = $updateContact;
        $this->removeLogo = $removeLogo;
    }

    // ===== Lifecycle =====

    public function mount()
    {
        $settings = $this->query->form();

        $this->hoa_name = $settings['hoa_name'] ?? '';
        $this->address = $settings['address'] ?? '';
        $this->website = $settings['website'] ?? '';
        $this->email = $settings['email'] ?? '';
        $this->contact_number = $settings['contact_number'] ?? '';
        $this->working_hours = $settings['working_hours'] ?? '';
        $this->logo_path = $settings['logo_path'] ?? null;
    }

    // ===== Validation =====

    protected function rulesForHoaInfo()
    {
        return [
            'hoa_name' => ['required', 'string', 'max:255'],
            'address'  => ['nullable', 'string', 'max:255'],
            'website'  => ['nullable', 'url', 'max:255'],
            'logo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ];
    }

    protected function rulesForContact()
    {
        return [
            'email'          => ['nullable', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'working_hours'  => ['nullable', 'string', 'max:255'],
        ];
    }

    // ===== Actions =====

    public function saveHoaInfo()
    {
        $this->authorize('update', Setting::class);
        $this->validate($this->rulesForHoaInfo());

        $this->logo_path = $this->updateHoa->handle([
            'hoa_name' => $this->hoa_name,
            'address'  => $this->address,
            'website'  => $this->website,
        ], $this->logo, $this->logo_path, auth('web')->user());

        $this->logo = null;

        $this->emit('notify', 'success', 'HOA information updated successfully.');
    }

    public function saveContact()
    {
        $this->authorize('update', Setting::class);
        $this->validate($this->rulesForContact());

        $this->updateContact->handle([
            'email'          => $this->email,
            'contact_number' => $this->contact_number,
            'working_hours'  => $this->working_hours,
        ], auth('web')->user());

        $this->emit('notify', 'success', 'Contact information updated successfully.');
    }

    public function removeLogo()
    {
        $this->authorize('update', Setting::class);

        $this->removeLogo->handle($this->logo_path, auth('web')->user());

        $this->logo_path = null;
        $this->logo = null;

        $this->emit('notify', 'info', 'Logo removed.');
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.settings.general-settings', $this->query->environment());
    }
}
