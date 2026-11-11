<?php

namespace App\Http\Livewire\Portal\HouseholdProperty\Modals;

use App\Actions\Homeowners\Households\UpdateHouseholdFacts;
use App\Http\Livewire\PortalModalComponent;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Enums\HouseholdType;
use App\Models\Household\Household;
use Illuminate\Validation\Rule;

/**
 * "Edit Household" modal — the facts the homeowner keeps about their own
 * household: how the unit is held, how the household is composed, what it
 * earns, and where it lived before.
 *
 * The unit a household occupies is the office's to assign, so nothing here
 * moves the household — only the four facts the association asks the household
 * for. The write goes through the same UpdateHouseholdFacts action the office's
 * own dialog uses, so the record cannot be kept two ways. Ownership is
 * authorized on HouseholdPolicy::updateOwn.
 *
 * Opened by native Bootstrap JS from the Household / Property page's Edit
 * button (no record id — the account holds one household, resolved through the
 * signed-in account), which `modal-loader.js` relays as
 * `openEditHouseholdModal`. Emits `household-details-updated` so the page
 * redraws with the saved values.
 */
class EditHouseholdModal extends PortalModalComponent
{
    use ResolvesHomeownerAccount;

    const MODAL_ID = 'editHouseholdModal';

    /** @var int|null Household being edited */
    public $householdId = null;

    /** @var array The facts the household keeps about itself */
    public $form = [
        'type'         => 'owner',
        'prev_address' => '',
    ];

    /** @var array<string, string> Modal open / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openEditHouseholdModal'  => 'open',
        'closeEditHouseholdModal' => 'closeModal',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var UpdateHouseholdFacts */
    protected $updateFacts;

    public function boot(UpdateHouseholdFacts $updateFacts)
    {
        $this->updateFacts = $updateFacts;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    /**
     * The four facts, held to the same rules the office's own edit dialog
     * uses — the record is one, so both panels ask for it the same way.
     *
     * @return array
     */
    protected function rules(): array
    {
        return [
            'form.type'         => ['required', Rule::in(HouseholdType::values())],
            'form.prev_address' => ['nullable', 'string', 'max:500'],
        ];
    }

    // =====================================================================
    // Open / close
    // =====================================================================

    /**
     * Load the signed-in account's household into the form.
     *
     * @return void
     */
    public function open(): void
    {
        $household = $this->authorizedHousehold();

        $this->householdId = $household->id;
        $this->form = $this->formFor($household);
        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the household and the form when the shell closes, so the next open
     * never inherits them.
     */
    protected function resetModal(): void
    {
        $this->householdId = null;
        $this->form = $this->blankForm();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    /**
     * Save the four facts on the account's own household.
     *
     * @return void
     */
    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $household = $this->authorizedHousehold();

        $this->validate();

        $this->updateFacts->handle($household, $this->form);

        $this->emit('notify', 'success', 'Household details updated.');
        $this->emit('household-details-updated');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Tools
    // =====================================================================

    /**
     * The account's own household, with a fresh ownership check. The household
     * is reached through the signed-in account rather than by id, so no render
     * can be made to edit another household.
     *
     * @return \App\Models\Household\Household
     */
    private function authorizedHousehold(): Household
    {
        $household = $this->account()->household;

        if (! $household) {
            abort(404);
        }

        $this->authorize('updateOwn', $household);

        return $household;
    }

    /**
     * The household's facts, as the form holds them.
     */
    private function formFor(Household $household): array
    {
        return [
            'type'         => $household->type ?: HouseholdType::OWNER,
            'prev_address' => $household->prev_address ?? '',
        ];
    }

    /**
     * The form before it is loaded.
     */
    private function blankForm(): array
    {
        return [
            'type'         => HouseholdType::OWNER,
            'prev_address' => '',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    /**
     * Render the form. The dropdown data is static and supplied whether or not
     * the shell has loaded, because both branches are always compiled.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.portal.household-property.modals.edit-household-modal', [
            'householdTypes' => HouseholdType::labels(),
        ]);
    }
}
