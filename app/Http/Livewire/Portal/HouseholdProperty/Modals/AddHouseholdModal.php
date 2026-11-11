<?php

namespace App\Http\Livewire\Portal\HouseholdProperty\Modals;

use App\Actions\Homeowners\Households\CreateOwnHousehold;
use App\Enums\HouseholdRelationship;
use App\Enums\HouseholdType;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalModalComponent;
use App\Queries\Homeowners\Households\HouseholdUnitQuery;
use Illuminate\Validation\Rule;

/**
 * "Add Household" modal — the homeowner creating their own household when the
 * office has none on record.
 *
 * Only the association address is known while no household exists: the block
 * and lot name the unit, and the address is read back from the office's own
 * records. A unit somebody already lives in is refused — the office places the
 * homeowner in an existing household, and the portal never links to one or
 * overwrites its head.
 *
 * Emits `household-details-updated`, so the page reloads with the household
 * the homeowner just created and the Add Member workflow becomes available.
 */
class AddHouseholdModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'addHouseholdModal';

    /** @var array The household the homeowner is creating */
    public $form = [
        'block'        => '',
        'lot'          => '',
        'type'         => HouseholdType::OWNER,
        'relationship' => '',
        'prev_address' => '',
    ];

    /** @var array<string, mixed>|null What the office holds for the named unit */
    public $unit = null;

    protected $listeners = [
        'openAddHouseholdModal'  => 'open',
        'closeAddHouseholdModal' => 'closeModal',
    ];

    // ===== Boot =====

    /** @var HouseholdUnitQuery */
    protected $units;

    /** @var CreateOwnHousehold */
    protected $createHousehold;

    public function boot(HouseholdUnitQuery $units, CreateOwnHousehold $createHousehold)
    {
        $this->units = $units;
        $this->createHousehold = $createHousehold;
    }

    // ===== Validation =====

    protected function rules(): array
    {
        return [
            'form.block'        => ['required', 'integer', 'min:1', 'max:9999'],
            'form.lot'          => ['required', 'integer', 'min:1', 'max:9999'],
            'form.type'         => ['required', Rule::in(HouseholdType::values())],
            'form.relationship' => ['required', Rule::in(HouseholdRelationship::values())],
            'form.prev_address' => ['nullable', 'string', 'max:500'],
        ];
    }

    // ===== Open / close =====

    public function open(): void
    {
        if ($this->account()->household()->exists()) {
            abort(403);
        }

        $this->form = $this->blankForm();
        $this->unit = null;
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->form = $this->blankForm();
        $this->unit = null;
        $this->resetValidation();

        parent::resetModal();
    }

    /**
     * A changed block or lot asks the office's records again.
     */
    public function updated($name): void
    {
        if ($name !== 'form.block' && $name !== 'form.lot') {
            return;
        }

        $this->unit = null;
        $this->resetErrorBag();

        $block = (int) ($this->form['block'] ?? 0);
        $lot = (int) ($this->form['lot'] ?? 0);

        if ($block < 1 || $lot < 1) {
            return;
        }

        $this->unit = $this->units->atUnit($block, $lot);
    }

    // ===== Save =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        if ($this->account()->household()->exists()) {
            $this->emit('notify', 'error', 'Your account already has a household.');

            return;
        }

        $this->validate();

        $this->unit = $this->units->atUnit((int) $this->form['block'], (int) $this->form['lot']);

        if ($this->unit && $this->unit['is_occupied']) {
            $this->addError(
                'form.block',
                'Someone is already registered at this address. Ask the office to add you to that household.'
            );

            return;
        }

        try {
            $this->createHousehold->handle($this->account(), $this->form);
        } catch (\RuntimeException $exception) {
            $this->addError('form.block', $exception->getMessage());

            return;
        }

        $this->emit('notify', 'success', 'Household created. You are its household head.');
        $this->emit('household-details-updated');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // ===== Tools =====

    private function blankForm(): array
    {
        return [
            'block'        => '',
            'lot'          => '',
            'type'         => HouseholdType::OWNER,
            'relationship' => '',
            'prev_address' => '',
        ];
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.portal.household-property.modals.add-household-modal', [
            'householdTypes'      => $this->loaded ? HouseholdType::labels() : [],
            'relationshipOptions' => $this->loaded ? HouseholdRelationship::labels() : [],
            'associationAddress'  => $this->units->associationAddress(),
        ]);
    }
}
