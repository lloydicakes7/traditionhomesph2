<?php

namespace App\Http\Livewire\Admin\Accounting\ChartOfAccounts\Modals;

use App\Actions\Accounting\ChartOfAccounts\DeleteAccount;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Account\Account;
use App\Queries\Accounting\ChartOfAccountsQuery;
use Illuminate\Validation\ValidationException;

/**
 * Remove Account — the confirmation a row's Remove action opens.
 *
 * Opened client-side by Bootstrap (`#accountDeleteModal`) with the account's id
 * on the trigger, and loaded lazily by public/js/modal-loader.js. Only an account
 * with nothing posted to it and no category, product, cash path or transfer
 * pointing at it may go; the dialog reads back what points at it and why it
 * cannot be removed, so the officer sees the answer before they commit.
 */
class AccountDeleteModal extends AdminModalComponent
{
    const MODAL_ID = 'accountDeleteModal';

    /** @var int|null Account the confirmation applies to */
    public $deletingAccountId = null;

    /** @var string Code and name of the account the confirmation names */
    public $deletingAccountLabel = '';

    /** @var array<int, array<string, mixed>> Related records the dialog lists */
    public $deleteSummary = [];

    /** @var string|null Why the account cannot be removed, when it cannot */
    public $deleteBlocker = null;

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadAccountDeleteModal'  => 'load',
        'closeAccountDeleteModal' => 'closeModal',
    ];

    /** @var ChartOfAccountsQuery */
    protected $chart;

    public function boot(ChartOfAccountsQuery $chart)
    {
        $this->chart = $chart;
    }

    // =====================================================================
    // Load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('delete', Account::class);

        $account = Account::findOrFail($id);

        $this->deletingAccountId = $account->id;
        $this->deletingAccountLabel = $account->label;
        $this->deleteSummary = $this->chart->deletionSummary($account);
        $this->deleteBlocker = $account->deletionBlocker();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->deletingAccountId = null;
        $this->deletingAccountLabel = '';
        $this->deleteSummary = [];
        $this->deleteBlocker = null;

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    /**
     * The Action re-checks the rules the dialog showed, so an account the books
     * already name is refused with the reason rather than failing at the driver.
     */
    public function delete(): void
    {
        if (! $this->loaded || ! $this->deletingAccountId) {
            return;
        }

        $this->authorize('delete', Account::class);

        $account = Account::findOrFail($this->deletingAccountId);

        try {
            app(DeleteAccount::class)->handle($account, auth('web')->user());
        } catch (ValidationException $e) {
            $this->emit('notify', 'warning', $e->validator->errors()->first());

            return;
        }

        $this->emit('notify', 'success', 'Account ' . $account->code . ' removed from the chart.');
        $this->emit('account-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.accounting.chart-of-accounts.modals.account-delete-modal');
    }
}
