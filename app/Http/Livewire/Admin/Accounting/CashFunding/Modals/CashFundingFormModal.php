<?php

namespace App\Http\Livewire\Admin\Accounting\CashFunding\Modals;

use App\Actions\Accounting\CashFunding\RecordCashFunding;
use App\Http\Livewire\AdminModalComponent;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Accounting\ChartOfAccountsQuery;
use Illuminate\Validation\Rule;

class CashFundingFormModal extends AdminModalComponent
{
    const MODAL_ID = 'cashFundingFormModal';

    /** @var string */
    public $entry_date = '';

    /** @var string */
    public $to_account_id = '';

    /** @var string */
    public $amount = '';

    /** @var string */
    public $memo = '';

    /** @var string */
    public $reference_no = '';

    /** @var array<string, string> */
    protected $listeners = [
        'openCashFundingFormModal'  => 'openCreate',
        'closeCashFundingFormModal' => 'closeModal',
    ];

    /** @var ChartOfAccountsQuery */
    protected $chart;

    public function boot(ChartOfAccountsQuery $chart)
    {
        $this->chart = $chart;
    }

    // ===== Lifecycle =====

    public function openCreate(): void
    {
        $this->authorize('create', JournalEntry::class);

        $this->resetForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetForm();
        $this->resetValidation();

        parent::resetModal();
    }

    private function resetForm(): void
    {
        $this->entry_date = now()->toDateString();
        $this->to_account_id = '';
        $this->amount = '';
        $this->memo = '';
        $this->reference_no = '';
    }

    // ===== Actions =====

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('create', JournalEntry::class);

        $this->validate($this->rules(), $this->messages());

        $entry = app(RecordCashFunding::class)->handle([
            'entry_date'    => $this->entry_date,
            'to_account_id' => $this->to_account_id,
            'amount'        => $this->amount,
            'memo'          => $this->memo,
            'reference_no'  => $this->reference_no,
        ], auth('web')->user());

        $this->emit('notify', 'success', 'Cash funding ' . $entry->entry_no . ' recorded.');
        $this->emit('cash-funding-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // ===== Rules =====

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'entry_date'    => 'required|date',
            'to_account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where('is_cash', true),
            ],
            'amount'        => 'required|numeric|min:0.01|max:9999999999.99',
            'memo'          => 'required|string|max:255',
            'reference_no'  => 'nullable|string|max:100',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'entry_date.required'    => 'Give the funding a date.',
            'to_account_id.required' => 'Choose the account the cash was added to.',
            'to_account_id.exists'   => 'Choose a cash or bank account.',
            'amount.required'        => 'Enter the amount.',
            'amount.min'             => 'The amount has to be more than zero.',
            'memo.required'          => 'Say where the cash came from or what it is for.',
        ];
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.accounting.cash-funding.modals.cash-funding-form-modal', [
            'accounts' => $this->loaded ? $this->chart->cashOptions() : [],
        ]);
    }
}
