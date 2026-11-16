# Agent Prompt: Portal Registration, Phone Input, Email Verification, Profile Verification

Read the AGENTS.md and follow it strictly.

---

## 0. What this change is

Four connected changes to the homeowner side of the system:

1. `/portal/register` shrinks from five steps to three — **Personal information → Account → Review and consent** — with only eight personal inputs.
2. One Philippine mobile number input, shared by registration, the portal profile screens and the admin resident create/edit modal.
3. A self-registered account opens after the **email is verified**, not after office approval. The verification link lands on the login page.
4. A signed-in but unverified-profile resident sees only the **onboarding form** (complete personal information, household and address, valid ID). Saving it puts the account in a queue the **admin approves or rejects**; only approval unlocks the rest of the portal.

Registration no longer asks for an address, a household or a valid ID — those move to step 4. Registration no longer writes a `Property` or a `Household`; it writes the `Resident` (personal information only) and the `User`.

Read before writing code: `AGENTS.md` §Domain rules 1, 5, 8, 11, 13; `CONVENTIONS.md` §1, §6, §7, §8, §11.

---

## 1. Registration wizard: three steps

### 1.1 Steps and fields

Steps, in this order and with these labels:

| Step | Name | Fields |
|---|---|---|
| 1 | Personal information | `surname`, `first_name`, `middle_name`, `suffix`, `mobile_no`, `birth_date`, `sex`, `civil_status` |
| 2 | Account | `email`, `password`, `password_confirmation` |
| 3 | Review and consent | the summary, `consent.terms`, `consent.privacy` |

Required: every step-1 field **except** `middle_name` and `suffix`. Married civil status **does not** ask for `spouse_name` on this screen.

Nothing else appears in step 1. Remove from the registration screen: `birth_place`, `religion`, `citizenship`, `occupation`, `educational_attainment`, `sectors`, `spouse_name`, and the whole address, household and valid-ID steps.

### 1.2 Files

- `app/Http/Livewire/Auth/RegisterWizard.php`
- `resources/views/livewire/auth/register-wizard.blade.php`
- `app/Http/Requests/Auth/RegistrationRequest.php`
- `app/Queries/Auth/RegistrationFormQuery.php`
- `app/Http/Controllers/Auth/RegisterController.php` (only its docblock: it says five steps)
- `resources/lang/en/auth.php`

### 1.3 `RegisterWizard.php`

- `$step` now runs 1–3; update `stepRules()` and `stepOfFirstError()` to the three steps and the three prefixes.
- Delete, because the fields are gone: `property`, `household`, `idFile`, `link_household`, `address`, `WithFileUploads`, `propertyPayload()`, `lookupUnit()`, `resolveAddress()`, `newUnitIsComplete()`, `householdIsComplete()`, `updated()`.
- Keep `$registered`, the rate limiter, `processing`, `next()`, `back()`, `edit()`, `submit()` — minus the address step checks inside `next()`.
- `submit()` builds: `['email' => strtolower(trim($this->email)), 'password' => $this->password, 'resident' => $this->form]` and calls `app(RegisterHomeowner::class)->handle($payload)`.
- Do **not** set `pendingApproval`; the account is not approved by an officer any more (task 3).
- The component stays under 200 lines.

### 1.4 `RegistrationRequest.php`

Stop inheriting `(new ResidentRequest)->rules()` — that list now carries fields the wizard must not ask for. Write the rules out explicitly:

```
form.surname      required string max:255 regex:/^[^\d]+$/u
form.first_name   required string max:255 regex:/^[^\d]+$/u
form.middle_name  nullable string max:255 regex:/^[^\d]*$/u
form.suffix       nullable string max:50  regex:/^[^\d]*$/u
form.mobile_no    required string max:11  new PhilippineMobileNumber
form.birth_date   required date before:today after:1900-01-01
form.sex          required Rule::in(Sex::values())
form.civil_status required Rule::in(CivilStatus::values())
email             required string email max:255 new EmailAvailable
password          required string confirmed new PasswordPolicy
consent.terms     accepted
consent.privacy   accepted
```

No `spouse_name` rule, no ID rule, no property/household rule.

### 1.5 `RegistrationFormQuery.php`

- `blankForm()` returns only the eight personal keys, all `''`.
- `options()` returns `sexOptions` and `civilStatusOptions` only.
- Delete `primaryAddress()`, `propertyAt()`, `householdAt()`, `initials()` — the wizard was their only caller.
- Delete `tests/Feature/Public/RegistrationTest.php`'s `propertyAt()` assertions with the rest of that test's rewrite (task 6).

### 1.6 `register-wizard.blade.php`

- `$steps` becomes the three steps; the left progress list and card header stay as they are.
- Step 1: surname, first name, middle name, suffix in a `row`; then `birth_date`, `mobile_no` (as `<x-adminlte-input-contact>`), `sex`, `civil_status`. Use the existing Bootstrap 4 / AdminLTE 3.2 markup already in the file. Drop the spouse block and every field listed in §1.1.
- Step 2: email, password, password confirmation, and **under the password input** a small `text-muted` note listing what the password must contain. Take the requirements from `App\Queries\Settings\SecuritySettingsQuery::passwordRequirements()` (inject it in `boot()` or read it through `RegistrationFormQuery`) and render them from lang keys — never hard-code "6 characters" in the view:

```blade
<small class="form-text text-muted">
    {{ __('auth.password_requirements', ['min' => $requirements['min_length']]) }}
    @if ($requirements['upper']) {{ __('auth.password_requirement_upper') }} @endif
    ...
</small>
```

The note must match what `App\Rules\PasswordPolicy` enforces: `min_length`, `upper`, `lower`, `number`, `symbol`.
- Step 3: a summary table of the personal fields and the account email, each with an `Edit` link (`wire:click.prevent="edit(1)"`, `edit(2)`), then the two consent checkboxes and their modals exactly as they are.
- After submit (`$registered`), the card says: a verification link was emailed to the address they gave, they must open it and click the button, and only then can they sign in. Keep the `Go to Login` button. Use new lang keys under the existing `registered_*` group.

---

## 2. One Philippine mobile number input

### 2.1 `public/js/contact-input.js`

Rewrite the file. Behaviour, on every `input` event on `[data-thha-contact]`:

1. Keep the digits only.
2. Drop a leading `63` and replace it with `0`, so a pasted `+639171234567` becomes `09171234567`.
3. If the first digit is not `0`, put `0` in front of it, so the field always starts with `0`.
4. Stop at 11 digits.
5. Write the cleaned value back only when it differs, and never leave a value the user cannot fix (an empty field stays empty; the leading `0` appears as soon as the first digit is typed).

Keep the IIFE + jQuery structure and the `data-thha-contact` selector the layouts already load (`resources/views/layouts/admin.blade.php`, `resources/views/layouts/portal.blade.php`, `resources/views/auth/user/register.blade.php`).

### 2.2 `resources/views/vendor/adminlte/components/form/input-contact.blade.php`

This copy is ours — it was already changed for THHA, so edit it. The `input` gets:

- `type="tel"`, `inputmode="numeric"`, `autocomplete="tel"`, `data-thha-contact`
- `minlength="11"`, `maxlength="11"`, `pattern="09[0-9]{9}"`
- `placeholder="09XXXXXXXXX"` when the caller passes none
- the caller's `wire:model*`, `id`, `name`, `value` and error class all still work through `$attributes` / `$getOldValue` as they do today.

`app/View/Components/Adminlte/Form/InputContact.php` needs no change.

### 2.3 Call sites

Every place that collects a contact number already renders `<x-adminlte-input-contact>`, so the rewritten input reaches them all. Check each one and change nothing else:

- `resources/views/livewire/auth/register-wizard.blade.php` — registration (step 1, task 1)
- `resources/views/livewire/portal/personal-information/personal-information-form.blade.php` — the portal's profile settings form; the contact number lives here, not on `portal/profile/profile-details.blade.php`
- `resources/views/livewire/portal/household-property/modals/partials/member-resident-fields.blade.php`
- `resources/views/livewire/admin/homeowners/partials/personal-info-fields.blade.php` — the resident create/edit modal
- `resources/views/livewire/portal/onboarding/profile-wizard.blade.php` — the new onboarding form (task 4)

Remove any `maxlength`, `pattern` or `placeholder` these call sites pass that contradicts §2.2.

### 2.4 Validation

`app/Http/Requests/Homeowners/ResidentRequest.php`: `form.mobile_no` becomes `['nullable', 'string', 'max:11', new PhilippineMobileNumber]`. This is the one rule the admin resident modal and the portal member modal both read. Registration keeps its own required rule (task 1). `App\Rules\PhilippineMobileNumber` stays as it is.

---

## 3. The account opens on email verification

### 3.1 Model

`app/Models/User.php`: `implements MustVerifyEmail` (`Illuminate\Contracts\Auth\MustVerifyEmail`). `email_verified_at` already exists in the migration and in `$casts`.

### 3.2 `app/Actions/Auth/RegisterHomeowner.php`

- `handle(array $data): User` — no `UploadedFile` argument any more.
- One transaction: `Resident::create(Resident::payloadFrom($data['resident']))`, then `User::create([... 'resident_id', 'email', 'password' => Hash::make(...), 'password_changed_at' => now(), 'terms_accepted_at' => now(), 'privacy_accepted_at' => now(), 'registration_status' => RegistrationStatus::APPROVED, 'is_active' => true])`.
- Remove `propertyFor()`, `createProperty()`, `attachHousehold()`, `filled()`, `autoAccept()`, `discardStoredId()`, `$storedIdPath`, the ID-upload and property/household logic, and the `RegistrationReceived` mail.
- After the transaction returns: `$user->sendEmailVerificationNotification()` in its own `try/catch` (`Log::error(...)`) and the existing `NotificationService::toOfficers(new HomeownerRegisteredNotification($user))` in its own `try/catch`. Never send mail inside the closure.
- Delete `app/Mail/RegistrationReceived.php` and its view — nothing else uses it.

### 3.3 Verification route

`app/Http/Controllers/Auth/VerifyEmailController.php`, one `verify($id, $hash)` method:

- `User::findOrFail($id)`; abort 403 unless `hash_equals(sha1($user->getEmailForVerification()), $hash)`.
- The signature itself is checked by the `signed` middleware on the route, not by the controller.
- If `! $user->hasVerifiedEmail()`, `$user->markEmailAsVerified()`.
- Do **not** sign the account in. Redirect to `redirect()->route('login')->with('status', __('auth.verified_sign_in'))`.
- No Livewire component, no view.

Routes in `routes/web.php`, beside the other auth routes:

```php
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware('signed')->name('verification.verify');
```

That name and shape is what Laravel 7's `VerifyEmail` notification builds its link from. Remove the signed-in resend route if you are tempted to add one — it is not part of this task.

### 3.4 Sign-in

- `app/Actions/Auth/SignInAccount.php`: replace the `PENDING` outcome with `UNVERIFIED`, and add `INACTIVE` for an account that exists but is switched off (`Admin` or `User` without `is_active`), which today also returns the old `PENDING`. A homeowner whose `hasVerifiedEmail()` is false returns `UNVERIFIED`.
- `app/Http/Controllers/Auth/LoginController.php`: `UNVERIFIED` → back to the form with `auth.verify_email` and the typed email; `INACTIVE` → back with the existing `auth.inactive` message; keep `FAILED` and the throttle as they are. Drop the `registration.pending` redirect.
- `resources/lang/en/auth.php`: `verify_email`, `verified_sign_in`, and the password requirement keys from §1.6.

### 3.5 Delete the office approval of registrations

Nothing can be `pending` any more, so this code is dead and must go:

- `app/Http/Controllers/Auth/PendingApprovalController.php`, its route `registration.pending`, and `resources/views/auth/pending-approval.blade.php`
- `app/Actions/Homeowners/Users/AcceptRegistration.php`
- `app/Http/Livewire/Admin/Homeowners/Homeowner/PendingRegistrationTableList.php` and `resources/views/livewire/admin/homeowners/homeowner/pending-registration-table-list.blade.php`
- `app/Http/Livewire/Admin/Homeowners/Homeowner/Modals/PendingRegistrationViewModal.php` and its view
- `app/Queries/Homeowners/Homeowners/PendingRegistrationListQuery.php`
- `HomeownerController::pendingRegistrations()`, the route `homeowners.pending-registrations`, `resources/views/admin/homeowners/pending-registrations.blade.php`, and any link to it in the admin views
- `tests/Feature/PendingRegistrationsTest.php`
- the `AUTO_ACCEPT_REGISTRATION` setting: `AuthenticationSettingsQuery`, `app/Actions/Settings/Authentication/UpdateAuthenticationSettings.php`, `app/Http/Livewire/Admin/Settings/AuthenticationSettings.php`, `resources/views/livewire/admin/settings/authentication-settings.blade.php`, and `tests/Feature/Public/RegistrationSettingsTest.php`'s coverage of it

Leave `RegistrationStatus`, the `registration_status` column and its scopes alone; registration writes `APPROVED` and nothing writes `PENDING` any more.

---

## 4. Onboarding before the services, and the admin verification

### 4.1 Status on the account

New enum `app/Enums/ProfileStatus.php`, in the shape of the enums in the project (`class ProfileStatus extends Enum`, constants, `labels()`, `label()`, `badgeTheme()`, and `isVerified(): bool` / `isSubmitted(): bool` helpers):

| Constant | Value | Label | Badge |
|---|---|---|---|
| `INCOMPLETE` | `incomplete` | Profile incomplete | `secondary` |
| `SUBMITTED` | `submitted` | Waiting for the office | `warning` |
| `VERIFIED` | `verified` | Verified | `success` |
| `REJECTED` | `rejected` | Needs correction | `danger` |

Column additions in the existing migration `database/migrations/2026_00_03_000001_create_users_table.php` (not a new migration):

```php
$table->string('profile_status', 20)->default('verified');
$table->timestamp('profile_submitted_at')->nullable();
$table->timestamp('profile_verified_at')->nullable();
$table->foreignId('profile_verified_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
$table->string('profile_rejection_note')->nullable();

$table->index('profile_status', 'idx_users_profile_status');
```

`AGENTS.md` §5.3 says never edit a shipped migration; the instruction in this task overrides it because the database has not shipped. Do not run `migrate:fresh`, `db:wipe` or a rollback: say in your report that the local database has to be rebuilt by the developer. The test suite runs on `:memory:` SQLite and is unaffected.

The default is `verified`, not `incomplete`, so that accounts already in the database and accounts written by any other path keep working; the self-registration Action writes `INCOMPLETE` itself. Mirror the default on the model too (`protected $attributes = ['profile_status' => 'verified'];`), because an instance just built in code has not read the column's default back.

`User`: add the five columns to `$fillable`, cast `profile_status` with `EnumCast::class . ':' . ProfileStatus::class` and the timestamps to `datetime`, add `hasCompletedProfile(): bool` (`profile_status->isVerified()`) and a `scopeAwaitingProfileVerification()`.

An account the office opens itself is already profiled by the officer, so `app/Actions/Homeowners/Users/CreateUserAccount.php` writes `profile_status = VERIFIED`, `profile_verified_at = now()` and `profile_verified_by_admin_id = $by->id`. Only a self-registered account goes through onboarding.

### 4.2 The gate

`app/Http/Middleware/EnsureProfileIsVerified.php`, alias `profile.verified` in `app/Http/Kernel.php`:

- Read the account from `auth('homeowner')`.
- Redirect to `route('portal.onboarding')` unless `profile_status` is `VERIFIED`.
- No inline role checks; it reads the account's own state only.

`routes/portal.php`: the onboarding page and the account's own details are reachable before verification; everything else waits.

- Small group (same `auth:homeowner`, `account.active:homeowner`, `password.expiry:homeowner,portal.profile` as today): logout, password confirm, `GET onboarding` → `OnboardingController@index` named `portal.onboarding`, `GET account-details`, `GET notifications*`, `GET manual/{topic?}`.
- The big group keeps the rest and adds `'profile.verified'` to its middleware array.

### 4.3 The onboarding form

- `app/Http/Controllers/Portal/OnboardingController.php`: thin, `authorize('updateOwn', auth('homeowner')->user())`, returns `view('portal.onboarding')`; the page view holds `@livewire('portal.onboarding.profile-wizard')` like `resources/views/portal/personal-information.blade.php` does.
- `app/Http/Livewire/Portal/Onboarding/ProfileWizard.php` extends `PortalComponent`, uses `WithFileUploads` and `ResolvesHomeownerAccount`, and holds three steps:

| Step | Fields |
|---|---|
| 1 Personal information | `philsys_card_no` (optional), `surname`, `first_name`, `middle_name`, `suffix`, `birth_date`, `birth_place`, `sex`, `civil_status`, `spouse_name` (required when married), `citizenship`, `religion`, `occupation`, `mobile_no`, `educational_attainment`, `sectors` |
| 2 Household and address | `property.block`, `property.lot`, `household.type`, `household.prev_address`; the association address from `Address::primary()` shown read-only |
| 3 Valid ID | `idFile` (JPG/PNG, max 5120) plus the submit button |

  Required: everything except `philsys_card_no`, `middle_name`, `suffix`, `religion`, `occupation`, `educational_attainment`, `sectors`, `household.prev_address`. `mobile_no` uses `<x-adminlte-input-contact>`, the ID input keeps the plain `custom-file` markup the old registration step 4 had.
- `app/Http/Requests/Portal/Homeowners/SubmitProfileRequest.php` holds the rules; the component reads them in `rules()` the way `RegisterWizard` reads `RegistrationRequest`.
- View `resources/views/livewire/portal/onboarding/profile-wizard.blade.php`: the same three-step layout, progress list and Back/Continue/Submit bar as the registration wizard, and a notice when the profile is `SUBMITTED` ("waiting for the office") or `REJECTED` (show `profile_rejection_note`) that lets the resident correct and resubmit.
- Every Livewire method resolves the account through the trait and passes it to the Action; no `where('user_id', ...)`.

### 4.4 `app/Actions/Homeowners/Profiles/SubmitProfileVerification.php`

`handle(User $account, array $data, $idFile): User`, one transaction, then side effects after it returns:

1. `Resident::payloadFrom($data['form'])` updates the account's resident.
2. The unit: `Property::query()->atUnit($block, $lot)->first()`, created under `Address::query()->primary()->first()` when the office has no record of it (throw if the association address is missing).
3. The household: if the property has no household, `Household::create(Household::payloadFrom([...]))` and place the resident as head (`placeHead`); if it has one whose head is someone else, add the resident as a member (`HouseholdMember::create(['is_head' => false])`); if the resident already belongs to it, leave the membership as it is.
4. The ID: `StorageHelper::storePrivate($idFile, 'ids/residents')` on `residents.valid_id_path`, replacing any previous file.
5. Account: `profile_status = SUBMITTED`, `profile_submitted_at = now()`, `profile_verified_at = null`, `profile_verified_by_admin_id = null`, `profile_rejection_note = null`.

After commit: `NotificationService::toOfficers(new ProfileSubmittedForVerification($account))` in its own `try/catch`. Portal actions write no `admin_activities`.

### 4.5 The admin side

- **Queries** — `app/Queries/Homeowners/Profiles/ProfileVerificationListQuery.php` (`paginate(array $criteria)`, submitted first, `with('resident')`, searchable by name/email, paginated) and `ProfileVerificationDetailQuery.php` (`build(User $account)` returning the personal fields, the unit, the household and the ID route).
- **Screen** — route `GET homeowners/profile-verifications` in `routes/admin.php` beside the other `homeowners.` routes → `HomeownerController::profileVerifications()` → `resources/views/admin/homeowners/profile-verifications.blade.php` holding `@livewire('admin.homeowners.verification.profile-verification-table-list')`.
- **Components** — `app/Http/Livewire/Admin/Homeowners/Verification/ProfileVerificationTableList.php` (+ view) and `Modals/ProfileVerificationViewModal.php` (+ view), in the shape of the existing resident table list and its view modal: the submitted profile, the unit and household, a link to the private ID through the existing `route('homeowners.residents.valid-id', $resident)`, and an Approve button and a Reject button with a required `note`.
- **Actions** — `VerifyProfile::handle(User $account, Admin $by)` and `RejectProfile::handle(User $account, string $note, Admin $by)`. They re-authorize inside the component before calling (`$this->authorize('verifyProfile', $account)`), write the status columns, and log with `ActivityLogService` after the write (`'verified'` / `'rejected'`, subject `'homeowner'`). After commit, `NotificationService::toHomeowner($account, new ProfileVerifiedNotification($account))` or `ProfileRejectedNotification` in its own `try/catch`.
- **Policy** — `app/Policies/UserPolicy.php`: `viewProfileVerifications(Admin $admin)` and `verifyProfile(Admin $admin, User $account)`, both riding `$admin->hasPermission('Homeowners', 'Edit')`. Use the `// ===== Admin =====` / `// ===== Portal =====` dividers the file already has.
- **Menu** — `config/adminlte.php`, Homeowners submenu, an entry `Profile Verification` with `'route' => 'homeowners.profile-verifications', 'can' => 'viewProfileVerifications', 'model' => App\Models\User::class`.
- **Notifications** — `app/Notifications/ProfileSubmittedForVerification.php` (extends `OfficerNotification`), `ProfileVerifiedNotification.php` and `ProfileRejectedNotification.php` (extend `ThhaNotification`, `via` = `['database', 'mail']`), named and shaped like `RegistrationApprovedNotification`.

### 4.6 An edit after verification

A resident who edits personal information, the unit or the household from the portal (`PersonalInformationForm`, `HouseholdPropertyDetails` and the household modals, through `UpdateResident`, `UpdateHousehold`, `UpdateHouseholdFacts`, `CreateOwnHousehold`) puts the account back in the queue: in those Actions, when the account's `profile_status` is `VERIFIED`, set it to `SUBMITTED`, stamp `profile_submitted_at`, clear `profile_verified_at`, `profile_verified_by_admin_id` and `profile_rejection_note`. Admin-side edits by the office do not change the status.

---

## 5. Language files

Registration, verification and password-requirement strings are keys in `resources/lang/en/auth.php`, because the wizard already reads its text from there. The onboarding strings go under an `onboarding` key in `resources/lang/en/auth.php`; the profile-verification screens get a `verification` section in `resources/lang/en/users.php`, shaped like the rest of that file: labels, statuses, buttons, flash and notification text. Do not add a `resources/lang/en/homeowners.php`: on Windows the filesystem is case-insensitive, so `__('Homeowners')` in `resources/views/admin/home.blade.php` resolves to it and the admin dashboard 500s. Do not go back and move text that is already inline elsewhere in the project; only the strings this task adds are keyed.

---

## 6. Tests

- Rewrite `tests/Feature/Public/RegistrationTest.php` for the three steps: only the eight personal fields are collected, a registration writes one `Resident` and one `User` and **no** `Property`/`Household`, the verification mail is sent, and the account cannot sign in until it is verified. Drop the address, household and ID cases.
- Delete `tests/Feature/PendingRegistrationsTest.php` and the `auto_accept_registration` case in `tests/Feature/Public/RegistrationSettingsTest.php`.
- Add `tests/Feature/ProfileVerificationTest.php` (beside `PortalDashboardTest.php`, where the other portal feature tests live): an unverified account is redirected to `portal.onboarding` from every gated route, a submission moves the status to `submitted`, an officer approves → `verified` and the services open, an officer rejects → `rejected` with a note and the resident sees the note, and a verified resident saving a change moves the status back to `submitted`.
- Add a case to `tests/Feature/Actions/Homeowners/` (beside the other Action tests) for `SubmitProfileVerification`, `VerifyProfile` and `RejectProfile`.

---

## 7. Verification you must run

```bash
php -l app/Http/Livewire/Auth/RegisterWizard.php   # and every other changed PHP file
php artisan route:list --name=verification
php artisan route:list --name=portal.onboarding
php artisan test --filter='RegistrationTest|ProfileVerificationTest|PublicPagesTest|UnifiedAuthTest'
```

`vendor/bin/pint` is not installed here; do not add it. Do not run any migration command.

---

## 8. What to report

Per `AGENTS.md` §Finishing: what changed (file by file, grouped by the four tasks), the commands you ran and their result, what you could not check (the local database rebuild), and any defect you found in untouched code as `file:line`, what, why.

---

Touch only what is required to be rewritten or to be made.

Dont add any //comments except for /** functions description and one line //comments for seperating between codes

Build with simple, minimalistic, structural, architectural and understandable codes and program.

If a specific table in database are required to change then specifically do it in that migration instead of creating a new migration to handle it.
