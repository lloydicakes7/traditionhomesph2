<?php

namespace App\Http\Livewire\Admin\Collections\MonthlyDues\Modals;

use App\Actions\Collections\MonthlyDues\CreatePastMonthlyDues;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Bill\Bill;
use App\Queries\Collections\MonthlyDues\PastMonthlyDueQuery;
use App\Utility\MoneyHelper;
use App\Utility\PeriodHelper;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class PastMonthlyDueModal extends AdminModalComponent
{
    const MODAL_ID = 'pastMonthlyDueModal';

    const MAX_MONTHS = 120;

    /** @var string 'single' bills one homeowner, 'all' bills every homeowner */
    public $scope = 'single';

    /** @var string The account billed when the scope is single */
    public $user_id = '';

    /** @var string First month billed, "Y-m" */
    public $from = '';

    /** @var string Last month billed, "Y-m" */
    public $to = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'openPastMonthlyDueModal'  => 'openCreate',
        'closePastMonthlyDueModal' => 'closeModal',
    ];

    /** @var PastMonthlyDueQuery */
    protected $query;

    /** @var CreatePastMonthlyDues */
    protected $createPastDues;

    public function boot(PastMonthlyDueQuery $query, CreatePastMonthlyDues $createPastDues)
    {
        $this->query = $query;
        $this->createPastDues = $createPastDues;
    }

    // =====================================================================
    // Lazy load — the shell is already on screen when this runs
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('generateMonthlyDues', Bill::class);

        $this->resetModalState();
        $this->resetValidation();

        $this->from = Carbon::now()->subMonths(self::MAX_MONTHS - 1)->format(PeriodHelper::FORMAT);
        $this->to = PeriodHelper::current();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetModalState();
        $this->resetValidation();

        parent::resetModal();
    }

    private function resetModalState(): void
    {
        $this->scope = 'single';
        $this->user_id = '';
        $this->from = '';
        $this->to = '';
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('generateMonthlyDues', Bill::class);

        $this->validate([
            'scope'   => ['required', Rule::in(['single', 'all'])],
            'user_id' => ['required_if:scope,single', 'nullable', 'exists:users,id'],
            'from'    => ['required', 'date_format:Y-m'],
            'to'      => ['required', 'date_format:Y-m'],
        ], [
            'user_id.required_if' => 'Choose the homeowner whose past dues are billed.',
            'from.required'       => 'Choose the first month to bill.',
            'to.required'         => 'Choose the last month to bill.',
        ], [
            'user_id' => 'homeowner',
            'from'    => 'from month',
            'to'      => 'to month',
        ]);

        $from = Carbon::createFromFormat(PeriodHelper::FORMAT, $this->from)->startOfMonth();
        $to = Carbon::createFromFormat(PeriodHelper::FORMAT, $this->to)->startOfMonth();

        if ($from->greaterThan($to)) {
            $this->addError('from', 'The first month cannot be after the last month.');

            return;
        }

        if ($to->greaterThan(Carbon::now()->startOfMonth())) {
            $this->addError('to', 'The last month cannot be later than the current month.');

            return;
        }

        if ($from->diffInMonths($to) + 1 > self::MAX_MONTHS) {
            $this->addError('from', 'A single batch can cover at most ' . self::MAX_MONTHS . ' months.');

            return;
        }

        $result = $this->createPastDues->handle([
            'scope'   => $this->scope,
            'user_id' => $this->scope === 'single' ? $this->user_id : null,
            'from'    => $this->from,
            'to'      => $this->to,
        ], auth('web')->user());

        if ($result['created'] === 0) {
            $this->emit('notify', 'warning', $result['skipped'] > 0
                ? 'Every selected month is already billed — nothing new was raised.'
                : 'No homeowner in the selected scope has anything to bill.');

            return;
        }

        $message = $result['created'] . ' monthly due invoice(s) created.';

        if ($result['skipped'] > 0) {
            $message .= ' ' . $result['skipped'] . ' already billed and skipped.';
        }

        $this->emit('notify', 'success', $message);
        $this->emit('due-saved');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        if (! $this->loaded) {
            return view('livewire.admin.collections.monthly-dues.modals.past-monthly-due-modal', [
                'owners'  => [],
                'preview' => $this->emptyPreview(),
            ]);
        }

        return view('livewire.admin.collections.monthly-dues.modals.past-monthly-due-modal', [
            'owners'  => $this->query->ownerOptions(),
            'preview' => $this->preview(),
        ]);
    }

    private function preview(): array
    {
        if (! $this->isMonth($this->from) || ! $this->isMonth($this->to)) {
            return $this->emptyPreview();
        }

        return $this->query->preview(
            $this->scope,
            $this->scope === 'single' && $this->user_id ? (int) $this->user_id : null,
            $this->from,
            $this->to
        );
    }

    private function isMonth($value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value) === 1;
    }

    private function emptyPreview(): array
    {
        return ['created' => 0, 'skipped' => 0, 'total' => 0.0, 'total_display' => MoneyHelper::format(0)];
    }
}
