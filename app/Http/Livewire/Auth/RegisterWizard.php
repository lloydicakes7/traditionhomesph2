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

/**
 * The three-step public registration wizard.
 *
 * Nothing is written until the last step: the component holds the applicant's
 * answers between steps, validates each step on the server, and re-validates
 * the whole payload at submission.
 *
 * The applicant gives only what is needed to open an account — who they are,
 * the address they sign in with, and their consent. The household, the unit and
 * the valid ID are asked for on the onboarding form, after the email is
 * verified and the account can be used.
 */
class RegisterWizard extends PublicComponent
{
    /** How many accounts one address may create in an hour. */
    const REGISTRATION_LIMIT = 5;

    /** The window those registrations are counted over, in seconds. */
    const REGISTRATION_DECAY = 3600;

    /** The step on screen: 1 personal, 2 account, 3 review. */
    public $step = 1;

    /** @var string The account's email address */
    public $email = '';

    /** @var string The password the applicant chose */
    public $password = '';

    /** @var string The same password again */
    public $password_confirmation = '';

    /** @var array<string, mixed> The applicant's personal information */
    public $form = [];

    /** @var array<string, bool> The two consents: the terms and the privacy notice */
    public $consent = ['terms' => false, 'privacy' => false];

    /** @var bool The submission is being written */
    public $processing = false;

    /** @var bool The registration was accepted */
    public $registered = false;

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
            1 => ['form.'],
            2 => ['email', 'password'],
            3 => ['consent.'],
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

        if ($this->step < 3) {
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

        $payload = [
            'email'    => strtolower(trim($this->email)),
            'password' => $this->password,
            'resident' => $this->form,
        ];

        $this->processing = true;

        try {
            app(RegisterHomeowner::class)->handle($payload);
        } catch (\Throwable $exception) {
            Log::error('Registration failed: ' . get_class($exception));

            $this->processing = false;

            $this->addError('registration', 'We could not complete your registration. Please try again, or contact the office.');

            return;
        }

        $this->limiter()->hit($this->throttleKey(), self::REGISTRATION_DECAY);

        $this->processing = false;
        $this->registered = true;

        $this->reset('password', 'password_confirmation');
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
            return 1;
        }

        if (Str::startsWith($field, 'consent.')) {
            return 3;
        }

        return 2;
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

    // ===== Render =====

    public function render()
    {
        return view('livewire.auth.register-wizard', $this->query->options() + [
            'accountEmail' => $this->email,
        ]);
    }
}
