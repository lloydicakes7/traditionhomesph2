<?php

namespace App\Http\Livewire\Admin\Collections\Modals;

use App\Actions\Collections\Payments\RecordPayment;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Payment;
use App\Queries\Collections\Payments\BillOptionQuery;
use App\Queries\Collections\PaymentMethods\PaymentMethodQuery;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

/**
 * Record Payment — one modal for every place money is received.
 *
 * The Invoices page opens it with the invoice whose row was clicked, the
 * Monthly Dues pages with the month's dues, the Amenity Bookings page with a
 * reservation's invoice, and the Payments page with nothing — in which case the
 * officer picks the bill first. All of them end in the same call (see
 * \App\Actions\Collections\Payments\RecordPayment), because there is one ledger
 * whichever kind of bill the money settled.
 *
 * Opened client-side by Bootstrap (`#recordPaymentModal`) with no id from the
 * Payments page, and, when a row already knows the bill, opened by its page
 * with `loadRecordPaymentModal` and its own `modal-open` shell (see
 * public/js/modal-loader.js).
 *
 * The amount defaults to the bill's outstanding balance, which is what is
 * usually being settled, and stays editable for a part payment.
 *
 * A bill the portal has already been paid against is not one this dialog can
 * collect: while that receipt waits, the same money may not be recorded again,
 * so the service refuses it and the refusal is shown in the dialog with what to
 * do instead (approve the receipt on the Payments page). Bills carrying a
 * waiting receipt are left out of the picker for the same reason.
 *
 * How the money arrived is the dialog's own first choice: leaving it alone
 * records the receipt as in-person (see \App\Models\PaymentMethod::IN_PERSON_LABEL),
 * because money taken over the counter is the usual case, and the officer may
 * name one of the office's online accounts instead. The proof the payer sent —
 * a transfer screenshot, a deposit slip — is attached here and stored with the
 * receipt.
 */
class RecordPaymentModal extends AdminModalComponent
{
    use WithFileUploads;

    const MODAL_ID = 'recordPaymentModal';

    /** @var string The id of the bill being settled */
    public $bill = '';

    /** @var string */
    public $amount = 0;

    /** @var string */
    public $payment_method_id = '';

    /** @var mixed Uploaded proof of payment */
    public $proof;

    /** @var string */
    public $reference_no = '';

    /** @var string */
    public $paid_at = '';

    /** @var string */
    public $notes = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openRecordPaymentModal'  => 'open',
        'loadRecordPaymentModal'  => 'load',
        'closeRecordPaymentModal' => 'closeModal',
    ];

    /** @var BillOptionQuery */
    protected $query;

    /** @var PaymentMethodQuery */
    protected $paymentMethodQuery;

    /** @var RecordPayment */
    protected $recordPayment;

    public function boot(
        BillOptionQuery $query,
        PaymentMethodQuery $paymentMethodQuery,
        RecordPayment $recordPayment
    ) {
        $this->query = $query;
        $this->paymentMethodQuery = $paymentMethodQuery;
        $this->recordPayment = $recordPayment;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when these run
    // =====================================================================

    /**
     * Open the dialog blank, so the officer picks which bill the money
     * settles (the Payments page).
     */
    public function open(): void
    {
        $this->authorize('create', Payment::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->paid_at = Carbon::now()->format('Y-m-d\TH:i');

        $this->markLoaded();
    }

    /**
     * Open the dialog with the bill already known (a bill's own list).
     *
     * @param  array|int|string  $payload  The bill's id, or an array carrying `bill`
     */
    public function load($payload = []): void
    {
        $this->authorize('create', Payment::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->bill = (string) (is_array($payload) ? ($payload['bill'] ?? '') : $payload);
        $this->paid_at = Carbon::now()->format('Y-m-d\TH:i');

        $this->amount = $this->bill ? (float) $this->query->find($this->bill)->balance : 0;

        $this->markLoaded();
        $this->dispatchBrowserEvent('modal-open', ['id' => self::MODAL_ID]);
    }

    protected function resetModal(): void
    {
        $this->resetModalState();
        $this->resetValidation();

        parent::resetModal();
    }

    private function resetModalState(): void
    {
        $this->bill = '';
        $this->amount = 0;
        $this->payment_method_id = '';
        $this->reference_no = '';
        $this->proof = null;
        $this->paid_at = '';
        $this->notes = '';
    }

    /**
     * The officer picked a bill on the Payments page: offer to settle it in
     * full.
     */
    public function updatedBill($value): void
    {
        $this->amount = $value
            ? (float) $this->query->find($value)->balance
            : 0;
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('create', Payment::class);

        $this->validate($this->rules(), $this->messages());

        // A bill the portal has already been paid against is refused by the
        // action, and the refusal is shown beside the bill rather than
        // swallowed: the officer's move is to approve that receipt.
        try {
            $payment = $this->recordPayment->handle(
                $this->query->find($this->bill),
                [
                    'amount'            => $this->amount,
                    'payment_method_id' => $this->payment_method_id ?: null,
                    'reference_no'      => $this->reference_no ?: null,
                    'proof'             => $this->proof,
                    'paid_at'           => $this->paid_at ?: now(),
                    'notes'             => $this->notes ?: null,
                ],
                auth('web')->user()
            );
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $field = array_key_first($errors);
            $field = $field === 'entry_date' ? 'paid_at' : $field;

            $this->addError(
                in_array($field, ['bill', 'paid_at'], true) ? $field : 'bill',
                $errors[$field][0] ?? 'That bill cannot be collected again.'
            );

            return;
        }

        $this->emit('notify', 'success', 'Payment recorded.');
        $this->emit('payment-recorded', $payment->id);

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'bill'              => ['required', 'string'],
            'amount'            => ['required', 'numeric', 'min:0.01'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'reference_no'      => ['nullable', 'string', 'max:100'],
            'proof'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'paid_at'           => ['required', 'date'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Short, distinct messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'bill.required'       => 'Choose the bill this payment settles.',
            'amount.required'     => 'Enter the amount received.',
            'amount.min'          => 'The amount must be more than zero.',
            'proof.image'     => 'The proof of payment must be an image.',
            'proof.max'       => 'The proof of payment must be 2MB or smaller.',
            'paid_at.required' => 'Enter when the payment was received.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'bill'              => 'bill',
            'amount'            => 'amount',
            'payment_method_id' => 'payment method',
            'reference_no'      => 'reference number',
            'proof'             => 'proof of payment',
            'paid_at'           => 'date received',
            'notes'             => 'notes',
        ];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        if (! $this->loaded) {
            return view('livewire.admin.collections.modals.record-payment-modal', [
                'bills'          => [],
                'methods'        => [],
                'selected'       => null,
                'pending'        => null,
                'selectedMethod' => null,
            ]);
        }

        $methods = $this->paymentMethodQuery->active();

        $selected = $this->bill ? $this->query->find($this->bill) : null;

        return view('livewire.admin.collections.modals.record-payment-modal', [
            'bills'          => $this->query->options(),
            'methods'        => $methods,
            'selected'       => $selected,
            'pending'        => $selected ? $this->query->pending($selected) : null,
            'selectedMethod' => $this->payment_method_id
                ? $methods->firstWhere('id', (int) $this->payment_method_id)
                : null,
        ]);
    }
}
