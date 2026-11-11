<?php

namespace App\Http\Livewire\Portal\PersonalInformation;

use App\Enums\CivilStatus;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalComponent;
use App\Http\Requests\Homeowners\ResidentRequest;
use App\Models\Resident;
use App\Queries\Homeowners\Residents\ResidentFormQuery;

class PersonalInformationForm extends PortalComponent
{
    use ResolvesHomeownerAccount;

    public $form = [];

    // ===== Boot =====

    /** @var ResidentFormQuery */
    protected $query;

    public function boot(ResidentFormQuery $query)
    {
        $this->query = $query;
    }

    // ===== Validation =====

    public function mount(): void
    {
        $resident = $this->resident();

        if ($resident) {
            $this->authorize('viewOwn', $resident);
        }

        $this->form = $this->formFor($resident);
    }

    // ===== Validation =====

    protected function rules(): array
    {
        return (new ResidentRequest)->rules();
    }

    // ===== Save =====

    public function save(): void
    {
        $resident = $this->resident();

        if (! $resident) {
            $this->emit('notify', 'error', 'There is no profile on file for your account yet.');

            return;
        }

        $this->authorize('updateOwn', $resident);

        $this->validate();

        $resident->update(Resident::payloadFrom($this->form));

        $this->form = $this->formFor($resident->fresh());

        $this->emit('notify', 'success', 'Personal information saved.');
    }

    // ===== Tools =====

    private function formFor(?Resident $resident): array
    {
        return [
            'surname'                => $resident->surname ?? '',
            'first_name'             => $resident->first_name ?? '',
            'middle_name'            => $resident->middle_name ?? '',
            'suffix'                 => $resident->suffix ?? '',
            'philsys_card_no'        => $resident->philsys_card_no ?? '',
            'mobile_no'              => $resident->mobile_no ?? '',
            'sex'                    => $resident->sex ?? '',
            'birth_date'             => $resident && $resident->birth_date
                ? $resident->birth_date->format('Y-m-d')
                : '',
            'civil_status'           => $resident->civil_status ?? '',
            'spouse_name'            => $resident->spouse_name ?? '',
            'citizenship'            => $resident->citizenship ?? '',
            'religion'               => $resident->religion ?? '',
            'occupation'             => $resident->occupation ?? '',
            'educational_attainment' => $resident->educational_attainment ?? '',
            'sectors'                => $resident ? $resident->sectorValues() : [],
        ];
    }

    // ===== Render =====

    public function render()
    {
        return view(
            'livewire.portal.personal-information.personal-information-form',
            $this->query->options() + [
                'resident'   => $this->resident(),
                'showSpouse' => CivilStatus::isMarried((string) ($this->form['civil_status'] ?? '')),
            ]
        );
    }
}
