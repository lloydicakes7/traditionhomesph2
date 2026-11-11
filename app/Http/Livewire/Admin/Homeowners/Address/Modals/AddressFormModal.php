<?php

namespace App\Http\Livewire\Admin\Homeowners\Address\Modals;

use App\Actions\Homeowners\Addresses\CreateAddress;
use App\Actions\Homeowners\Addresses\UpdateAddress;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Address;
use App\Queries\Homeowners\Addresses\AddressListQuery;
use App\Services\PSGCService;

class AddressFormModal extends AdminModalComponent
{
    // =====================================================================
    // CONSTANTS
    // =====================================================================

    private const DEFAULT_FORM = [
        'label'       => '',
        'street'      => '',
        'subdivision' => '',
        'barangay'    => '',
        'city'        => '',
        'province'    => '',
        'region'      => '',
        'zip_code'    => '',
        'country'     => 'Philippines',
        'is_primary'  => false,
    ];

    private const LEVELS = [
        'region'   => 'regions',
        'province' => 'provinces',
        'city'     => 'citiesMunicipalities',
        'barangay' => 'barangays',
    ];

    private const FETCH_CHILDREN = [
        'region'   => 'fetchProvinces',
        'province' => 'fetchCitiesMunicipalities',
        'city'     => 'fetchBarangays',
    ];

    public $showFormModal = false;

    public $isEditing = false;

    public $editingAddressId = null;

    public $form = self::DEFAULT_FORM;

    protected $listeners = [
        'openAddressFormModal'  => 'openCreate',
        'loadAddressFormModal'  => 'openEdit',
        'closeAddressFormModal' => 'closeModal',
    ];

    // =====================================================================
    // PSGC
    // =====================================================================

    public $regions = [];

    public $provinces = [];

    public $citiesMunicipalities = [];

    public $barangays = [];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var AddressListQuery */
    protected $query;

    /** @var CreateAddress */
    protected $createAddress;

    /** @var UpdateAddress */
    protected $updateAddress;

    /** @var PSGCService */
    protected $psgcService;

    public function boot(
        AddressListQuery $query,
        CreateAddress $createAddress,
        UpdateAddress $updateAddress,
        PSGCService $psgcService
    ) {
        $this->query = $query;
        $this->createAddress = $createAddress;
        $this->updateAddress = $updateAddress;
        $this->psgcService = $psgcService;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'form.label'       => ['required', 'string', 'max:100'],
            'form.street'      => ['nullable', 'string', 'max:255'],
            'form.subdivision' => ['required', 'string', 'max:255'],
            'form.barangay'    => ['required', 'string', 'max:255'],
            'form.city'        => ['required', 'string', 'max:255'],
            'form.province'    => ['required', 'string', 'max:255'],
            'form.region'      => ['nullable', 'string', 'max:255'],
            'form.zip_code'    => ['nullable', 'string', 'max:20'],
            'form.country'     => ['required', 'string', 'max:100'],
            'form.is_primary'  => ['boolean'],
        ];
    }

    // =====================================================================
    // CASCADING WATCHERS
    // =====================================================================

    public function updatedFormRegion($value): void
    {
        $this->cascadeChanged('region', $value);
    }

    public function updatedFormProvince($value): void
    {
        $this->cascadeChanged('province', $value);
    }

    public function updatedFormCity($value): void
    {
        $city = $this->cascadeChanged('city', $value);

        $this->form['zip_code'] = $city['zip_code'] ?? '';
    }

    // =====================================================================
    // Open / close
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Address::class);

        $this->openWith(self::DEFAULT_FORM);
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Address::class);

        $address = $this->query->find((int) $id);

        $saved = array_merge(
            self::DEFAULT_FORM,
            array_filter(
                $address->only(array_keys(self::DEFAULT_FORM)),
                fn ($value) => $value !== null
            )
        );

        $this->openWith($saved, $address->id);
    }

    public function resetModal(): void
    {
        $this->showFormModal = false;
        $this->editingAddressId = null;
        $this->resetPsgcState();
        $this->dispatchBrowserEvent('modal-close', ['id' => 'addressFormModal']);

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        $this->authorize($this->isEditing ? 'update' : 'create', Address::class);
        $this->validate();

        if ($this->isEditing) {
            $this->updateAddress->handle($this->editingAddressId, $this->form, auth('web')->user());
        } else {
            $this->createAddress->handle($this->form, auth('web')->user());
        }

        $this->emit(
            'notify',
            'success',
            $this->isEditing ? 'Address updated successfully.' : 'Address created successfully.'
        );

        $this->emit('addressSaved');
        $this->resetModal();
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.homeowners.address.modals.address-form-modal');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function openWith(array $form, ?int $addressId = null): void
    {
        $this->isEditing = $addressId !== null;
        $this->editingAddressId = $addressId;
        $this->form = $form;

        $this->resetPsgcState();
        $this->regions = $this->psgcService->fetchRegions();

        if ($this->isEditing) {
            $this->restoreCascade($form);
        }

        $this->resetValidation();
        $this->markLoaded();
    }

    private function cascadeChanged(string $level, ?string $value): ?array
    {
        $this->clearBelow($level);

        return $this->loadChildren($level, $value);
    }

    private function clearBelow(string $level): void
    {
        $levels = array_keys(self::LEVELS);
        $below  = array_slice($levels, array_search($level, $levels, true) + 1);

        foreach ($below as $child) {
            $this->form[$child] = '';
            $this->{self::LEVELS[$child]} = [];
        }

        $this->form['zip_code'] = '';
    }

    private function loadChildren(string $level, ?string $value): ?array
    {
        if (! isset(self::FETCH_CHILDREN[$level])) {
            return null;
        }

        $selected = collect($this->{self::LEVELS[$level]})->firstWhere('name', $value);

        if ($selected) {
            $levels = array_keys(self::LEVELS);
            $child  = $levels[array_search($level, $levels, true) + 1];

            $this->{self::LEVELS[$child]} = $this->psgcService
                ->{self::FETCH_CHILDREN[$level]}($selected['code']);
        }

        return $selected;
    }

    private function restoreCascade(array $saved): void
    {
        foreach (array_keys(self::FETCH_CHILDREN) as $level) {
            $selected = $this->loadChildren($level, $saved[$level] ?? '');

            if (! $selected) {
                return;
            }

            if ($level === 'city') {
                $this->form['zip_code'] = $selected['zip_code'] ?? $saved['zip_code'] ?? '';
            }
        }
    }

    private function resetPsgcState(): void
    {
        foreach (self::LEVELS as $options) {
            $this->{$options} = [];
        }
    }
}
