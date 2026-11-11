<?php

namespace App\Http\Livewire\Admin\Accounting\ChartOfAccounts\Modals;

use App\Actions\Accounting\ChartOfAccounts\CreateAccount;
use App\Actions\Accounting\ChartOfAccounts\UpdateAccount;
use App\Enums\AccountKind;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Account\Account;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Add / Edit an account on the chart.
 *
 * The office chooses one of three kinds — income, expense, or cash — and the
 * books derive the accounting type and whether it holds money. The code is
 * suggested in the kind's block but stays editable. A cash account may carry a
 * starting balance on the day it is added; the entry behind it is posted by
 * the Action, never written here.
 *
 * Opened client-side by Bootstrap (`#accountFormModal`): a blank dialog from the
 * page's Add Account trigger (`openAccountFormModal`), a pre-filled one carrying
 * the account's id (`loadAccountFormModal`), both relayed by
 * public/js/modal-loader.js.
 */
class AccountFormModal extends AdminModalComponent
{
    const MODAL_ID = 'accountFormModal';

    /** @var bool Editing an existing account when true */
    public $isEditing = false;

    /** @var int|null Account being edited; null when adding */
    public $editingAccountId = null;

    /** @var string The code last suggested by the form */
    public $suggestedCode = '';

    /** @var bool Whether the kind can no longer change */
    public $kindLocked = false;

    /** @var string|null The category page that owns this account */
    public $managedBy = '';

    /** @var bool Whether this is a system account */
    public $systemAccount = false;

    /** @var array<string, mixed> */
    public $form = [
        'code'             => '',
        'name'             => '',
        'kind'             => AccountKind::INCOME,
        'description'      => '',
        'starting_balance' => '0',
        'balance_as_of'    => '',
    ];

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openAccountFormModal'  => 'openCreate',
        'loadAccountFormModal'  => 'openEdit',
        'closeAccountFormModal' => 'closeModal',
    ];

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', Account::class);

        $this->isEditing = false;
        $this->editingAccountId = null;
        $this->suggestedCode = Account::nextCode(AccountKind::INCOME());
        $this->kindLocked = false;
        $this->managedBy = '';
        $this->systemAccount = false;
        $this->form = [
            'code'             => $this->suggestedCode,
            'name'             => '',
            'kind'             => AccountKind::INCOME,
            'description'      => '',
            'starting_balance' => '0',
            'balance_as_of'    => now()->toDateString(),
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Account::class);

        $account = Account::findOrFail($id);
        $kind = $account->kind();

        $this->isEditing = true;
        $this->editingAccountId = $account->id;
        $this->suggestedCode = (string) $account->code;
        $this->managedBy = (string) $account->managedByLabel();
        $this->systemAccount = $account->isSystem();
        $this->kindLocked = $account->isSystem()
            || $account->isDefaultCash()
            || $this->managedBy !== ''
            || $account->journalLines()->exists();
        $this->form = [
            'code'             => (string) $account->code,
            'name'             => (string) $account->name,
            'kind'             => $kind ? $kind->value : '',
            'description'      => (string) $account->description,
            'starting_balance' => '0',
            'balance_as_of'    => now()->toDateString(),
        ];
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingAccountId = null;
        $this->suggestedCode = '';
        $this->kindLocked = false;
        $this->managedBy = '';
        $this->systemAccount = false;
        $this->form = [
            'code'             => '',
            'name'             => '',
            'kind'             => AccountKind::INCOME,
            'description'      => '',
            'starting_balance' => '0',
            'balance_as_of'    => '',
        ];
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Kind / cash account
    // =====================================================================

    public function updatedFormKind($value): void
    {
        if (! $this->isEditing) {
            $suggested = Account::nextCode(AccountKind::from($value));

            if ($this->form['code'] === $this->suggestedCode || $this->form['code'] === '') {
                $this->form['code'] = $suggested;
            }

            $this->suggestedCode = $suggested;
        }

        if ($value !== AccountKind::MONEY) {
            $this->form['starting_balance'] = '0';
        }
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded || $this->systemAccount || $this->managedBy !== '') {
            return;
        }

        $this->isEditing
            ? $this->authorize('update', Account::class)
            : $this->authorize('create', Account::class);

        $account = $this->editingAccountId ? Account::findOrFail($this->editingAccountId) : null;

        $this->validate(
            $this->prefixedRules($this->rules($account)),
            [
                'form.code.required' => 'Give the account a code.',
                'form.code.digits_between' => 'The code must be 3 to 6 digits.',
                'form.code.unique' => 'That code is already used by another account.',
                'form.name.required' => 'Give the account a name.',
                'form.kind.required' => 'Choose what kind of account this is.',
                'form.balance_as_of.required_if' => 'The starting balance needs the date it applies to.',
            ]
        );

        $payload = [
            'code'             => trim((string) $this->form['code']),
            'name'             => $this->form['name'],
            'kind'             => $this->form['kind'],
            'description'      => $this->form['description'],
            'starting_balance' => $this->form['starting_balance'],
            'balance_as_of'    => $this->form['balance_as_of'],
        ];

        try {
            if ($account) {
                app(UpdateAccount::class)->handle($account, $payload, auth('web')->user());

                $this->emit('notify', 'success', 'Account ' . $account->code . ' updated.');
            } else {
                $account = app(CreateAccount::class)->handle($payload, auth('web')->user());

                $this->emit('notify', 'success', 'Account ' . $account->code . ' added to the chart.');
            }
        } catch (ValidationException $e) {
            $this->showActionError($e);

            return;
        }

        $this->emit('account-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * What an account on the chart has to be.
     *
     * @return array<string, mixed>
     */
    private function rules(?Account $account = null): array
    {
        $isNewMoney = ! $account && $this->form['kind'] === AccountKind::MONEY;

        return [
            'code'             => [
                'required',
                'digits_between:3,6',
                Rule::unique('accounts', 'code')->ignore($account ? $account->id : null),
            ],
            'name'             => ['required', 'string', 'max:255'],
            'kind'             => ['required', Rule::in(array_column(AccountKind::cases(), 'value'))],
            'description'      => ['nullable', 'string', 'max:255'],
            'starting_balance' => $isNewMoney ? ['required', 'numeric', 'min:0'] : ['nullable', 'numeric', 'min:0'],
            'balance_as_of'    => $isNewMoney ? ['required', 'date'] : ['nullable', 'date'],
        ];
    }

    /**
     * Put a rule the Action refused onto the field the officer can fix.
     */
    private function showActionError(ValidationException $e): void
    {
        foreach ($e->errors() as $field => $messages) {
            $target = $field === 'account'
                ? 'account'
                : ($field === 'entry_date' ? 'form.balance_as_of' : 'form.' . $field);

            $this->addError($target, $messages[0]);
        }
    }

    /**
     * The chart's rules, bound to the form's own field names.
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function prefixedRules(array $rules): array
    {
        $prefixed = [];

        foreach ($rules as $field => $rule) {
            $prefixed['form.' . $field] = $rule;
        }

        return $prefixed;
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.accounting.chart-of-accounts.modals.account-form-modal', [
            'kinds' => AccountKind::options(),
            'readOnly' => $this->systemAccount || $this->managedBy !== '',
        ]);
    }
}
