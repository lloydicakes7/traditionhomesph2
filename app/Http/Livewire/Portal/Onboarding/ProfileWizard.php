<?php

namespace App\Http\Livewire\Portal\Onboarding;

use App\Actions\Homeowners\Profiles\SubmitProfileVerification;
use App\Enums\PropertyType;
use App\Exceptions\ProfileSubmissionException;
use App\Http\Livewire\Concerns\ResolvesHomeownerAccount;
use App\Http\Livewire\PortalComponent;
use App\Http\Requests\Portal\Homeowners\SubmitProfileRequest;
use App\Queries\Homeowners\Households\HouseholdUnitQuery;
use App\Queries\Homeowners\Profiles\OnboardingFormQuery;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

/**
 * The form every resident fills before the portal opens to them.
 *
 * Three steps: the person in full, the unit and the household, and one valid ID.
 * Nothing else in the portal is reachable until the office has checked what was
 * submitted, so a refused profile is corrected here as well.
 */
class ProfileWizard extends PortalComponent
{
    use ResolvesHomeownerAccount, WithFileUploads;

    /** The step on screen: 1 personal, 2 household, 3 valid ID. */
    public $step = 1;

    /** @var array<string, mixed> The resident's personal information */
    public $form = [];

    /** @var array<string, mixed> The unit the household lives in */
    public $property = [
        'block' => '',
        'lot'   => '',
        'sqm'   => '',
        'type'  => '',
    ];

    /** @var array<string, mixed> The household's own facts */
    public $household = [
        'type'         => 'owner',
        'prev_address' => '',
        'relationship' => '',
        'be_head'      => false,
    ];

    /** @var array<string, mixed>|null What the association holds for the named unit */
    public $unit = null;

    /** @var mixed The valid ID, held in Livewire's temporary storage */
    public $idFile;

    // ===== Boot =====

    /** @var OnboardingFormQuery */
    protected $query;

    /** @var HouseholdUnitQuery */
    protected $units;

    public function boot(OnboardingFormQuery $query, HouseholdUnitQuery $units): void
    {
        $this->query = $query;
        $this->units = $units;
    }

    public function mount(): void
    {
        $account = $this->account();

        $this->authorize('updateOwn', $account);

        $this->form = $this->query->formFor($account);
        $this->property = $this->query->propertyFor($account);
        $this->household = $this->query->householdFor($account);

        $this->refreshUnit();
    }

    /** A changed block or lot is read back from the association's own records. */
    public function updated($name): void
    {
        if ($name !== 'property.block' && $name !== 'property.lot') {
            return;
        }

        $this->household['be_head'] = false;
        $this->resetErrorBag();
        $this->refreshUnit();
    }

    private function refreshUnit(): void
    {
        $residentId = $this->account()->resident_id;

        $this->unit = $this->unitIsNamed()
            ? $this->units->atUnit(
                (int) $this->property['block'],
                (int) $this->property['lot'],
                $residentId !== null ? (int) $residentId : null
            )
            : null;
    }

    private function unitIsNamed(): bool
    {
        return (int) ($this->property['block'] ?? 0) > 0 && (int) ($this->property['lot'] ?? 0) > 0;
    }

    // ===== Validation =====

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        $rules = (new SubmitProfileRequest)->rules();

        if ($this->unit !== null) {
            return $rules;
        }

        return array_merge($rules, [
            'property.sqm'  => ['required', 'numeric', 'min:1', 'max:99999'],
            'property.type' => ['required', Rule::in(PropertyType::values())],
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function stepRules(int $step): array
    {
        $prefixes = [
            1 => ['form.'],
            2 => ['property.', 'household.'],
            3 => ['idFile'],
        ][$step] ?? [];

        return array_filter($this->rules(), function ($key) use ($prefixes) {
            return Str::startsWith($key, $prefixes);
        }, ARRAY_FILTER_USE_KEY);
    }

    // ===== Steps =====

    public function next(): void
    {
        $this->resetErrorBag();
        $this->refreshUnit();

        $this->validate($this->stepRules($this->step));

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function back(): void
    {
        $this->resetErrorBag();

        if ($this->step > 1) {
            $this->step--;
        }
    }

    /**
     * Open one section of the form from its card header.
     */
    public function open(int $step): void
    {
        $this->resetErrorBag();

        if ($step >= 1 && $step <= 3) {
            $this->step = $step;
        }
    }

    // ===== Submit =====

    public function submit(): void
    {
        $account = $this->account();

        $this->authorize('updateOwn', $account);

        if ($account->profile_status?->isSubmitted()) {
            $this->leaveForDashboard();

            return;
        }

        $this->resetErrorBag();
        $this->refreshUnit();

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->step = $this->stepOfFirstError(array_keys($exception->errors()));

            throw $exception;
        }

        if (! $this->idFile && ! $this->resident()?->valid_id_path) {
            $this->step = 3;
            $this->addError('idFile', __('auth.onboarding.id_required'));

            return;
        }

        try {
            app(SubmitProfileVerification::class)->handle($account, [
                'form'      => $this->form,
                'property'  => $this->property,
                'household' => $this->household,
            ], $this->idFile);
        } catch (ProfileSubmissionException $exception) {
            $this->step = 2;
            $this->addError('property.block', $exception->getMessage());

            return;
        } catch (\Throwable $exception) {
            Log::error('Profile submission failed: ' . get_class($exception));

            $this->addError('profile', __('auth.onboarding.failed'));

            return;
        }

        $this->reset('idFile');

        $this->leaveForDashboard();
    }

    private function leaveForDashboard(): void
    {
        session()->flash('notify', [
            'type'    => 'info',
            'message' => __('auth.onboarding.under_review'),
        ]);

        $this->redirect(route('portal.dashboard'));
    }

    /**
     * @param  array<int, string>  $fields
     */
    private function stepOfFirstError(array $fields): int
    {
        $field = $fields[0] ?? 'form.surname';

        if (Str::startsWith($field, 'form.')) {
            return 1;
        }

        if (Str::startsWith($field, ['property.', 'household.'])) {
            return 2;
        }

        return 3;
    }

    // ===== Render =====

    public function render()
    {
        $account = $this->account();

        return view('livewire.portal.onboarding.profile-wizard', $this->query->options() + [
            'account'        => $account,
            'resident'       => $account->resident,
            'primaryAddress' => $this->query->primaryAddress(),
        ]);
    }
}
