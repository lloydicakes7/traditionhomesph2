<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues\Modals;

use App\Actions\Collections\MonthlyDues\RecordAdvanceMonthlyDues;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Queries\Collections\MonthlyDues\AdvancePaymentQuery;
use App\Queries\Collections\PaymentMethods\PaymentMethodQuery;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AdvancePaymentModal extends AdminModalComponent
{
    const MODAL_ID = 'advancePaymentModal';

    const MAX_MONTHS = 120;

    /** @var string The homeowner prepaying their dues */
    public $user_id = '';

    /** @var string First month covered, "Y-m" */
    public $from = '';

    /** @var string Last month covered, "Y-m" */
    public $to = '';

    /** @var string The advance deposit the money is drawn from, when one is used */
    public $advance_deposit_id = '';

    /** @var string */
    public $payment_method_id = '';

    /** @var string */
    public $reference_no = '';

    /** @var string */
    public $paid_at = '';

    /** @var string */
    public $notes = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openAdvancePaymentModal'  => 'open',
        'closeAdvancePaymentModal' => 'closeModal',
    ];

    /** @var AdvancePaymentQuery */
    protected $query;

    /** @var PaymentMethodQuery */
    protected $paymentMethods;

    /** @var RecordAdvanceMonthlyDues */
    protected $recordAdvance;

    public function boot(
        AdvancePaymentQuery $query,
        PaymentMethodQuery $paymentMethods,
        RecordAdvanceMonthlyDues $recordAdvance
    ) {
        $this->query = $query;
        $this->paymentMethods = $paymentMethods;
        $this->recordAdvance = $recordAdvance;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function open(): void
    {
        $this->authorizeAccess();

        $this->resetModalState();
        $this->resetValidation();

        $this->from = Carbon::now()->format('Y-m');
        $this->to = Carbon::now()->format('Y-m');
        $this->paid_at = Carbon::now()->format('Y-m-d\TH:i');

        $this->markLoaded();
    }

    public function updatedUserId(): void
    {
        $this->advance_deposit_id = '';
    }

    protected function resetModal(): void
    {
        $this->resetModalState();
        $this->resetValidation();

        parent::resetModal();
    }

    private function resetModalState(): void
    {
        $this->user_id = '';
        $this->from = '';
        $this->to = '';
        $this->advance_deposit_id = '';
        $this->payment_method_id = '';
        $this->reference_no = '';
        $this->paid_at = '';
        $this->notes = '';
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorizeAccess();

        $this->validate([
            'user_id'            => ['required', 'exists:users,id'],
            'from'               => ['required', 'date_format:Y-m'],
            'to'                 => ['required', 'date_format:Y-m'],
            'advance_deposit_id' => ['nullable', 'exists:advance_deposits,id'],
            'payment_method_id'  => ['nullable', 'exists:payment_methods,id'],
            'reference_no'       => ['nullable', 'string', 'max:100'],
            'paid_at'            => ['required', 'date'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ], [
            'user_id.required' => 'Choose the homeowner whose dues are prepaid.',
            'from.required'    => 'Choose the first month to cover.',
            'to.required'      => 'Choose the last month to cover.',
            'paid_at.required' => 'Enter when the payment was received.',
        ], [
            'user_id'            => 'homeowner',
            'from'               => 'from month',
            'to'                 => 'to month',
            'advance_deposit_id' => 'advance deposit',
            'payment_method_id'  => 'payment method',
            'reference_no'       => 'reference number',
            'paid_at'            => 'date received',
            'notes'              => 'notes',
        ]);

        $this->guardRange();

        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        try {
            $result = $this->recordAdvance->handle([
                'user_id'            => $this->user_id,
                'from'               => $this->from,
                'to'                 => $this->to,
                'advance_deposit_id' => $this->advance_deposit_id ?: null,
                'payment_method_id'  => $this->payment_method_id ?: null,
                'reference_no'       => $this->reference_no ?: null,
                'paid_at'            => $this->paid_at ?: now(),
                'notes'              => $this->notes ?: null,
            ], auth('web')->user());
        } catch (ValidationException $exception) {
            $this->showValidationError($exception->errors());

            return;
        }

        if ($result['settled'] === 0) {
            $this->emit('notify', 'warning', $this->nothingSettledMessage($result));

            return;
        }

        $message = $result['settled'] . ' monthly due invoice(s) paid in advance, '
            . $result['total_display'] . ' received.';

        if ($result['skipped'] > 0) {
            $message .= ' ' . $result['skipped'] . ' month(s) were already settled and skipped.';
        }

        $this->emit('notify', 'success', $message);
        $this->emit('due-saved');
        $this->emit('payment-recorded');
        $this->emit('advance-deposit-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function authorizeAccess(): void
    {
        $this->authorize('applyAdvancePayment', Bill::class);
        $this->authorize('create', Payment::class);
    }

    private function guardRange(): void
    {
        if ($this->from > $this->to) {
            $this->addError('from', 'The first month cannot be after the last month.');

            return;
        }

        $months = (int) Carbon::createFromFormat('Y-m', $this->from)
            ->startOfMonth()
            ->diffInMonths(Carbon::createFromFormat('Y-m', $this->to)->startOfMonth()) + 1;

        if ($months > self::MAX_MONTHS) {
            $this->addError('from', 'A single batch can cover at most ' . self::MAX_MONTHS . ' months.');
        }
    }

    private function showValidationError(array $errors): void
    {
        $field = array_key_first($errors);
        $known = ['user_id', 'from', 'to', 'advance_deposit_id', 'payment_method_id', 'reference_no', 'paid_at', 'notes'];

        $this->addError(
            in_array($field, $known, true) ? $field : 'from',
            $errors[$field][0] ?? 'The advance payment could not be recorded.'
        );
    }

    private function nothingSettledMessage(array $result): string
    {
        if ($result['skipped'] === 0) {
            return 'No month in this batch could be settled.';
        }

        if (($result['created'] ?? 0) === 0) {
            return 'Every month in this batch is already settled, so nothing was paid.';
        }

        return 'The months were billed, but nothing could be settled. Check the bills and try again.';
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        if (! $this->loaded) {
            return view('livewire.admin.collections.monthly-dues.modals.advance-payment-modal', [
                'owners'         => [],
                'deposits'       => [],
                'methods'        => [],
                'selectedMethod' => null,
                'preview'        => $this->query->preview(0, null, null),
            ]);
        }

        $methods = $this->paymentMethods->active();

        return view('livewire.admin.collections.monthly-dues.modals.advance-payment-modal', [
            'owners'         => $this->query->ownerOptions(),
            'deposits'       => $this->user_id ? $this->query->depositOptions((int) $this->user_id) : [],
            'methods'        => $methods,
            'selectedMethod' => $this->payment_method_id
                ? $methods->firstWhere('id', (int) $this->payment_method_id)
                : null,
            'preview'        => $this->query->preview((int) $this->user_id, $this->from, $this->to),
        ]);
    }
}
