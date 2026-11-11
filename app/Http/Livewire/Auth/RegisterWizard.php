<?php

namespace App\Http\Livewire\Auth;

use App\Actions\Auth\RegisterHomeowner;
use App\Http\Livewire\PublicComponent;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Queries\Auth\RegistrationFormQuery;
use App\Queries\Settings\AuthenticationSettingsQuery;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

/**
 * The five-step public registration wizard.
 *
 * Nothing is written until the last step: the component holds the applicant's
 * answers between steps, validates each step on the server, and re-validates
 * the whole payload at submission.
 *
 * The unit's address is the association's own, shown read-only; the applicant
 * names only the block and lot. A unit the office has no record of is described
 * once (size and type), and a vacant unit collects the household's own details
 * (tenure type, monthly income and previous address) so the applicant becomes
 * its head. The address lookup only ever names the unit and the head's
 * initials, never another resident's details.
 */
class RegisterWizard extends PublicComponent
{
    use WithFileUploads;

    /** How many accounts one address may create in an hour. */
    const REGISTRATION_LIMIT = 5;

    /** The window those registrations are counted over, in seconds. */
    const REGISTRATION_DECAY = 3600;

    /** The step on screen: 1 account, 2 person, 3 address, 4 ID, 5 review. */
    public $step = 1;

    /** @var string The account's email address */
    public $email = '';

    /** @var string The password the applicant chose */
    public $password = '';

    /** @var string The same password again */
    public $password_confirmation = '';

    /** @var array<string, mixed> The applicant's personal information */
    public $form = [];

    /** @var array<string, string> The unit the applicant lives in */
    public $property = [
        'block' => '',
        'lot'   => '',
        'sqm'   => '0',
        'type'  => '',
    ];

    /** @var array<string, string> The household at a unit nobody occupies yet */
    public $household = [
        'type'           => 'owner',
        'prev_address'   => '',
    ];

    /** @var mixed The valid ID, held in Livewire's temporary storage */
    public $idFile;

    /** @var array<string, bool> The two consents: the terms and the privacy notice */
    public $consent = ['terms' => false, 'privacy' => false];

    /** @var array<string, mixed>|null What the address lookup may say */
    public $address = null;

    /** @var bool The applicant confirmed they live in the matched household */
    public $link_household = false;

    /** @var bool The submission is being written */
    public $processing = false;

    /** @var bool The registration was accepted */
    public $registered = false;

    /** @var bool The accepted registration still waits for the office */
    public $pendingApproval = false;

    // ===== Boot =====

    /** @var RegistrationFormQuery */
    protected $query;

    public function boot(RegistrationFormQuery $query): void
    {
        $this->query = $query;
    }

    public function mount(): void
    {
        if (! app(AuthenticationSettingsQuery::class)->residentRegistrationAllowed()) {
            abort(404);
        }

        $this->form = $this->query->blankForm();
    }

    // ===== Validation =====

    /**
     * Every rule of every step, in the wizard's own property names.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return (new RegistrationRequest)->rules();
    }

    /**
     * The rules that belong to one step only.
     *
     * @return array<string, array<int, mixed>>
     */
    private function stepRules(int $step): array
    {
        $prefixes = [
            1 => ['email', 'password'],
            2 => ['form.'],
            3 => ['property.', 'household.', 'link_household'],
            4 => ['idFile'],
            5 => ['consent.'],
        ][$step] ?? [];

        return array_filter($this->rules(), function ($key) use ($prefixes) {
            return Str::startsWith($key, $prefixes);
        }, ARRAY_FILTER_USE_KEY);
    }

    // ===== Steps =====

    /**
     * Move to the next step, validating the current one first.
     */
    public function next(): void
    {
        $this->resetErrorBag();

        $this->validate($this->stepRules($this->step));

        if ($this->step === 3 && ! $this->resolveAddress()) {
            return;
        }

        if ($this->step < 5) {
            $this->step++;
        }
    }

    /**
     * Return to an earlier step, keeping every answer already given.
     */
    public function back(): void
    {
        $this->resetErrorBag();

        if ($this->step > 1) {
            $this->step--;
        }
    }

    /**
     * Jump back to the step an edit link names.
     */
    public function edit(int $step): void
    {
        $this->resetErrorBag();

        if ($step >= 1 && $step < $this->step) {
            $this->step = $step;
        }
    }

    /**
     * A changed block or lot asks the office's records again, and drops the
     * confirmation the applicant gave for the unit they were leaving.
     */
    public function updated($name): void
    {
        if ($name !== 'property.block' && $name !== 'property.lot') {
            return;
        }

        $this->address = null;
        $this->link_household = false;
        $this->resetErrorBag();

        $this->lookupUnit();
    }

    // ===== Submit =====

    /**
     * Validate everything again and write the registration.
     */
    public function submit(): void
    {
        if ($this->processing) {
            return;
        }

        $this->resetErrorBag();

        if ($this->limiter()->tooManyAttempts($this->throttleKey(), self::REGISTRATION_LIMIT)) {
            $this->addError(
                'registration',
                'Too many registrations have been filed from this connection. Please try again later or contact the office.'
            );

            return;
        }

        try {
            $this->validate();
        } catch (ValidationException $exception) {
            $this->step = $this->stepOfFirstError(array_keys($exception->errors()));

            throw $exception;
        }

        if (! $this->resolveAddress()) {
            $this->step = 3;

            return;
        }

        $payload = [
            'email'          => strtolower(trim($this->email)),
            'password'       => $this->password,
            'resident'       => $this->form,
            'property'       => $this->propertyPayload(),
            'household'      => $this->household,
            'link_household' => (bool) $this->link_household,
        ];

        $this->processing = true;

        try {
            $user = app(RegisterHomeowner::class)->handle($payload, $this->idFile);
        } catch (\Throwable $exception) {
            Log::error('Registration failed: ' . get_class($exception));

            $this->processing = false;

            $this->addError('registration', 'We could not complete your registration. Please try again, or contact the office.');

            return;
        }

        $this->limiter()->hit($this->throttleKey(), self::REGISTRATION_DECAY);

        $this->processing = false;
        $this->registered = true;
        $this->pendingApproval = $user->isPendingRegistration();

        $this->reset('idFile', 'password', 'password_confirmation');
    }

    /**
     * The unit as columns: the block and lot as numbers, and the size and type
     * only when the applicant described a unit the office had no record of.
     *
     * @return array<string, mixed>
     */
    private function propertyPayload(): array
    {
        $sqm = trim((string) ($this->property['sqm'] ?? ''));

        return [
            'block' => (int) $this->property['block'],
            'lot'   => (int) $this->property['lot'],
            'sqm'   => $sqm === '' ? null : (float) $sqm,
            'type'  => trim((string) ($this->property['type'] ?? '')) ?: null,
        ];
    }

    /**
     * The step that owns the first field the full validation rejected.
     *
     * @param  array<int, string>  $fields
     */
    private function stepOfFirstError(array $fields): int
    {
        $field = $fields[0] ?? 'email';

        if (Str::startsWith($field, 'form.')) {
            return 2;
        }

        if (Str::startsWith($field, ['property.', 'household.']) || $field === 'link_household') {
            return 3;
        }

        if ($field === 'idFile') {
            return 4;
        }

        if (Str::startsWith($field, 'consent.')) {
            return 5;
        }

        return 1;
    }

    // ===== Throttle =====

    /**
     * One key per connection: a script cannot create accounts in bulk, while a
     * household behind a shared address is still far below the limit.
     */
    private function throttleKey(): string
    {
        return 'registration:' . request()->ip();
    }

    /**
     * The framework's rate limiter (Laravel 7 has no RateLimiter facade).
     */
    private function limiter(): RateLimiter
    {
        return app(RateLimiter::class);
    }

    // ===== Address =====

    /**
     * Show what the office already holds for the named unit, as soon as the
     * applicant has named one.
     */
    private function lookupUnit(): void
    {
        if ($this->property['block'] === '' || $this->property['lot'] === '') {
            return;
        }

        $property = $this->query->propertyAt((int) $this->property['block'], (int) $this->property['lot']);

        if ($property !== null) {
            $this->address = $this->query->householdAt($property);
        }
    }

    /**
     * Confirm the unit before the wizard moves on: a unit the office has no
     * record of needs its size and type, a vacant unit needs the household's
     * details, and an occupied one needs the applicant to confirm the link.
     */
    private function resolveAddress(): bool
    {
        $this->address = null;

        $property = $this->query->propertyAt((int) $this->property['block'], (int) $this->property['lot']);

        if ($property !== null) {
            $this->address = $this->query->householdAt($property);

            if ($this->address['is_occupied']) {
                if (! $this->link_household) {
                    $this->addError('link_household', __('auth.address_link_required'));

                    return false;
                }

                return true;
            }

            return $this->householdIsComplete();
        }

        $primary = $this->query->primaryAddress();

        if ($primary === null) {
            $this->addError('property.block', __('auth.address_primary_missing'));

            return false;
        }

        if (! $this->newUnitIsComplete()) {
            return false;
        }

        $this->address = [
            'property_id'   => null,
            'address'       => implode(' · ', array_filter([
                'Blk ' . (int) $this->property['block'] . ' Lot ' . (int) $this->property['lot'],
                $primary->full_address,
            ])),
            'is_occupied'   => false,
            'is_new'        => true,
            'head_initials' => null,
        ];

        return $this->householdIsComplete();
    }

    /**
     * A unit the office has no record of has to be described before it can be
     * created with the registration.
     */
    private function newUnitIsComplete(): bool
    {
        if (trim((string) $this->property['type']) !== '') {
            return true;
        }

        $this->addError('property.type', __('auth.property_type_required'));

        return false;
    }

    /**
     * A vacant unit needs the household's own details, because the applicant
     * is about to become its head.
     */
    private function householdIsComplete(): bool
    {
        if (trim((string) $this->household['type']) !== '') {
            return true;
        }

        $this->addError('household.type', __('auth.household_type_required'));

        return false;
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.auth.register-wizard', $this->query->options() + [
            'address'        => $this->address,
            'accountEmail'   => $this->email,
            'primaryAddress' => $this->query->primaryAddress(),
        ]);
    }
}
