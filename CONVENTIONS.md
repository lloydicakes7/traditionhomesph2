# CONVENTIONS.md: THHA

How code is written in this project: **PHP 8.1 · Laravel 7.30 · Livewire 2.12 · AdminLTE 3.2 (`jeroennoten/laravel-adminlte`) · Blade · plain CSS · jQuery + Bootstrap 4**.

[`AGENTS.md`](./AGENTS.md) says **what** the system is and **why**. This file says **how** every file is written.

Rule of thumb: if two ways work, pick the simpler and more readable one. If code you touch breaks a rule here, fix it in the same change (unless the task explicitly limits scope).

| Section | Topic |
|---|---|
| 1 | Version guardrails |
| 2 | Naming |
| 3 | Project layout (modules, tree, model groups, where does it go) |
| 4 | File anatomy and comments |
| 5 | Models, enums, database |
| 6 | Requests, controllers, routes |
| 7 | Actions, Queries, Services, Utility |
| 8 | Livewire architecture (CRUD, table, search, stats, form, uploads) |
| 9 | Livewire performance |
| 10 | Blade, the `app/View` layer, AdminLTE, UI consistency |
| 11 | Authentication and authorization |
| 12 | Observers and notifications |
| 13 | Assets |
| 14 | PDFs and exports |
| 15 | Lang files |
| 16 | Security and privacy |
| 17 | Testing |
| 18 | Git and quality gates |

---

## 1. Version Guardrails

The framework is old. Most tutorials, snippets, and AI suggestions target newer versions. **Do not write any of the following.**

**PHP:** target **PHP 8.1+** (native enums). `composer.json` must say `"php": "^8.1"` before enums are used. Constructor promotion, `match`, nullsafe `?->`, named arguments, `readonly` properties, and typed properties are allowed in Actions, Services, Utility, and Models. **Do not** use `readonly` or constructor promotion on Livewire components (Livewire 2 hydrates public properties by reflection).

### Laravel 8-12 features that do not exist in Laravel 7

| Do not write | Write this instead |
|---|---|
| `@checked`, `@selected`, `@disabled`, `@readonly`, `@required`, `@class`, `@style` | `{{ $on ? 'checked' : '' }}` or a Blade component |
| `to_route()` | `redirect()->route()` |
| `Rule::enum()` | `Rule::in(Status::values())` |
| `casts(): array` method, `Attribute::make()` accessors | `$casts` property, `getXAttribute()` / `setXAttribute()` |
| Native enum in `$casts` | Enum uses the `CastsAsEnum` trait (section 5.2) |
| `#[ObservedBy]`, `ShouldHandleEventsAfterCommit` | `Model::observe()` in `AppServiceProvider::boot()` |
| `afterCommit`, `$afterCommit`, `DB::afterCommit()` | Send notifications from the **Action** after `DB::transaction()` returns |
| Factory classes, `HasFactory`, `Database\Seeders` namespace | Closure factories `factory(Model::class)`, seeders in `database/seeds` |
| `Model::preventLazyLoading()` | Eager load by habit; assert query counts in tests (section 17) |
| `withSum()`, `withAvg()`, `withExists()`, `->sole()`, `str()` helper | `withCount()`, `selectRaw` with a bound subquery, `Str::` |
| `Cache::flexible()`, `Number::`, `Concurrency::`, `defer()`, `Context` | `Cache::remember()`, `MoneyHelper`, queued jobs |
| `lang_path()`, `useLangPath()` | `resource_path('lang')` (the default) |
| `Route::controller()`, `Route::get('/', Controller::class)` | `Route::get('/', [Controller::class, 'index'])` |
| `$request->string()`, `$request->date()` | `$request->input()`, `Carbon::parse()` |
| `<x-layouts.app>` component layouts with `$slot`, `Blade::componentNamespace()` | `@extends` layouts; class components resolve from `app/View/Components` automatically |
| Vite, `@vite` | Plain files in `public/` (section 13) |

### Livewire 3 features that do not exist in Livewire 2

| Do not write | Write this instead |
|---|---|
| `App\Livewire\...` namespace, `app/Livewire/` folder | `App\Http\Livewire\...`, `app/Http/Livewire/` |
| `#[Computed]`, `#[On]`, `#[Rule]`, `#[Layout]`, `#[Url]`, `#[Locked]`, `#[Lazy]` | `getXProperty()`, `$listeners`, `rules()`, `->extends()->section()`, `$queryString`, `protected` properties, `wire:init` (section 9.2) |
| `<livewire:x lazy />`, `placeholder()` | `wire:init="load"` plus a skeleton (section 9.2) |
| `$this->dispatch('x')` | `$this->emit('x')` or `$this->dispatchBrowserEvent('x')` |
| `wire:model.live`, `wire:model` meaning deferred | In v2 `wire:model` is **live**. Use `.defer` for forms, `.lazy`, `.debounce.400ms` |
| `wire:navigate`, `@persist`, `$wire` in JS, `Livewire\Form` objects | Plain links, `window.livewire` / `Livewire.emit()`, plain public arrays |
| `wire:model.blur`, `wire:confirm`, `wire:offline` (v3 spelling) | `.lazy`, `data-thha-confirm` (section 8.12), `wire:offline` only if the installed 2.x supports it |
| Alpine (`x-data`, `x-show`, `@click`) | jQuery + Bootstrap 4 JS. Livewire 2 does not bundle Alpine and this project does not install it |
| `#[Reactive]`, `#[Modelable]` | Parent passes props through `mount()` and listens to child events |

### Bootstrap 5 classes that do not exist in AdminLTE 3.2 (Bootstrap 4)

| Do not write | Write this instead |
|---|---|
| `ms-*`, `me-*`, `ps-*`, `pe-*` | `ml-*`, `mr-*`, `pl-*`, `pr-*` |
| `text-start`, `text-end`, `float-start`, `float-end` | `text-left`, `text-right`, `float-left`, `float-right` |
| `fw-bold`, `fs-5` | `font-weight-bold`, `h5` |
| `form-select`, `form-check-input` on inputs | `form-control`, `custom-select`, `custom-control-input` |
| `badge bg-success` | `badge badge-success` |
| `btn-close`, `data-bs-*` | `close`, `data-*` (`data-toggle`, `data-dismiss`, `data-target`) |
| `gap-*`, `g-*`, `row-cols-*` | Grid columns + margin utilities |

---

## 2. Naming

| Thing | Convention | Example |
|---|---|---|
| PHP classes | PascalCase | `RecordPayment` |
| Models | Singular PascalCase, inside a group folder (section 3.5) | `Models\Admin\AdminActivity` |
| Account models | Exactly two | `Admin`, `User` |
| Tables | Plural snake_case | `property_occupancies` |
| Columns | snake_case | `birth_date` |
| Booleans | `is_` / `has_` prefix | `is_active` |
| Date vs datetime columns | `_on` = date, `_at` = datetime | `started_on`, `paid_at` |
| Foreign keys | `{model}_id`; admin audit `{verb}_by_admin_id` | `property_id`, `issued_by_admin_id` |
| Enums | `{Thing}`, `{Thing}Status`, `{Thing}Type`; cases PascalCase, values snake_case | `BillStatus::Unpaid` = `'unpaid'` |
| Actions | `{Verb}{Noun}`, one use case, one `handle()` | `RecordPayment`, `AssignPropertyOwner` |
| Queries | `{Noun}{Purpose}Query`: gives a view its data | `ResidentListQuery`, `BillStatsQuery`, `ReceiptQuery` |
| Services | `{Capability}Service`, in `Services/{Area}/`. Never a table, model, or module name | `PasswordSetupService`, `ActivityLogService`, `PsgcService`, `BillSettlementService` |
| Utility | `{Noun}Helper`. No models, no enums | `MoneyHelper`, `CacheHelper`, `FifoHelper` |
| Controllers | `{Thing}Controller` or invokable `{Verb}{Thing}Controller` | `Admin\Collections\ReceiptController` |
| Livewire page | `{Model}Index`, `{Model}Form`, `{Model}Show` | `BillIndex`, `BillForm`, `BillShow` |
| Livewire part | `{Model}Stats`, `{Model}{Tab}Tab`, `{Model}{Purpose}Modal` | `BillStats`, `ResidentDocumentsTab` |
| Livewire shared | Generic noun, in `Shared/` | `UploadAvatar`, `ResidentPicker` |
| Livewire trait | `With{Thing}` in `Livewire/Concerns/` | `WithSearch`, `WithSorting` |
| Livewire view | kebab-case of class | `bill-index.blade.php` |
| Livewire tag name | dotted path | `admin.collections.bill-index` |
| Form Requests | `{Verb}{Thing}Request` | `StoreResidentRequest` |
| Rules | Names what is valid | `PhilsysCardNumber` |
| Observers | `{Model}Observer` | `BillObserver` |
| Notifications | Past-tense event | `PaymentRecorded` |
| Policies | `{Model}Policy` | `BillPolicy` |
| Policy abilities | Admin: `viewAny`, `view`, `create`, `update`, `delete`, domain verbs. Portal: `{verb}Own` | `viewOwn` |
| Permissions | `module.action` | `bills.manage` |
| Blade extension classes | `{Theme}Blades` in `app/View/Blades/` | `AdminLteBlades` |
| View components (class) | PascalCase in `app/View/Components/{Group}/` | `Adminlte\StatCard` |
| View composers | `{Thing}Composer` in `app/View/Composers/` | `BrandingComposer` |
| Lang files | lowercase module group | `residents.php` |
| Lang keys | snake_case, dot path | `residents.status.active` |
| Route names | `{side}.{resource}.{action}` | `admin.residents.index`, `portal.bills.show` |
| URLs | kebab-case plural | `/admin/bank-reconciliations` |
| Blade files and folders | kebab-case | `admin/homeowners/resident-index` |
| Blade tags (ours) | `<x-ui.*>`, `<x-form.*>`, `<x-table.*>`, `<x-adminlte.*>` | `<x-ui.status-badge>` |
| Blade tags (package) | `<x-adminlte-*>` (dash, not dot). Never confuse the two | `<x-adminlte-input>` |
| CSS classes | `thha-` prefix, kebab-case | `thha-stat-card` |
| CSS / JS files | kebab-case | `components.css`, `toast.js` |
| JS | camelCase functions, one global `window.Thha` | `Thha.toast()` |
| Livewire events | kebab-case, `{model}-{past-verb}` | `resident-saved` |
| Browser events | `thha:` prefix | `thha:toast` |
| Migrations | `create_{table}_table`, `add_{column}_to_{table}_table` | `add_tenure_status_to_households_table` |

---

## 3. Project Layout

### 3.1 The three rules

1. **UI surfaces are side-first.** Anything a person sees or a URL reaches is grouped by `Admin/`, `Portal/`, `Public/` and then by module.
2. **Business code is domain-first.** Actions, Queries, Services, Models, Policies, Enums, Observers, and Notifications are grouped by domain, never by side, so the admin panel and the portal share one implementation of every rule.
3. **Shared UI lives in `Shared/` or `app/View/`.** A Livewire component used by more than one side goes in `Livewire/Shared/`. A Blade building block goes in `app/View/Components/` (class) or `resources/views/components/` (anonymous).

The side is named **Portal** everywhere (URL `/portal`, folders `Portal/`, routes `portal.*`). `Client` and `Homeowner` as side names are legacy. The guard is still called `homeowner`.

### 3.2 Modules

Use these module names for folders (Actions, Queries, Livewire, Controllers, Requests, views), lang files, and route names. Do not invent new ones without adding them here.

`Dashboard` · `Homeowners` (residents, households, properties, occupancies, link requests) · `Rbi` · `Collections` (bills, payments, deposits, products) · `Accounting` · `Expenses` · `Amenities` · `Support` (requests, complaints, violations) · `HoaCenter` (announcements, directory, officers) · `Reports` · `Settings` · `Access` (admins, roles, activity log) · `Auth`

### 3.3 Tree

```
app/
  Actions/{Module}/            {Verb}{Noun}.php
  Casts/                       EnumCast only if a Castable trait cannot be used
  Console/Commands/            PurgeTempUploads.php
  Enums/                       flat, one file per enum; Concerns/CastsAsEnum.php
  Events/  Listeners/          BuildAdminLteMenu listener + real domain events only
  Exceptions/
  Http/
    Controllers/
      Auth/                    LoginController, LogoutController, RegisterController, SetPasswordController,
                               ForgotPasswordController, ResetPasswordController, PendingApprovalController
      Auth/Admin/  Auth/Portal/   officer-only / homeowner-only pages (register, confirm-password)
      Admin/{Module}/          downloads, previews, uploads: things Livewire cannot stream
      Portal/{Module}/
      Public/
    Livewire/
      AdminComponent.php  PortalComponent.php  PublicComponent.php     base classes
      Concerns/                With{Thing} traits shared by components
      Shared/                  guard-agnostic components used on more than one side
      Auth/                    every auth component (RegisterWizard)
      Admin/{Module}/{Model}{Purpose}.php
      Portal/{Module}/...
      Public/...
    Middleware/
    Requests/Auth/             one class per auth form (LoginRequest, ResetPasswordRequest, ...)
    Requests/{Admin|Portal|Public}/{Module}/...
  Models/{Group}/              grouped by aggregate (section 3.5); Concerns/ for shared traits
  Notifications/               Channels/
  Observers/                   flat, {Model}Observer
  Policies/                    flat, {Model}Policy; Concerns/ResolvesAccount.php
  Providers/                   bootstrapping only (incl. ViewServiceProvider)
  Rules/
  Queries/{Module}/            {Noun}{Purpose}Query.php; Concerns/
  Services/{Area}/             standalone capabilities, e.g. Services/Api/PsgcService.php,
                               Services/PrintDocuments/PrintDocumentService.php
  Utility/                     stateless static helpers: no models, no enums, no business meaning
  View/
    Blades/                    AdminLteBlades.php: directives and Blade extensions
    Components/                class-based Blade components, by group:
      Adminlte/                  wrappers around package widgets with THHA defaults
      Ui/                        generic: status badge, money, empty state, page header
      Form/                      THHA form groups
      Table/                     table shell, sortable header, skeleton
    Composers/                 {Thing}Composer: cached data shared with layouts and partials
config/
  adminlte.php                 package config (menu, plugins, layout classes)
  menu/admin.php  menu/portal.php      menu item definitions
database/
  factories/  migrations/  seeds/
public/
  css/                         base.css layout.css components.css print.css  pdf/*.css
  js/                          core.js toast.js modal.js confirm.js bulk.js plugins.js contact-input.js
  image/
  vendor/                      GENERATED by AdminLTE install. Never hand-edit
resources/
  lang/en/                     one file per module + common, enums, validation, notifications
  views/
    layouts/                   admin.blade.php  portal.blade.php  public.blade.php
    livewire/{admin|portal|public|shared}/{module}/{kebab-name}.blade.php
    livewire/auth/             every Livewire auth view (registration wizard, steps, modals)
    auth/                      unified auth pages: login, forgot/reset password, set-password,
                               pending-approval, registration-closed
    auth/admin/  auth/user/    officer-only / homeowner-only auth pages (register, confirm)
    admin/  portal/  public/   Blade-only pages (static pages, dashboards)
    components/{ui,form,table,adminlte}/   anonymous or class-component views
    partials/                  shared fragments that are not components
    pdf/                       one flat view per printable document (pdf/receipt, pdf/rbi-form-a);
                               only pdf/layouts and pdf/partials are folders
    emails/
    vendor/adminlte/           published package views (section 10.3)
routes/
  web.php  admin.php  portal.php  console.php
tests/
  Feature/{Admin|Portal|Public}/   Unit/   Smoke/
```

Nothing lives loose at the root of `app/`. No global helper files; helpers are static classes in `app/Utility`.

**`app/View` is singular.** Laravel resolves class components from `App\View\Components` by convention. A plural `app/Views` folder would break auto-discovery.

### 3.4 Where does it go?

| I need to... | It goes in |
|---|---|
| Add a page | `Http/Livewire/{Side}/{Module}/` + `resources/views/livewire/...` + a route |
| Add a table, stats strip, form, or tab to a page | Its own component beside the page (section 8.3) |
| Add an uploader used in two or more forms | `Http/Livewire/Shared/` (section 8.9) |
| Stream a PDF, Excel, receipt, or file | `Http/Controllers/{Side}/{Module}/` (thin) |
| Validate input | `Http/Requests/` (Livewire components reuse it, sections 6.2 and 8.7) |
| Perform a write (one user-visible use case) | `Actions/{Module}/` |
| Give a view its data (list, detail, stats, select options, report, document data) | `Queries/{Module}/` |
| Add a standalone capability (activity log, journal posting, PDF render, PSGC lookup, numbering, notification dispatch) | `Services/{Area}/` |
| Normalize validated input into column values | A private method in the Action; `Model::payloadFrom()` if Create and Update share it |
| Do a pure calculation with no THHA meaning (FIFO allocation, money, period math) | `Utility/` |
| Reuse a small query fragment (filter, search, status) | Model scope |
| Name a fixed list of values | `Enums/` |
| Do a technical operation (money, files, cache keys, cookies) | `Utility/` |
| Show text | `resources/lang/en/{module}.php` |
| Reuse markup that takes props | `app/View/Components/{Group}/` (logic needed) or `resources/views/components/` (no logic) |
| Reuse a plain fragment | `partials/` |
| Wrap a package widget with our defaults | `app/View/Components/Adminlte/` |
| Add a Blade directive | `app/View/Blades/AdminLteBlades.php` |
| Share data with every page or layout | `app/View/Composers/` (cached) |
| Style something | `public/css/` |
| Add browser behavior | `public/js/` |
| Decide who may do something | `Policies/` |
| React to a model event | `Observers/` |
| Tell a user something happened | `Notifications/` (dispatched by an Action) |
| Print a document | `resources/views/pdf/` (flat, beside `layouts/` and `partials/`) |

### 3.5 Model groups

`app/Models` is **grouped by aggregate**: models that belong together and relate mostly to each other share a folder and a namespace. Example: `Admin` and `AdminActivity` live in `app/Models/Admin/` and are `App\Models\Admin\Admin` and `App\Models\Admin\AdminActivity`.

| Group (folder / namespace) | Models | Module (for Actions, Queries, UI) |
|---|---|---|
| `Admin` | `Admin`, `AdminActivity`, `Role` | Access |
| `System` | `Setting` | Settings |
| `Homeowners` | `Address`, `Property`, `PropertyOccupancy`, `Resident`, `User`, `Household`, `HouseholdMember`, `HouseholdLinkRequest` | Homeowners |
| `HoaCenter` | `DirectoryGroup`, `DirectoryEntry`, `DirectoryPhone`, `HoaMember`, `Announcement` | HoaCenter |
| `Accounting` | `Account`, `JournalEntry`, `JournalLine`, `BankReconciliation`, `BankReconciliationLine`, `AccountTransfer`, `AccountingPeriod` | Accounting |
| `Collections` | `ProductCategory`, `Product`, `Bill`, `BillItem`, `PaymentMethod`, `Payment`, `AdvanceDeposit`, `SoaEmail` | Collections |
| `Amenities` | `Amenity`, `AmenitySchedule`, `AmenityBooking` | Amenities |
| `Expenses` | `Supplier`, `ExpenseCategory`, `Expense`, `ExpenseItem` | Expenses |
| `Support` | `Request`, `Complaint`, `Violation` | Support |

Rules:
- A model belongs to **exactly one** group: the group of the aggregate root it cannot exist without (`AdminActivity` → `Admin`, `BillItem` → `Collections`).
- Group folders mirror the table groups in `database/migrations/`. A new table adds its model to the matching group; a new group needs this table updated.
- Models in a group import models of other groups explicitly. A relationship across groups is normal (`Bill` → `User`); a cycle of *folders* importing each other for logic is a sign the aggregate is split wrong.
- Shared model traits live in `app/Models/Concerns/`. Group-only traits live in `app/Models/{Group}/Concerns/`.
- Enums, Policies, Observers, and factories stay **flat** (one file per model or enum, found by name).
- The `Support\Request` model collides with `Illuminate\Http\Request`. Where both are needed, alias the model: `use App\Models\Support\Request as SupportRequest;`.
- **Moving a model changes its class name.** Anything that stores the class name breaks: `notifications.notifiable_type`, `config/auth.php` provider models, `AuthServiceProvider::$policies`, factories, queued job payloads. Register a morph map so class names never reach the database again:

```php
Relation::morphMap([
    'admin' => Admin::class,
    'user' => User::class,
]);
```

---

## 4. File Anatomy and Comments

### 4.1 Comment policy

**No comments.** Names, types, and small methods carry the meaning. Reasoning that cannot be expressed in code goes in this file or `AGENTS.md`, never in a comment.

The only allowed comments are **section dividers**:

| File type | Divider |
|---|---|
| PHP, JS | `// ===== Section =====` |
| CSS | `/* ===== Section ===== */` |
| Blade | `{{-- ===== Section ===== --}}` |

Rules:
- Title Case, one to three words, taken from the vocabulary below.
- A blank line before and after. Same indent as the code it introduces.
- Use a divider only when it separates two or more members. Never a divider over a single method.
- Also allowed: tool directives (`@phpstan-ignore-next-line`, `phpcs:ignore`), and the comments that ship inside framework/package files you did not write (`config/*.php` stubs, `resources/views/vendor/**`, `public/vendor/**`).
- Banned: `// TODO`, `// fix later`, commented-out code, docblocks that restate a signature, `/** @var */` unless a tool cannot infer the type, HTML comments in Blade (`<!-- -->`).

### 4.2 Section vocabulary

| File type | Sections, in this order |
|---|---|
| Model | `Setup` (traits, `$fillable`, `$casts`, `$hidden`) · `Relationships` · `Scopes` · `Accessors` · `Mutators` · `Helpers` |
| Action | `Handle` · `Helpers` (a single `handle()` with no helpers needs no divider) |
| Query | `Queries` · `Helpers` |
| Service | `Operations` · `Helpers` |
| Livewire component | `Setup` (traits, properties, `$listeners`, `$queryString`) · `Rules` · `Lifecycle` (`mount`, `hydrate`, `updated*`) · `Queries` (computed properties) · `Actions` (public methods called from the view) · `Helpers` (protected) · `Render` |
| Livewire trait | `Setup` · `Actions` · `Helpers` |
| View composer / component class | `Setup` · `Compose` (or `Render`) · `Helpers` |
| Blades class | `Directives` · `Aliases` |
| Controller (6+ methods) | `Pages` · `Actions` · `Downloads` |
| Policy | `Admin` · `Portal` |
| Enum | `Cases` · `Labels` · `Helpers` |
| Route files | One divider per module, e.g. `// ===== Residents =====` |
| Blade page | `Header` · `Stats` · `Filters` · `Table` · `Form` · `Modals` · `Scripts` |
| CSS | `Tokens` · `Layout` · `Components` · `Utilities` · `Overrides` |
| JS | `Setup` · `Handlers` · `Init` |

### 4.3 Order inside a class

Traits, constants, properties, constructor, lifecycle, public methods, protected/private methods.

### 4.4 Size limits

| Thing | Limit | When exceeded |
|---|---|---|
| Method | 25 lines | Extract a helper or move to a Service |
| Action | 100 lines | Move reusable steps to a Service |
| Controller | 150 lines | Split by responsibility |
| Livewire component | 200 lines | Split into a child component, a trait, or move logic to an Action/Service |
| Query | 200 lines | Split by screen or purpose |
| Service | 300 lines | Split by capability |
| Composer | 40 lines | Move work to a Service |
| Blade view | 200 lines | Extract a partial or component |
| CSS file | 400 lines | Split by concern |

### 4.5 Style

- 4-space indent, LF line endings, final newline (`.editorconfig`). Formatter: **Laravel Pint**, preset `laravel` (`pint.json`).
- Import every class at the top. No inline fully-qualified names. No unused imports.
- Type every parameter and return (`: void`, `: bool`, `: Collection`).
- Early return instead of nesting. No `else` after `return`.
- No magic values: statuses are enums, repeated numbers are constants or settings, text is lang keys.
- No `dd()`, `dump()`, `var_dump()`, `Log::debug()` left behind.

---

## 5. Models, Enums and Database

### 5.1 Models

Relationships, casts, scopes, accessors, and small domain predicates only. No validation, no multi-step business logic, no HTTP.

```php
namespace App\Models\Collections;

class Bill extends Model
{
    // ===== Setup =====

    protected $fillable = ['reference', 'user_id', 'type', 'bill_date', 'due_date', 'total', 'status'];

    protected $casts = [
        'status' => BillStatus::class,
        'type' => BillType::class,
        'bill_date' => 'date',
        'due_date' => 'date',
        'total' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // ===== Relationships =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    // ===== Scopes =====

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', BillStatus::unpaidValues());
    }

    // ===== Helpers =====

    public function isOverdue(): bool
    {
        return $this->status->isUnpaid() && $this->due_date->isPast();
    }
}
```

- `$fillable` always. Never `$guarded = []`.
- Every relationship has a return type.
- A query used twice becomes a scope. A list-page `search` is always a scope (`scopeSearch`).
- A static `payloadFrom(array $data): array` under `// ===== Helpers =====` maps validated input to column values when two or more Actions share the mapping (section 7.1, rule 8).
- Two account models only: `Admin` (guard `web`) and `User` (guard `homeowner`). Domain models belong to a `User`, `Resident`, or `Property`, not to an account type.
- Group folders and cross-group rules: section 3.5.

### 5.2 Enums

Every fixed, business-defined value set is a native backed enum in `app/Enums/`. A status or type typed as a bare string anywhere else is a defect.

```php
enum BillStatus: string
{
    use CastsAsEnum;

    // ===== Cases =====

    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Void = 'void';

    // ===== Labels =====

    public function label(): string
    {
        return __('enums.bill_status.'.$this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Partial => 'warning',
            self::Void => 'secondary',
            default => 'danger',
        };
    }

    // ===== Helpers =====

    public function isUnpaid(): bool
    {
        return in_array($this, [self::Unpaid, self::Partial], true);
    }
}
```

The shared trait gives every enum the same three helpers and the Laravel 7 cast hook:

```php
trait CastsAsEnum
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(fn (self $case) => ['value' => $case->value, 'label' => $case->label()], self::cases());
    }

    public static function castUsing(array $arguments = []): CastsAttributes
    {
        return new EnumCast(static::class);
    }
}
```

Enums that are cast on a model also `implements Castable`. Then `$casts = ['status' => BillStatus::class]` just works.

- Validation: `Rule::in(BillStatus::values())`.
- Selects: `@foreach (BillStatus::options() as $option)`. Never duplicate a list in a view or JS.
- Labels come from `resources/lang/en/enums.php`.
- `Role` is the exception: roles are admin-managed rows in the `roles` table. `DefaultRoleSlug` carries constants for the roles THHA ships.

### 5.3 Migrations

- One table per migration. `down()` drops exactly what `up()` created.
- **Never edit a migration that has shipped.** Add a new one.
- Laravel 7 style: `$table->id()`, `$table->foreignId('x')->constrained()->cascadeOnDelete()`.
- Every foreign key is a real constraint with an explicit `cascadeOnDelete()`, `restrictOnDelete()`, or `nullOnDelete()`. Audit columns: `foreignId('issued_by_admin_id')->nullable()->constrained('admins')->nullOnDelete()`.
- Enum-backed columns are `string(20)` (or the longest value) with a default that is an enum value. A set that is fixed in `app/Enums` and will not grow uses MySQL `enum()` with the enum's values — `residents.sex`, `residents.civil_status` and `residents.educational_attainment` do, and that is the preferred shape for a resident's profile columns. A set the office may extend stays `string`, because adding a value to an `enum()` column needs a migration.
- Money is `decimal(12,2)`. Never `float`.
- **Index every column used to filter, sort, or search a list.** Add a composite index for the common filter + sort pair (`status, due_date`).
- Unique constraints encode business rules (`bills`: `user_id, type, billing_period`).

### 5.4 Seeders and factories (Laravel 7)

```php
$factory->define(Resident::class, function (Faker $faker) {
    return ['surname' => $faker->lastName, 'first_name' => $faker->firstName];
});
```

Seeders live in `database/seeds` with no namespace and extend `Seeder`. Fixed accounts use `updateOrCreate`. Demo data lives in `DemoSeeder`, separate from the reference data every install needs.

---

## 6. Requests, Controllers and Routes

### 6.1 Controllers

Controllers exist for what Livewire cannot do: login/logout, file and PDF downloads, previews, upload streaming, webhooks. They validate, authorize, call an Action (write), a Query (data for a preview or export), or a Service (a capability such as rendering a PDF), respond.

```php
class ReceiptController extends Controller
{
    public function show(Payment $payment, ReceiptQuery $query, ReceiptService $receipts)
    {
        $this->authorize('view', $payment);

        return $receipts->preview($query->build($payment));
    }

    public function download(Payment $payment, ReceiptQuery $query, ReceiptService $receipts)
    {
        $this->authorize('view', $payment);

        return $receipts->download($query->build($payment));
    }
}
```

- Resource method names: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, plus `download`.
- Redirect after a write. Flash messages come from lang files.
- A page that shows data is a Livewire component, not a controller.

### 6.2 Form Requests

One per write operation, in `Http/Requests/{Side}/{Module}/`. All validation lives here or in a Livewire `rules()` that reuses it.

```php
class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'mobile_no' => ['required', 'regex:/^09\d{9}$/'],
            'sex' => ['required', Rule::in(Sex::values())],
        ];
    }

    public function attributes(): array
    {
        return __('residents.attributes');
    }
}
```

Use `$request->validated()`, never `$request->all()`. A rule used twice moves to `app/Rules/`. Messages and attribute names come from lang files.

### 6.3 Routes

```
routes/web.php     public site      names: public.*
routes/admin.php   prefix /admin    names: admin.*
routes/portal.php  prefix /portal   names: portal.*
```

`RouteServiceProvider` sets `$namespace = null` and maps each file with its prefix, name prefix, and middleware. Inside a route file never repeat the prefix or name prefix.

```php
use App\Http\Livewire\Admin\Homeowners\ResidentForm;
use App\Http\Livewire\Admin\Homeowners\ResidentIndex;

// ===== Residents =====

Route::get('residents', ResidentIndex::class)->name('residents.index');
Route::get('residents/create', ResidentForm::class)->name('residents.create');
Route::get('residents/{resident}/edit', ResidentForm::class)->name('residents.edit');
```

- One style: `[Controller::class, 'method']` or `Component::class`, always imported. Never `'Controller@method'` strings.
- Group by middleware: `auth` (guard `web`) for admin, `auth:homeowner` for portal. The system has
  **one** sign-in route, `/login` in `routes/web.php`, and it carries no `guest` middleware: the
  controller sends an account already signed in on either guard to its own panel, so an officer can
  sign in from a browser that still holds a portal session. Never add `/admin/login` or
  `/portal/login`. Only the registration screens are behind `guest:*` (`guest:web` +
  `guest:homeowner` on `/portal/register`, `guest` on `/admin/register`), because a signed-in
  account has no registration to file.
- Route-level `can:` middleware is a convenience. **The component or controller still authorizes** (section 8.10).

---

## 7. Actions, Queries, Services and Utility

Four kinds of classes. Each has one job and one direction of dependency.

```
Component / Controller / Command / Job
      │ writes                              │ reads for a view
      ▼                                     ▼
   Action ──► Service ──► Utility         Query ──► Utility
      │          │                           │
      └──────────┴───────────────────────────┴──► Model / Enum
```

| Kind | One-line job |
|---|---|
| **Action** | Runs one operation: every create, update, delete, void, record, approve, issue |
| **Query** | Gives a view its data: lists, details, stats, select options, reports, document data |
| **Service** | A standalone capability other code calls as a black box: activity log, journal posting, PSGC lookup, PDF render, numbering, notification dispatch |
| **Utility** | A generic technical tool with no THHA meaning: cache, storage, money, period, chart, FIFO |

| From | May use | Must never use |
|---|---|---|
| Action | Services, Utility, Models, Enums, Notifications, domain exceptions | Another Action, a Query, `Request`, `auth()`, `redirect()` |
| Query | Models, scopes, Utility | Actions, Services, any write |
| Service | Other Services (a different capability, no cycles), Utility, Models | Actions, Queries |
| Utility | PHP, framework facades (`Cache`, `Storage`), other Utility | `App\Models`, `App\Enums`, Services, DB-backed settings |

### 7.1 Actions (`app/Actions/{Module}/`)

An Action is **one user-visible write**: "record a payment", "issue a violation", "accept a household link". It owns the use case from validated input to the last side effect.

```php
class CreateAdvanceDepositPromo
{
    public function __construct(private ActivityLogService $activity) {}

    // ===== Handle =====

    public function handle(array $data, Admin $by): AdvanceDepositPromo
    {
        $promo = AdvanceDepositPromo::create(AdvanceDepositPromo::payloadFrom($data));

        $this->activity->log(
            $by,
            'created',
            'advance_deposit_promo',
            "Added the \"{$promo->name}\" advance deposit promo ({$promo->discount_display})",
            ['advance_deposit_promo_id' => $promo->id]
        );

        return $promo;
    }
}
```

One write, so no transaction. `UpdateAdvanceDepositPromo` uses the same `payloadFrom()`, which lives in the model's `Helpers` section:

```php
// ===== Helpers =====

public static function payloadFrom(array $data): array
{
    return [
        'name' => $data['name'],
        'description' => ($data['description'] ?? null) ?: null,
        'discount_type' => $data['discount_type'],
        'discount_value' => $data['discount_value'] ?? 0,
        'months_required' => $data['months_required'] ?? 1,
        'is_active' => (bool) ($data['is_active'] ?? false),
    ];
}
```

A multi-write Action with a shared capability, pure math in a Utility, and side effects after commit:

```php
class ApplyAdvanceDepositsToDues
{
    public function __construct(
        private BillSettlementService $settlement,
        private ActivityLogService $activity,
    ) {}

    // ===== Handle =====

    public function handle(string $month, Admin $by): int
    {
        $drawn = DB::transaction(function () use ($month, $by) {
            $dues = Bill::query()->monthlyDues()
                ->where('billing_period', $month)
                ->where('balance', '>', 0)
                ->orderBy('id')
                ->get();

            return $dues->sum(fn (Bill $due) => $this->drawFor($due, $by));
        });

        if ($drawn > 0) {
            $this->activity->log($by, 'applied', 'monthly_due', 'Applied advance deposits to '.$drawn.' payment(s)', ['month' => $month]);
        }

        return $drawn;
    }

    // ===== Helpers =====

    private function drawFor(Bill $due, Admin $by): int
    {
        if (Payment::query()->waitingForBill($due->id)->exists()) {
            return 0;
        }

        $deposits = AdvanceDeposit::query()->oldestOpenFor($due->user_id)->get();
        $plan = FifoHelper::allocate(
            (string) $due->balance,
            $deposits->map(fn ($deposit) => ['id' => $deposit->id, 'available' => (string) $deposit->balance])->all()
        );

        foreach ($plan as $slice) {
            $deposit = $deposits->firstWhere('id', $slice['id']);

            $this->settlement->settle($due, [
                'amount' => $slice['amount'],
                'advance_deposit_id' => $deposit->id,
                'reference_no' => 'AD-'.$deposit->id,
                'notes' => 'Applied from advance deposit #'.$deposit->id,
                'received_by_admin_id' => $by->id,
            ]);

            $deposit->applyCredit($slice['amount']);
        }

        return count($plan);
    }
}
```

Illustrative: use the scope names that exist in the model. The point is the split: the loop and the writes stay in the Action; the allocation math is a Utility.

Rules:

1. **One Action = one use case = one `handle()`.** Name is `{Verb}{Noun}`: `CreateResident`, `UpdateResident`, `VoidBill`, `AssignPropertyOwner`. Create and update are separate Actions unless 90% of the code is shared.
2. **Transaction only when it protects something.** Open `DB::transaction()` when two or more writes (or a write plus a file promotion) must succeed or fail together. A single `create()` or `update()` has no transaction. A Livewire method or controller that opens a transaction is a defect.
3. **Inputs are explicit.** Models, scalars, a validated array (`$request->validated()` or `$this->validate()`), and the actor (`Admin $by` or `?User $by`). Never a `Request`, a Livewire component, the session, or `auth()`.
4. **Output is data.** Return the model or a scalar. Never `redirect()`, `session()->flash()`, a toast, or a view. The caller decides the UI result.
5. **Failure is a domain exception** (`App\Exceptions\ClosedPeriodException`, `OverpaymentException`). The caller catches it and shows a lang message.
6. **Side effects after commit.** Audit log, notifications, jobs, and cache flushes run after the transaction returns (Laravel 7 has no `afterCommit`). Inside the closure only writes happen. An Action that sends mail inside the closure is a defect.
7. **Audit.** The Action decides a change is logged and calls `ActivityLogService::log()`; the Service performs the write to `admin_activities`. Portal-initiated Actions do not write `admin_activities`.
8. **Payload mapping lives in the Action.** Turning validated input into column values (`?: null`, boolean casts, defaults) is a private method under `// ===== Helpers =====`. When Create and Update share it, it becomes a static `Model::payloadFrom(array $data): array`. Never a Service.
9. **Reads inside an Action use model scopes** on the records the Action will change. Actions do not call Queries; a Query is display data (section 7.2).
10. **Composition.** An Action never calls another Action: the inner one would send notifications inside the outer transaction. A step two or more Actions need is a Service method **only if it is a real capability** (section 7.3). A 1 to 3 line step is duplicated or becomes a model method. Pure calculation goes to a Utility.
11. **Every write from a component or controller is an Action**, even a one-line create. That keeps audit, authorization, and transaction rules uniform and Livewire methods at five to eight lines.
12. **Callers:** Livewire components, controllers, console commands, jobs. Observers and Services never call Actions.
13. **Testing:** one feature test per Action for the happy path, each domain exception, the activity-log row, and the notification.

### 7.2 Queries (`app/Queries/{Module}/`)

A Query class is the **read side**: whatever a screen shows comes from one. If a method's job is "return the rows, numbers, options, or document data for this view", it is a Query, not a Service and not a component method.

> `HomeownersService::paginatedList()` is a Query. It becomes `Queries/Homeowners/ResidentListQuery::paginate()`. Any `list`, `search`, `find…ForView`, `stats`, `options`, or `report` method inside a Service moves out the same way.

```php
class ResidentListQuery
{
    // ===== Queries =====

    public function paginate(array $criteria): LengthAwarePaginator
    {
        return Resident::query()
            ->select(['id', 'surname', 'first_name', 'mobile_no', 'created_at'])
            ->with('household.property:id,block,lot')
            ->search($criteria['search'] ?? '')
            ->filter($criteria['filters'] ?? [])
            ->orderBy($criteria['sort'], $criteria['direction'])
            ->paginate($criteria['perPage']);
    }
}
```

| Kind | Class | Method | Returns |
|---|---|---|---|
| List | `{Model}ListQuery` | `paginate(array $criteria)` | Paginator |
| Detail | `{Model}DetailQuery` | `find(int $id)` | Model with eager loads |
| Stats | `{Model}StatsQuery` | `summary()` | Plain array of cards, cached |
| Options | `{Thing}OptionsQuery` | `all()` | Array for selects, cached |
| Report | `{Report}Query` | `run(array $criteria)` | Rows and totals (arrays) |
| Document data | `{Document}Query` | `build(Model $record)` | Plain array for a PDF or preview |

Rules:

- **Read-only.** Never insert, update, or delete; no transaction; no notification; no audit log.
- **Input is explicit.** A `$criteria` array (`search`, `filters`, `sort`, `direction`, `perPage`), ids, models, or the owner for the portal. Never a `Request`, a component, the session, or `auth()`.
- **Authorization stays outside.** The component or controller authorizes with a Policy. A portal Query always takes the owner and filters by it; that is a scope, never a substitute for the Policy.
- **Output is data**, never HTML, never a view. Display formatting belongs to accessors, enum `label()`, and `<x-ui.*>` components. There is no presenter layer.
- **Efficient by construction:** selected columns, eager loads, `withCount`, one grouped query per stat set, always paginated, indexed filters (section 9.4). Stats and options cache themselves through `CacheHelper`.
- **Built from model scopes.** A scope is a reusable fragment (`search`, `unpaid`); a Query is the whole dataset of one screen. This is not a repository: no generic `find/create/update/delete` wrappers, and no Query that only forwards to Eloquent.
- One Query per screen dataset (`ResidentListQuery`, not a `ResidentQueries` with fifteen methods). Admin and portal share a Query when the data is the same; when the shape differs, write a separate one (`BillListQuery`, `OwnBillListQuery`).
- **Callers:** Livewire computed properties, controllers (previews, exports), other Queries. Actions and Services read with models and scopes directly.
- A lookup that **enforces a rule** (does this property already have an owner?) is a model scope used by the Action, not a Query.
- `DB::table()` and raw SQL are allowed only here (reports), always with bindings, never string concatenation.
- Over 200 lines: split by screen or purpose.
- Test: one test per Query for the result shape, each filter, and the query count.

### 7.3 Services (`app/Services/{Area}/`)

A Service is a **standalone capability**: a piece of the system that has its own public API and that any Action (or controller, for rendering) calls as a black box. The folder `{Area}` is a loose grouping (`Api`, `Access`, `Accounting`, `Collections`, `Documents`, `System`). The class is named `{Capability}Service`.

Real Services:

| Service | Capability |
|---|---|
| `PsgcService` | Region, province, city, barangay lookup from the PSGC API, cached |
| `ActivityLogService` | Writes `admin_activities` rows |
| `PasswordSetupService` | Issues a signed, expiring set-password link and mails it |
| `BillSettlementService` | Applies money to a bill: creates the payment row and updates `paid_amount`, `balance`, `status` |
| `NotificationService` | Dispatches a notification over the right channels |
| `PdfService`, `CsvService` | Renders a document or export |
| `PermissionService` | The permission catalogue and role checks |
| `DatabaseService` | Backup and restore |

```php
class BillSettlementService
{
    // ===== Operations =====

    public function settle(Bill $bill, array $data): Payment
    {
        $payment = Payment::create([
            'bill_id' => $bill->id,
            'advance_deposit_id' => $data['advance_deposit_id'] ?? null,
            'user_id' => $bill->user_id,
            'amount' => $data['amount'],
            'paid_at' => ($data['paid_at'] ?? null) ?: now(),
            'status' => $data['status'] ?? PaymentStatus::Recorded,
        ]);

        $this->applyToBill($bill, $payment);

        return $payment;
    }

    // ===== Helpers =====

    private function applyToBill(Bill $bill, Payment $payment): void
    {
        $bill->paid_amount = MoneyHelper::add($bill->paid_amount, $payment->amount);
        $bill->balance = MoneyHelper::sub($bill->total, $bill->paid_amount);
        $bill->status = $bill->balance === '0.00' ? BillStatus::Paid : BillStatus::Partial;
        $bill->save();
    }
}
```

Rules:

- Takes models and plain arrays. **Never** a `Request`, a Livewire component, the session, or `auth()`.
- **Contains no Action logic.** No public `createX()`, `updateX()`, `deleteX()`, `voidX()`, `recordX()` that run a whole use case; no sequencing of steps for one use case; no deciding *when* to audit or notify; no `redirect()`, flash, or toast.
- **Contains no view data.** No list, detail, stats, options, or report method (those are Queries).
- **Contains no payload mappers.** `payload()`, `*Payload()`, `normalize()`, `map()` belong to the Action or `Model::payloadFrom()`.
- A Service that writes may open its own transaction; nested transactions are safe and the Action's outer one wins.
- A Service may call a Service of a different capability. No cycles. A Service never calls an Action or a Query.
- Throws a domain exception on a rule violation.
- One Service per capability, not per table. Over 300 lines: split by capability.
- Create one only when the capability is reused or exceeds what a method can hold. A single `create()` is an Action, not a Service.
- A Service may know THHA models, enums, and DB-backed settings. That is what separates it from a Utility.

### 7.4 Utility (`app/Utility/`)

Stateless static classes that **work on their arguments only** and have no THHA meaning: `CacheHelper`, `ChartHelper`, `MoneyHelper`, `PeriodHelper`, `StorageHelper`, `TimeHelper`, `FifoHelper`. Check them before writing a facade call or a new helper.

- **No `App\Models`, no `App\Enums`, no DB-backed settings, no Services.** A class that needs one of those is a Service.
- Static methods, no state, no constructor.
- May call framework facades (`Cache`, `Storage`) and other Utility classes.
- Used by Actions, Queries, Services, Livewire, and Blade directives.

`FifoHelper::allocate(string $needed, array $buckets): array` is the model Utility. Input: a money string and a list of `['id' => int, 'available' => string]` ordered oldest first. Output: a list of `['id' => int, 'amount' => string]` whose sum is the smaller of `$needed` and the total available. No models, no database; bcmath through `MoneyHelper`.

Classes in `app/Utility/` today that import models or enums, or read DB-backed settings (`SettingsHelper`, `PermissionHelper`, `MenuHelper`, `DatabaseBackupHelper`, `CsvHelper`, `PdfBuilder`), are in the wrong layer. They move to Services **in an explicit layer-cleanup task only**. Do not rename them yourself; until then, the old names in this file (sections 7.6, 9.4, 10.2, 10.4, 14) still apply.

### 7.5 Choosing the layer

Decision tree, in order:

1. Does a person ask for something to happen (create, update, delete, record)? → **Action**.
2. Does a screen, PDF, or export need data? → **Query**.
3. Is it a capability with its own API that several unrelated Actions need as a black box? → **Service**.
4. Does it work on arguments only and know no model or enum? → **Utility**.
5. Is it a reusable fragment of a query → **model scope**; a fact about one record → **model method**.
6. Three lines used once? Keep it in the caller. Still unsure? **Action**. Never default to a Service.

### 7.6 Settings and config

- `env()` is read only inside `config/*.php`.
- DB-backed settings are read through `SettingsHelper`, which caches and exposes `flush()`.
- Service Providers bootstrap only: bindings, view composers, observers, event wiring, Blade extensions. No queries, no branding logic, no bare `catch (\Exception $e) {}`.

### 7.7 Defect shapes (before → after)

| Found | Why it is wrong | Becomes |
|---|---|---|
| `AdvanceDepositPromoService::payload()` | Service holds only a payload mapper; named for a model | `AdvanceDepositPromo::payloadFrom()`; delete the Service |
| `ProductService::productPayload()` / `categoryPayload()` | Same | `Product::payloadFrom()`, `ProductCategory::payloadFrom()` |
| `ProductService::syncIncomeAccount()` | Use-case glue around another Service | Inline in `CreateProductCategory` / `UpdateProductCategory`, calling `Account::syncCategory()`. If the glue is 5+ lines in both, a model method `ProductCategory::attachAccount(Account $account)` |
| `PaymentService::post()` | Named for a table; Actions need it as a shared step | `BillSettlementService::settle()` |
| `DB::transaction` around one `create()` | Protects nothing; audit sits outside anyway | Remove the transaction |
| Draw loop with `min()` and `round()` inside an Action | Pure math mixed with writes | `FifoHelper::allocate()` plans, the Action writes |
| `HomeownersService::paginatedList()` | View data in a Service | `ResidentListQuery::paginate()` |
| A Query that calls `->update()` | A Query writing | Move the write to an Action |
| An Action constructor takes another Action | Nested side effects inside the outer transaction | Shared step becomes a Service capability, or a model method |
| `Utility` class importing `App\Models\*` | Utility knows the domain | Move to Services in an explicit layer-cleanup task |

### 7.8 Self-check

Run before finishing any change. Every command must print nothing.

```bash
grep -rnE 'function \w*[pP]ayload\(' app/Services
grep -rlE 'App\\(Models|Enums)' app/Utility
grep -rlE '::create\(|->(update|delete|save|insert)\(' app/Queries
grep -rlE 'use App\\(Actions|Queries)' app/Services app/Queries app/Utility
grep -rlE 'use App\\Actions' app/Actions
for f in app/Services/*/*Service.php; do ls app/Models/*/"$(basename "$f" Service.php)".php 2>/dev/null; done
```

A hit is a defect to fix, or a line in your report with the reason. Also check by eye: no Action constructor takes another Action, and no Action wraps a single write in `DB::transaction`.

---

## 8. Livewire Architecture (v2)

### 8.1 When a component exists

Livewire is for **server truth**: the interaction needs the database, a Policy, or server-computed data. Opening a modal, toggling a section, highlighting a checkbox, selecting all rows, or switching a tab is **not** Livewire. Use Bootstrap 4 / jQuery.

### 8.2 Base classes

Every component extends one of three bases (or is a `Shared/` component, section 8.9). They fix the layout, the guard, and the helpers once.

```php
abstract class PortalComponent extends Component
{
    use AuthorizesRequests;

    protected const LAYOUT = 'layouts.portal';

    // ===== Lifecycle =====

    public function hydrate(): void
    {
        Auth::shouldUse('homeowner');
    }

    // ===== Helpers =====

    protected function page(string $view, array $data = [], ?string $title = null)
    {
        return view($view, $data)
            ->extends(static::LAYOUT, ['pageTitle' => $title])
            ->section('content');
    }

    protected function notify(string $type, string $message): void
    {
        $this->dispatchBrowserEvent('thha:toast', ['type' => $type, 'message' => $message]);
    }

    protected function prefixRules(string $prefix, array $rules): array
    {
        $prefixed = [];

        foreach ($rules as $field => $rule) {
            $prefixed[$prefix.'.'.$field] = $rule;
        }

        return $prefixed;
    }
}
```

`AdminComponent` is identical with `LAYOUT = 'layouts.admin'` and `Auth::shouldUse('web')`. A child that overrides `hydrate()` must call `parent::hydrate()`. Child components rendered **inside** a page (`Stats`, `Tab`, `Modal`) extend the same side base, not `Component`.

**Why `hydrate()` forces the guard:** a Livewire update request goes to `/livewire/message/...`, not to the page URL. It does **not** re-run the page's route middleware, so `auth:homeowner` has not set the default guard. Without this, `auth()->user()`, `authorize()`, and `@can` silently resolve against the admin guard.

### 8.3 Component taxonomy

A feature slice is a **set of small components**, not one big one. Each has one job.

| Kind | Name | Job | Loads |
|---|---|---|---|
| Index | `{Model}Index` | Table, search, filters, sort, pagination, row actions, bulk actions | On page load |
| Stats | `{Model}Stats` | The KPI strip above the table | Lazy (`wire:init`), cached |
| Form | `{Model}Form` | Create and edit (nullable id), one `$form` array | On page load |
| Show | `{Model}Show` | Header and summary of one record, hosts the tabs | On page load |
| Tab | `{Model}{Tab}Tab` | One tab's table or panel | **Lazy**, on first view of the tab |
| Modal | `{Model}{Purpose}Modal` | A form or panel over 60 lines inside a modal | Lazy, on open |
| Shared | `UploadAvatar`, `UploadFile`, `ResidentPicker`, ... | Reusable widget used by several slices or sides | By parent |
| Widget | `NotificationBell`, `MenuBadges` | Small live element in a layout | Poll or event |

A page's Livewire component is a **page shell**: it owns the route, authorizes the page, and embeds the parts with `@livewire('admin.homeowners.resident-stats', [], key('resident-stats'))`. Parts talk to each other through events (section 8.12), never through shared state.

### 8.4 A complete CRUD slice

Every module screen (residents, bills, violations, announcements, ...) is built from the same set. Build order extends the standard one:

migration → enum → model (+factory/seeder) → policy → form requests → **Queries (list, detail, stats)** → **Services (only if a new capability is needed)** → **Actions** → **Livewire: Index, Stats, Form, Show (+ Tabs)** → views → route → lang → test

| Piece | File |
|---|---|
| List page | `Http/Livewire/Admin/{Module}/{Model}Index.php` + `livewire/admin/{module}/{model}-index.blade.php` |
| Stats strip | `{Model}Stats.php` + `{model}-stats.blade.php` |
| Create / edit | `{Model}Form.php` + `{model}-form.blade.php` |
| Detail | `{Model}Show.php` + `{model}-show.blade.php`, `{Model}{Tab}Tab.php` per tab |
| Validation | `Store{Model}Request`, `Update{Model}Request` |
| Writes | `Create{Model}`, `Update{Model}`, `Delete{Model}` (plus domain verbs) in `Actions/{Module}/` |
| Reads | `{Model}ListQuery`, `{Model}DetailQuery`, `{Model}StatsQuery`, built on model scopes (`search`, `filter`) |
| Authorization | `{Model}Policy` (`viewAny`, `view`, `create`, `update`, `delete`) |
| Text | `resources/lang/en/{module}.php` |
| Routes | `{model}s.index`, `.create`, `.edit`, `.show` |
| Test | `tests/Feature/Admin/{Module}/{Model}Test.php` |

The portal slice reuses the same Actions and Services and has its own thinner components (`Portal/{Module}/...`) with `*Own` abilities.

### 8.5 Index component (table, search, filters)

```php
class ResidentIndex extends AdminComponent
{
    use WithBulkActions, WithFilters, WithListCriteria, WithPagination, WithPerPage, WithSearch, WithSorting;

    // ===== Setup =====

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['resident-saved' => '$refresh'];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => ''],
        'sortDirection' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    protected array $sortable = ['surname', 'created_at'];

    protected string $defaultSort = 'surname';

    // ===== Lifecycle =====

    public function mount(): void
    {
        $this->authorize('viewAny', Resident::class);
    }

    // ===== Queries =====

    public function getResidentsProperty()
    {
        return app(ResidentListQuery::class)->paginate($this->listCriteria());
    }

    // ===== Actions =====

    public function delete(int $residentId, DeleteResident $action): void
    {
        $resident = Resident::findOrFail($residentId);
        $this->authorize('delete', $resident);

        $action->handle($resident, auth()->user());

        $this->notify('success', __('residents.deleted'));
    }

    // ===== Render =====

    public function render()
    {
        return $this->page('livewire.admin.homeowners.resident-index', [], __('residents.title'));
    }
}
```

**Concerns (`Http/Livewire/Concerns/`)** are the shared table behavior. Every list uses them; nobody re-implements search or sort in a component.

| Trait | Provides |
|---|---|
| `WithSearch` | `$search`, resets the page when it changes |
| `WithSorting` | `$sortField`, `$sortDirection`, `sortBy()` checked against the component's `$sortable` whitelist, `sortColumn()`, `sortDir()` |
| `WithPerPage` | `$perPage` limited to `[10, 15, 25, 50]`, resets the page |
| `WithFilters` | `$filters` array, `resetFilters()`, resets the page on any change |
| `WithListCriteria` | `listCriteria()`: builds the `$criteria` array (`search`, `filters`, `sort`, `direction`, `perPage`) from the other traits and hands it to the list Query |
| `WithBulkActions` | Server half of mass actions: `bulkDelete(array $ids)` re-authorizes **each** record; the selection itself is client-side |
| `WithFormState` | `$form`, `fillForm()`, `resetForm()` for form components |

```php
trait WithSorting
{
    // ===== Setup =====

    public $sortField = '';

    public $sortDirection = 'asc';

    // ===== Actions =====

    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->sortable, true)) {
            return;
        }

        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    // ===== Helpers =====

    protected function sortColumn(): string
    {
        return in_array($this->sortField, $this->sortable, true) ? $this->sortField : $this->defaultSort;
    }

    protected function sortDir(): string
    {
        return $this->sortDirection === 'desc' ? 'desc' : 'asc';
    }
}
```

A trait cannot add to `$queryString`, so the component lists every URL-synced property itself.

**Table rules**

- Search: `wire:model.debounce.500ms="search"`. The `search` scope ignores input shorter than 2 characters and searches indexed columns; leading-wildcard `LIKE` only on small tables.
- Filters (status, period, type): selects use `wire:model.lazy` (one request on change), options from enums.
- Sort: only whitelisted columns; the direction is validated.
- Pagination: `paginate($this->perPage)`; `{{ $this->residents->links() }}`.
- **Row selection is client-only.** Checkboxes and "select all" are jQuery in `bulk.js`. The server sees only the final `ids` array when the user clicks a bulk action (`data-thha-bulk-action="bulkDelete"`), and re-authorizes every id.
- Row actions are `wire:click` with `wire:key` on the `<tr>`; destructive ones carry `data-thha-confirm`.
- The empty result shows `<x-table.empty>`, never a blank table. The first paint shows `<x-table.skeleton>` when the component is lazy.

### 8.6 Stats component

The KPI strip is its own component so the table never waits for it.

```php
class ResidentStats extends AdminComponent
{
    // ===== Setup =====

    public $ready = false;

    protected $listeners = ['resident-saved' => '$refresh'];

    // ===== Actions =====

    public function load(): void
    {
        $this->ready = true;
    }

    // ===== Render =====

    public function render(ResidentStatsQuery $stats)
    {
        return view('livewire.admin.homeowners.resident-stats', [
            'cards' => $this->ready ? $stats->summary() : [],
        ]);
    }
}
```

```blade
<div wire:init="load" class="row">
    @if (! $ready)
        <x-table.skeleton-cards :count="4" />
    @else
        @foreach ($cards as $card)
            <div class="col-6 col-lg-3" wire:key="stat-{{ $card['key'] }}">
                <x-adminlte.stat-card :label="$card['label']" :value="$card['value']" :icon="$card['icon']" :theme="$card['theme']" />
            </div>
        @endforeach
    @endif
</div>
```

- Numbers come from a `*StatsQuery` that runs **one grouped query** per card set and caches the result (section 9.4). The component never builds the query.
- Stats listen to the same `*-saved` events as the table so both refresh once.
- Money in cards uses `MoneyHelper` through `<x-ui.money>`.

### 8.7 Form component

```php
class ResidentForm extends AdminComponent
{
    use WithFormState;

    // ===== Setup =====

    public $residentId = null;

    // ===== Rules =====

    protected function rules(): array
    {
        return $this->prefixRules('form', (new StoreResidentRequest)->rules());
    }

    protected function validationAttributes(): array
    {
        return $this->prefixRules('form', __('residents.attributes'));
    }

    // ===== Lifecycle =====

    public function mount(?Resident $resident = null): void
    {
        $this->authorize($resident ? 'update' : 'create', $resident ?? Resident::class);

        $this->residentId = $resident?->id;
        $this->fillForm($resident?->only(['surname', 'first_name', 'mobile_no']) ?? []);
    }

    // ===== Actions =====

    public function save(CreateResident $create, UpdateResident $update)
    {
        $data = $this->validate()['form'];
        $resident = $this->residentId ? Resident::findOrFail($this->residentId) : null;
        $this->authorize($resident ? 'update' : 'create', $resident ?? Resident::class);

        $saved = $resident ? $update->handle($resident, $data, auth()->user()) : $create->handle($data, auth()->user());

        $this->emit('resident-saved');
        session()->flash('success', __('residents.saved'));

        return redirect()->route('admin.residents.show', $saved);
    }

    // ===== Render =====

    public function render()
    {
        return $this->page('livewire.admin.homeowners.resident-form', [], __('residents.form_title'));
    }
}
```

- **One `$form` array.** Never bind inputs to an Eloquent model (section 8.11).
- Inputs: `wire:model.defer="form.first_name"`. The whole form is sent in **one** request on submit (`wire:submit.prevent="save"`).
- Rules come from the FormRequest (section 6.2). Add HTML attributes (`required`, `maxlength`, `pattern`, `inputmode`) for instant browser feedback at zero server cost.
- Instant server checks (`wire:model.lazy` + `updated($field)` + `validateOnly()`) only for uniqueness-type fields: email, PhilSys number, `(block, lot)`.
- Select lists come from enums and cached lookups (section 9.4). Long lists (residents, properties) use a `Shared` picker (section 8.9) that searches on the server, never a `<select>` with thousands of options.
- A form over 200 lines splits into sections (partials) or sub-forms; a modal form is a Modal component.

### 8.8 Show component and tabs

`{Model}Show` loads the record header and renders a Bootstrap tab strip. **Tab panes are lazy** (section 9.2): the first pane renders at once; the other panes mount their Tab component when first shown, through `Thha.lazyTab` in `core.js`, which emits `tab-shown` to that pane's component once.

### 8.9 Shared components: uploads and pickers

`Http/Livewire/Shared/` holds components used on more than one side. They are **guard-agnostic**: they take everything through `mount()` parameters, never call `auth()`, never authorize a record, and communicate with the parent only by `emitUp`. The parent authorizes and persists.

| Component | Props | Emits | Notes |
|---|---|---|---|
| `UploadAvatar` | `field`, `current`, `disk`, `size` | `file-uploaded` (`field`, `path`) | Image only (`image\|mimes:jpg,jpeg,png\|max:2048`), square preview, remove button |
| `UploadImage` | `field`, `current`, `max` | `file-uploaded` | Banner and announcement images, public disk allowed |
| `UploadFile` | `field`, `accept`, `max`, `multiple` | `file-uploaded` | Documents (IDs, proofs, receipts) on the **private** disk |
| `ResidentPicker` | `name`, `selectedId` | `resident-picked` | Server-side search, at most 10 results, never preloads the table |
| `PropertyPicker` | `name`, `selectedId` | `property-picked` | Block and lot search |
| `ActivityFeed` | `subjectType`, `subjectId` | none | Lazy, paginated `admin_activities` |

```php
class UploadAvatar extends Component
{
    use WithFileUploads;

    // ===== Setup =====

    public $field = 'avatar';

    public $current = null;

    public $photo = null;

    // ===== Rules =====

    protected $rules = ['photo' => 'image|mimes:jpg,jpeg,png|max:2048'];

    // ===== Lifecycle =====

    public function updatedPhoto(): void
    {
        $this->validate();

        $path = StorageHelper::storeTemp($this->photo, 'avatars');

        $this->emitUp('file-uploaded', $this->field, $path);
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.shared.upload-avatar');
    }
}
```

Upload flow, always the same:

1. The child validates type and size, then stores the file in a **temp location** through `StorageHelper::storeTemp()` and emits the relative path. It never writes to the final location.
2. The parent keeps only the path string in `$form`, **re-validates it** with `StorageHelper::isTemp($path)` (the browser can send any string), and passes it to the Action.
3. The Action calls `StorageHelper::promote($path, $destination)` inside its transaction and deletes the replaced file after commit.
4. `PurgeTempUploads` (scheduled daily) deletes temp files older than 24 hours.
5. A form that has one uploader used nowhere else may use `WithFileUploads` directly instead of a Shared component. The moment a second form needs it, extract it.

Set `temporary_file_upload.middleware` in `config/livewire.php` to `throttle:60,1` plus `auth:web,homeowner`, and the `rules` to the largest accepted size. Documents are never in `public/`; the avatar of a resident is private and served by an authorized route.

### 8.10 Authorization

Livewire update requests bypass route middleware, so **every action re-authorizes**, even if the route already had `can:`.

- `mount()` authorizes the page (`viewAny`, `view`).
- Every public action that reads or writes a record loads it by ID **inside the action** and calls `$this->authorize()` on it. Never trust a record ID sent from the browser.
- Portal components check ownership through a Policy (`viewOwn`, `updateOwn`). Never `where('user_id', ...)` as a substitute for a Policy.
- Hiding a button is not authorization.
- Stats and Tab child components authorize in their own `mount()` too; a child is not covered by its parent's check.

### 8.11 State

- One root element per component.

Public properties are **visible to and writable by the browser**.

- Store scalars, arrays, and IDs. Never a Collection or paginator (use a computed property, `getXProperty()`).
- Never bind inputs to a public Eloquent model. A tampered request could change any attribute. Bind to `$form` (array) or individual properties, validate, then pass the validated array to an Action.
- Never put secrets, other users' data, or whole records the user may not see in a public property.
- Bound properties are untyped or nullable (`?string`): the browser can send `null`.
- A property that only the server reads is `protected`. A property the browser must not change (`$residentId` after `mount`) is re-checked inside the action, because Livewire 2 has no `#[Locked]`.

### 8.12 Events and browser integration

- Component-to-component: `$listeners` with kebab-case names (`resident-saved`). Use the narrowest emit: `emitSelf`, `emitUp`, `emitTo('component', ...)`. A global `emit()` wakes **every** component on the page, so use it only for events with several listeners.
- Component-to-browser: `dispatchBrowserEvent('thha:...')`. Handlers live in `public/js/`.
- Toasts: `$this->notify('success', __('...'))`. One handler (`toast.js`, Toastr plugin).
- Destructive actions: `wire:click="delete({{ $id }})" data-thha-confirm="{{ __('common.confirm_delete') }}"`. `confirm.js` intercepts the click, asks the browser to confirm, and stops the click when the answer is no. Livewire 2 has no `wire:confirm`.
- Modals: Bootstrap markup with `wire:ignore.self` on the modal root. Server actions open and close with `$this->dispatchBrowserEvent('thha:modal', ['id' => 'resident-modal', 'action' => 'show'])`. Opening a modal that needs no server data is plain `data-toggle="modal"`.
- jQuery plugins (Select2, datepicker): wrap in `wire:ignore`, initialize in `plugins.js` on `livewire:load`, and re-initialize in `Livewire.hook('message.processed', ...)`.
- Scripts inside a Livewire view do not re-run on updates. Put page scripts in `@push('js')` on a full page, or in `public/js/`.

---

## 9. Livewire Performance

The target: **a page paints fast, and every interaction costs at most one small request.** Apply these rules while building, not afterward.

### 9.1 Fewer server calls

| Interaction | Do this | Not this |
|---|---|---|
| Form typing | `wire:model.defer`, one request on submit | `wire:model` on every input |
| Search box | `wire:model.debounce.500ms` | Bare `wire:model` |
| Filter select | `wire:model.lazy` | Bare `wire:model` |
| Open modal, tab, accordion, dropdown | Bootstrap `data-toggle` | A Livewire round trip |
| Select rows, select all, expand row | jQuery in `bulk.js` | `wire:model` on a checkbox array |
| Show or hide a field from another field | jQuery / `data-*` hooks | `updated()` re-render |
| Instant validation | HTML attributes first; `validateOnly` only for uniqueness | `validateOnly` on every field |
| Several components react to one change | One `emit` that lists all, or `emitTo` | Chained emits |
| Refreshing data | `$refresh` on a *listener*, narrowest component | Polling |
| Live badge count | `wire:poll.60s` on **one** small Widget component | Polling on page components |
| Action that changes nothing visible | `$this->skipRender()` | Full re-render |

No polling below 30 seconds. Polling lives only in Widget components (`NotificationBell`, `MenuBadges`). Use `.visible` or `.keep-alive` modifiers only after confirming the installed Livewire 2.x supports them.

### 9.2 Lazy loading (Livewire 2 has no `lazy`)

Use the `wire:init` pattern. The page shell paints at once; the heavy part loads in a second small request.

```blade
<div wire:init="load">
    @if (! $ready)
        <x-table.skeleton :rows="5" />
    @else
        {{-- the real content --}}
    @endif
</div>
```

Lazy-load, always: **stats strips, charts, tab panes except the first, modal bodies, activity feeds, "documents" tables, anything below the fold that runs its own query.** Never lazy-load the primary table of a page, the form of a form page, or anything the user must see to act.

Tabs: the pane mounts its component only when first shown. `core.js` listens for Bootstrap's `shown.bs.tab` and calls `Livewire.emitTo(componentId, 'tab-shown')` once; the Tab component sets `$ready` in that listener. Modal bodies work the same with `shown.bs.modal`.

### 9.3 Smaller payloads

- Public properties hold **scalars and IDs**. Collections, paginators, and models are `getXProperty()` computed properties (they are never serialized into the page).
- `protected` for anything only the server needs.
- `->select([...])` the columns the view uses; `with('relation:id,col')`.
- Paginate at 15. The per-page select tops out at 50.
- Split big pages into components: a child re-renders **only itself**, so a table row action does not re-render the stats strip.
- `wire:ignore` for static heavy markup, third-party widgets, and chart canvases.
- Stable `wire:key` on loops. A missing or index-based key forces large DOM diffs.
- Do not echo a large array into the view only to hand it to JS; render JSON once in a `data-*` attribute outside Livewire, or lazy-load it.
- Thumbnails: store a resized copy for avatars and list images; the list never loads originals. `loading="lazy"` on every `<img>` outside the first screen.

### 9.4 Cheaper queries and caching

- Computed properties are memoized **per request**: reference `$this->residents` several times, the query runs once. Do not call the same query from `render()` and an action.
- Index every filter, sort, and search column (section 5.3).
- Aggregates: one grouped query, or `withCount`.
- The cache lives **inside the Query** (stats and options) or the Utility, never in the component.
- **Cache with `CacheHelper`** (wrapper over `Cache::remember`, no cache tags because the `file` and `database` drivers do not support them):

```php
CacheHelper::remember('collections.stats', $period, 300, fn () => $this->compute($period));
CacheHelper::flush('collections.stats');
```

`CacheHelper` stores a **version number** per group and builds keys from it; `flush(group)` bumps the version.

| Cache | TTL | Flushed by |
|---|---|---|
| Settings | forever | `SettingsHelper::flush()` on write |
| Lookup lists (accounts, products, payment methods, amenities, roles) | forever | The model's Observer on save/delete |
| Stats strips and dashboard cards | 300 s | The Observers of the models they count |
| Menu and layout badges (composers) | 60 s | TTL |
| Enum options | none needed (in code) | |

- **Never cache** anything scoped to a user in a shared key, anything authorization-dependent, or money balances shown in a transaction flow.
- Exports and reports use `chunk()` / `cursor()`; PDFs and mass emails go to a queue.

### 9.5 Server, assets, and the page

- Production: `config:cache`, `route:cache`, `view:cache`, `composer install --no-dev --optimize-autoloader`, OPcache on, queue worker for notifications, PDFs, SMS, and SOA emails.
- Load AdminLTE plugins **per page** (`@section('plugins.Select2', true)`); `config/adminlte.php` keeps them `active => false` unless every page needs them.
- `@livewireScripts` once, `@livewireStyles` once (section 10.1). Scripts at the end of the body.
- No DataTables on server-paginated lists (it duplicates what Livewire already does); DataTables only for small static report tables.
- Composers are cached and bound to layouts, not to every view (section 10.4).

### 9.6 Budgets and measurement

| Measure | Budget |
|---|---|
| Queries on a list page | 15 or fewer, no N+1 |
| Queries per Livewire update | 8 or fewer |
| Livewire update payload | A few KB; over 50 KB is a defect |
| First paint of a list page | Shell and table; stats arrive after |
| Rows per page | 15 (max 50) |

Every Index component has one feature test that asserts its query count stays inside the budget (section 17). Check payloads with the browser network tab while building.

---

## 10. Blade, the `app/View` Layer, and AdminLTE

### 10.1 Layouts

`layouts/admin.blade.php` and `layouts/portal.blade.php` extend `adminlte::page`. They set the title, push shared assets, and nothing else.

```blade
@extends('adminlte::page')

@section('title', $pageTitle ?? config('adminlte.title'))
```

- Livewire full pages render into `@section('content')`. Use `@push('css')` / `@push('js')` for page assets.
- The page heading is `<x-ui.page-header :title="__('residents.title')" />` inside the component view.
- Livewire needs its assets: set `'livewire' => true` in `config/adminlte.php` if the installed package version supports it; otherwise `@livewireStyles` / `@livewireScripts` go in the layouts. Never both.
- `public.blade.php` is the pre-login public site layout.

### 10.2 Menu

The package menu is built at runtime by one listener, `App\Listeners\BuildAdminLteMenu`, registered for `JeroenNoten\LaravelAdminLte\Events\BuildingMenu`. It reads `config/menu/admin.php` or `config/menu/portal.php` through `MenuHelper`, chosen by `request()->is('portal*')`. Item text is a lang key (`'text' => 'menu.residents'`). `config('adminlte.menu')` stays empty. Live badges in the menu are the `MenuBadges` Widget component, not menu config.

### 10.3 Vendor files: the hard rules

AdminLTE ships two generated trees. Treat both as vendor code.

| Path | Rule |
|---|---|
| `public/vendor/**` (`adminlte`, `bootstrap`, `jquery`, `fontawesome-free`, `overlayScrollbars`, plugin folders) | **Never edit, format, rename, or delete by hand.** Regenerate with the package commands (`adminlte:install`, `adminlte:plugins`). Check with `adminlte:status` |
| `resources/views/vendor/adminlte/**` | Published copies of package views. **Keep a file only if it differs from the package.** Identical copies are deleted so upgrades work |
| `resources/views/vendor/livewire/**` | Same rule |

Customization order of preference:

1. `config/adminlte.php` (title, logo, layout classes, sidebar, user menu, plugins).
2. Sections and stacks (`content_header`, `css`, `js`, `@push`).
3. A wrapper in `app/View/Components/Adminlte/` (section 10.5).
4. Our own CSS in `public/css/`.
5. Editing one published vendor view. Name it in your report with the reason.

Find real customizations with `diff -rq vendor/jeroennoten/laravel-adminlte/resources/views resources/views/vendor/adminlte`.

App code (queries, business markup, per-module pages) **never** lives in `resources/views/vendor/**`.

### 10.4 The `app/View` layer

```
app/View/
  Blades/AdminLteBlades.php
  Components/{Adminlte,Ui,Form,Table}/*.php
  Composers/*Composer.php
app/Providers/ViewServiceProvider.php
```

`ViewServiceProvider` is a bootstrapping-only provider (registered in `config/app.php`). It calls the Blades classes and binds the composers.

```php
class ViewServiceProvider extends ServiceProvider
{
    // ===== Setup =====

    protected array $composers = [
        'layouts.admin' => [BrandingComposer::class, PeriodComposer::class],
        'layouts.portal' => [BrandingComposer::class],
        'layouts.public' => [BrandingComposer::class],
        'pdf.layouts.*' => [BrandingComposer::class],
    ];

    // ===== Lifecycle =====

    public function boot(): void
    {
        AdminLteBlades::register();

        foreach ($this->composers as $views => $composers) {
            View::composer($views, $composers);
        }
    }
}
```

**Blades (`app/View/Blades/`)**: classes that extend Blade for the theme. `AdminLteBlades` is the **only** place that calls `Blade::directive()`, `Blade::aliasComponent()`, or `Blade::include()`. A directive only calls a Utility; it has no logic.

```php
class AdminLteBlades
{
    // ===== Directives =====

    public static function register(): void
    {
        Blade::directive('money', fn (string $expression) => "<?php echo \\App\\Utility\\MoneyHelper::format({$expression}); ?>");
        Blade::directive('period', fn (string $expression) => "<?php echo \\App\\Utility\\PeriodHelper::label({$expression}); ?>");
        Blade::directive('date', fn (string $expression) => "<?php echo \\App\\Utility\\TimeHelper::date({$expression}); ?>");
    }
}
```

Another theme or document type gets its own class (`PdfBlades`), registered the same way. Directives are for output formatting; **never** a role or permission check (`@can` is the check).

**Composers (`app/View/Composers/`)**: share data that **every page of a layout** needs.

Composer rules:
- Bind to **layouts and partials**, never `'*'`, never to a Livewire component view (a component re-renders on every update and would re-run the composer).
- Read through `SettingsHelper`, `CacheHelper`, or a Service. **No bare queries**, no business logic. Over 40 lines moves to a Service.
- Data that depends on the component's state is a computed property, not a composer.
- A composer runs on **full page loads only**. Anything that must update live (badge counts) is a Widget component.
- Typical composers: `BrandingComposer` (name, logo, contact), `PeriodComposer` (current accounting period, admin), `HoaContactComposer` (public and PDF letterhead), `MenuCountsComposer` (cached counts for the first paint of badges).

### 10.5 Components: ours and the package's

Four groups of **class** components in `app/View/Components/`, each with its view in `resources/views/components/{group}/`. Create a class component only when it needs logic or default resolution; otherwise an anonymous Blade component is enough.

| Group | Tag | Holds |
|---|---|---|
| `Adminlte` | `<x-adminlte.*>` | **Wrappers around package widgets** with THHA defaults: `stat-card` (info/small box), `page-card` (card with toolbar and footer slots), `form-modal` (modal with title, body, save/cancel), `profile-card` |
| `Ui` | `<x-ui.*>` | Generic: `status-badge`, `page-header`, `money`, `empty-state`, `avatar`, `confirm-button` |
| `Form` | `<x-form.*>` | THHA form groups where the package input does not fit: `resident-select`, `money-input`, `period-input`, `upload-slot` |
| `Table` | `<x-table.*>` | `table`, `th` (sortable), `search`, `per-page`, `empty`, `skeleton`, `skeleton-cards`, `bulk-bar` |

- **Wrap a package widget only when we would otherwise set the same defaults three or more times.** Otherwise use `<x-adminlte-*>` directly.
- Package components use the **dash** form (`<x-adminlte-input>`); ours use the **dot** form (`<x-adminlte.stat-card>`). They never share a name.
- Wrappers pass `$attributes` through, so `wire:*` and `class` still work.
- Confirm the installed package version provides the `x-adminlte-*` components (introduced in 3.7); `composer show jeroennoten/laravel-adminlte`.
- Repeated markup (three or more uses) becomes a component or partial. Repeated option lists come from enums.

### 10.6 UI/UX consistency

Every screen is assembled from the **package widgets and our wrappers**. Do not hand-write HTML for something a vendor component already renders.

**Widgets to use**

| Need | Use |
|---|---|
| KPI tile | `<x-adminlte.stat-card>` (wraps `x-adminlte-info-box` / `x-adminlte-small-box`) |
| Panel, section | `<x-adminlte.page-card>` (wraps `x-adminlte-card`) |
| Text, email, number, password | `<x-adminlte-input>` |
| Mobile / contact number | `<x-adminlte-input-contact>` (eleven digits, the leading 0 kept; `js/contact-input.js` is loaded by the admin, portal and registration layouts) |
| Textarea | `<x-adminlte-textarea>` |
| Select, searchable select | `<x-adminlte-select>`, `<x-adminlte-select2>` (server-search pickers use Shared components) |
| Date, date range | `<x-adminlte-input-date>`, `<x-adminlte-date-range>` |
| Toggle | `<x-adminlte-input-switch>` |
| File | `<x-adminlte-input-file>` only for plain posts; Livewire forms use the Shared upload components |
| Modal | `<x-adminlte-modal>` via `<x-adminlte.form-modal>` |
| Button | `<x-adminlte-button>` (label, theme, icon) |
| Alert, callout | `<x-adminlte-alert>`, `<x-adminlte-callout>` |
| Progress | `<x-adminlte-progress>` |
| Profile header | `<x-adminlte-profile-widget>` via `<x-adminlte.profile-card>` |
| Tabs | Bootstrap 4 `nav nav-tabs` + `tab-content` markup (no package tab component) |
| Chart | `ChartHelper` data + Chart.js plugin loaded per page |

**Page types.** Each has one fixed skeleton. A new page copies the skeleton and changes content, never structure.

| Page type | Skeleton, top to bottom |
|---|---|
| Index | `page-header` (title + primary action) → stats strip → `page-card` with toolbar (search, filters, per-page) → table → pagination in the footer |
| Form | `page-header` → one `page-card` per section → footer: Cancel (secondary link) left of Save (primary), right-aligned |
| Show | `page-header` (with record actions) → `profile-card` / summary → tabs, lazy panes |
| Dashboard | Stats row → chart cards → recent tables, each lazy |
| Report | Filter card → result table or chart → Export buttons (PDF, Excel) |
| Modal form | `form-modal`, `modal-lg` for forms, `modal-sm` for confirms |

**Action vocabulary.** The same action looks the same everywhere.

| Action | Icon | Button class |
|---|---|---|
| Add / create | `fas fa-plus` | `btn-primary` |
| Save | `fas fa-save` | `btn-primary` |
| Edit | `fas fa-edit` | `btn-outline-primary btn-sm` |
| View | `fas fa-eye` | `btn-outline-info btn-sm` |
| Delete / void | `fas fa-trash` | `btn-outline-danger btn-sm` + `data-thha-confirm` |
| Approve / record / accept | `fas fa-check` | `btn-success` |
| Decline / reject | `fas fa-times` | `btn-outline-danger` |
| Print / PDF | `fas fa-print`, `fas fa-file-pdf` | `btn-outline-secondary` |
| Export Excel | `fas fa-file-excel` | `btn-outline-success` |
| Cancel, back | none | `btn-secondary` or a plain link |

**Rules**

- **Status** is always `<x-ui.status-badge :status="$enum" />` (uses the enum's `label()` and `badge()`). Never a hand-colored `<span>`.
- **Money** is always `<x-ui.money>` (right-aligned, `PHP 1,500.00`). **Dates** go through `@date` / `TimeHelper` (`M d, Y`; datetime `M d, Y h:i A`).
- **Forms:** label above the field, required fields marked, two columns (`col-md-6`) on desktop, one on mobile; help text in `form-text text-muted`; errors inline from `x-adminlte-input` (`is-invalid` + `invalid-feedback`). Group fields by RBI/DILG section order where a form follows a government form.
- **Tables:** `table table-hover table-sm` inside `table-responsive`; actions column last and right-aligned; numbers right-aligned; at most one primary action per row, the rest in a `btn-group`.
- **Feedback:** success and error are toasts (`notify`); a page-level problem is an `<x-adminlte-alert>`; validation errors are inline; destructive actions always confirm.
- **States:** every list has an empty state (`<x-table.empty>`) and a loading state (`wire:loading` + skeleton); every save button disables while loading; errors never show a stack trace.
- **Layout:** admin uses the AdminLTE fixed sidebar; the portal uses the same widgets and the layout classes set in `config/adminlte.php`. Layout classes are changed **only** there.
- **Look:** colors, spacing, radii are CSS variables under `Tokens` in `base.css`; the package theme colors are not overridden per page.
- **Accessibility:** every input has a label; icon-only buttons have `aria-label` and `title`; contrast follows the AdminLTE defaults; everything is keyboard reachable; tables have `<th scope>`.
- **Mobile:** mobile-first; tables scroll inside `table-responsive`; primary actions stay reachable without horizontal scroll.
- **Text** is `__()`; **icons** are Font Awesome 5 free only.
- A new visual pattern is added to this section and to a wrapper component **before** it is used on a second page.

### 10.7 Markup rules

- No logic in Blade. No queries, no `@php` with business logic, no `Auth::user()->...` role checks. Views format and display.
- Escape with `{{ }}`. `{!! !!}` only for trusted, pre-sanitized HTML (the inlined PDF stylesheet).
- No `<script>`, `<style>`, or `style=""` in views. Exception: a computed width or color from data (`style="width: {{ $pct }}%"`).
- Authorization in views: `@can('update', $bill)`, or a computed `$this->canCreate` from the component.

---

## 11. Authentication and Authorization

| | Admin panel | Homeowner portal |
|---|---|---|
| Model / table | `Admin` / `admins` | `User` / `users` |
| Guard | `web` | `homeowner` |
| Routes | `routes/admin.php` | `routes/portal.php` |
| Rule type | Role / permission | Ownership |

- Guard decides who reaches a route group. Policies decide access to a record.
- Policies take an untyped `$user` and decide the side themselves. A typed `Admin $admin` parameter throws a `TypeError` when a portal user is passed.
- Admin abilities check `$user->hasAnyRole()`. Portal abilities end in `Own` and check ownership. Never mix the two checks.

```php
class BillPolicy
{
    use ResolvesAccount;

    // ===== Admin =====

    public function viewAny($user): bool
    {
        return $this->isAdmin($user)
            && $user->hasAnyRole([DefaultRoleSlug::PRESIDENT, DefaultRoleSlug::TREASURER]);
    }

    // ===== Portal =====

    public function viewOwn($user, Bill $bill): bool
    {
        return $this->isPortalUser($user) && $user->id === $bill->user_id;
    }
}
```

- Register Policies in `AuthServiceProvider`.
- The menu hides items with `'can'`, pointing to Gates generated from the permission catalogue. That is cosmetic; the route and the action still authorize.
- Logins regenerate the session; logouts invalidate it and regenerate the token. Login is throttled.

---

## 12. Observers and Notifications

### 12.1 Observers

Model lifecycle side effects only: generate a reference/control number on `creating`, delete stored files on `deleted`, flush the caches that depend on the model, cancel unbilled dues when a household moves out. Register with `Model::observe()` in `AppServiceProvider::boot()`.

- Methods stay short. Anything larger becomes a Service call. An Observer never calls an Action.
- Observers do not fire for `DB::table()`, `insert()`, or mass `query()->update()`. When an Action uses one of those, it flushes the affected caches itself.
- Observers run **inside** the transaction. Do not send notifications from them (Laravel 7 has no `afterCommit`).

### 12.2 Notifications

One class per user-facing event, queued, past-tense name. Channels: `mail` and the custom SMS channel in `app/Notifications/Channels/`. **Dispatched from an Action after the transaction commits.**

```php
class PaymentRecorded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Payment $payment) {}

    public function via($notifiable): array
    {
        return ['mail', SmsChannel::class];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.payment_recorded.subject'))
            ->line(__('notifications.payment_recorded.body', ['amount' => MoneyHelper::format($this->payment->amount)]));
    }
}
```

All message text lives in `resources/lang/en/notifications.php`.

---

## 13. Assets

There is no build step. Plain files, edited directly in `public/`.

```
public/css/   base.css · layout.css · components.css · print.css
public/css/pdf/rbi.css, receipt.css, ...     stylesheets inlined into PDFs only
public/js/    core.js · toast.js · modal.js · confirm.js · bulk.js · plugins.js
```

- `config/adminlte.php` `plugins` loads our global files through one entry:

```php
'Thha' => [
    'active' => true,
    'files' => [
        ['type' => 'css', 'asset' => true, 'location' => 'css/base.css'],
        ['type' => 'js', 'asset' => true, 'location' => 'js/core.js'],
    ],
],
```

- PDF stylesheets are **not** loaded on admin pages.
- CSS: class selectors with the `thha-` prefix. No IDs as selectors, no `!important`, no inline styles. Colors, spacing, and radii used more than twice are CSS variables under a `Tokens` divider. Mobile-first.
- JS: jQuery and vanilla only, one global `window.Thha`, no inline handlers except `data-*` hooks. `bulk.js` (selection), `confirm.js` (SweetAlert2), `modal.js`, `toast.js`, and the lazy-tab helper in `core.js` are the only behaviors components may rely on.
- Uploaded files are never in `public/`, except public announcement images. They go through the private disk and an authorized route.

---

## 14. PDFs and Exports

### 14.1 Rendering

All PDFs render through `PdfBuilder` (DomPDF). A document has:

| Part | Location |
|---|---|
| View | `resources/views/pdf/{document}.blade.php` |
| Shared layout and THHA letterhead | `resources/views/pdf/layouts/`, `resources/views/pdf/partials/` |
| Stylesheet (inlined) | `public/css/pdf/{document}.css` |
| Data builder | A Query class (`{Document}Query::build()`) that returns a plain array `$data` |
| Text | `resources/lang/en/{module}.php` |

The same view serves the on-screen **preview** (with a toolbar) and the **download** (no toolbar), so the preview and the PDF are always the same document.

### 14.2 DomPDF CSS limits

DomPDF is not a browser. In PDF stylesheets and PDF views:

| Do not use | Use |
|---|---|
| `display: flex`, `inline-flex`, `grid`, `gap` | Tables, `display: inline-block`, margins |
| `position: sticky` | Not needed (the toolbar is screen-only and absent from the PDF) |
| `break-before: page`, `break-inside` | `page-break-before: always`, `page-break-inside: avoid` |
| `@checked`, `<input type="checkbox">` | `<span class="rbi-box">X</span>` (a bordered inline-block box) |
| Fonts outside the DomPDF set | `DejaVu Sans` (full Unicode) or `Helvetica` for Latin-only text |
| `min-height` on table cells | A fixed `height` |

### 14.3 Receipts

Both sides print receipts from the same Query (`ReceiptQuery`, the data) and the same Service (`ReceiptService`, the PDF render) and the same view (`pdf/collections/receipt.blade.php`). Admin controller authorizes `view`; portal controller authorizes `viewOwn`. Only `recorded` payments print.

### 14.4 Excel and CSV

Through a Utility (`CsvHelper`, or an `ExcelBuilder` wrapping `avadim/fast-excel-laravel`). Use `chunk()` for large exports. Never export fields the exporting role cannot see on screen.

---

## 15. Lang Files

All user-facing text is in `resources/lang/en/`. No literal label, heading, button, or message in PHP or Blade.

```
common.php  auth.php  enums.php  notifications.php  validation.php
menu.php  residents.php  households.php  rbi.php  bills.php  payments.php ...
```

```php
return [
    'title' => 'Residents',
    'deleted' => 'Resident removed.',
    'attributes' => ['first_name' => 'first name'],
];
```

- `__('residents.title')` in PHP and Blade. One file per module, snake_case nested keys, `:placeholder` for variables.
- Enum labels in `enums.php`, grouped by enum (`enums.bill_status.paid`).
- JS strings come from `data-*` attributes rendered from lang, or from `window.Thha.lang`, set once in the layout from a small `__('toast')` array.
- A second locale (`fil`) later is a mirrored folder with the same keys.

---

## 16. Security and Privacy

This system holds personal data covered by the Data Privacy Act of 2012 (RA 10173).

- Sensitive fields: PhilSys Card Number, birth date, contact details, mother's maiden name, blood type, income, IDs and proof-of-residency files. Never log them. Never put them in a URL or query string. Show them only to roles and owners that need them.
- **PhilSys:** store and print only the 16-digit **Card Number (PCN)**. The PhilSys Serial Number (PSN) is never collected, stored, or printed.
- Private files use the private disk and an authorized download route that checks a Policy.
- Mass assignment: `$fillable` always. Request input: `$request->validated()` only. Actions receive validated arrays only.
- Livewire public properties are client-visible (section 8.11). `Shared` components trust nothing from the browser; the parent re-validates paths and ids.
- Admin actions that change data write one `admin_activities` row through `ActivityLogService`, called by the Action. Never write that table ad hoc.
- Login throttling, CSRF on every form, `$hidden` on `password` and `remember_token`.
- Cached values never contain a sensitive field (section 9.4).

---

## 17. Testing

PHPUnit through `php artisan test`.

- `tests/Feature/{Admin|Portal|Public}/` for flows. `tests/Smoke/` for one test that visits every parameter-free GET route under each guard and asserts 200 or a login redirect, and mounts every Livewire component.
- Always name the guard: `$this->actingAs($admin, 'web')`, `$this->actingAs($user, 'homeowner')`.
- Livewire: `Livewire::actingAs($admin)->test(ResidentIndex::class)->set('search', 'cruz')->assertSee('Cruz')`.
- **Actions:** one test per Action for the happy path, each domain exception, the activity-log row, and the notification (`Notification::fake()`, asserted after the call returns).
- **Queries:** one test per Query for the result shape, each filter and sort, and the query count.
- **Services:** every capability has a direct unit or feature test that calls it with no Action involved. Accounting rules (balanced entries, closed periods, payment to journal) are mandatory.
- **Index components:** one test asserts search, sort, filter, and that the query count stays within budget:

```php
DB::enableQueryLog();

Livewire::actingAs($admin)->test(ResidentIndex::class)->call('sortBy', 'surname');

$this->assertLessThanOrEqual(15, count(DB::getQueryLog()));
```

- **Lazy parts:** assert the shell renders without the heavy data, then `->call('load')` and assert the data.
- **Shared uploaders:** `Livewire::test(UploadAvatar::class)->set('photo', UploadedFile::fake()->image('a.jpg'))->assertEmitted('file-uploaded')`, plus a rejection test for a wrong type.
- Policies: one test per ability for the allowed role, a denied role, and a portal user.

---

## 18. Git and Quality Gates

- Branch per task: `feature/household-linking`, `chore/cleanup-conventions`.
- Imperative commit messages: `Add property occupancy migration and model`.
- Never commit `.env`, `vendor/`, `node_modules/`, `storage/*.key`, or anything under the private disk.
- Run before finishing any change:

```bash
./vendor/bin/pint --test
php artisan view:cache
php artisan test
```

Plus the section 7.8 self-check. A command that could not run is reported as not run, never as a pass.
