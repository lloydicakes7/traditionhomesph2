<?php

namespace App\Http\Livewire\Admin\Accounting\PeriodClose\Modals;

use App\Actions\Accounting\Periods\ReopenPeriod;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Account\AccountingPeriod;
use App\Utility\PeriodHelper;

/**
 * Reopen the Books — the confirmation a closed month's Reopen action opens.
 *
 * Opened client-side by Bootstrap (`#periodReopenModal`) with the month — a
 * "Y-m" string, which is what the register keys its rows on — on the trigger,
 * and loaded lazily by public/js/modal-loader.js. Reopening puts a locked month
 * back in play, so the statements for it move — which is the reasoning the
 * dialog states.
 */
class PeriodReopenModal extends AdminModalComponent
{
    const MODAL_ID = 'periodReopenModal';

    /** @var string|null Month the confirmation applies to, "Y-m" */
    public $reopeningPeriod = null;

    /** @var string Label of the month the confirmation names */
    public $reopeningPeriodLabel = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadPeriodReopenModal'  => 'load',
        'closePeriodReopenModal' => 'closeModal',
    ];

    // =====================================================================
    // Load
    // =====================================================================

    /**
     * The month arrives as the register's own key ("Y-m"), not a row id.
     */
    public function load(?string $period = null): void
    {
        if (! $period) {
            return;
        }

        $this->authorize('reopen', AccountingPeriod::class);

        $this->reopeningPeriod = (string) $period;
        $this->reopeningPeriodLabel = PeriodHelper::label($period);

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->reopeningPeriod = null;
        $this->reopeningPeriodLabel = '';

        parent::resetModal();
    }

    // =====================================================================
    // Reopen
    // =====================================================================

    public function reopenPeriod(): void
    {
        if (! $this->loaded || ! $this->reopeningPeriod) {
            return;
        }

        $this->authorize('reopen', AccountingPeriod::class);

        app(ReopenPeriod::class)->handle($this->reopeningPeriod, auth('web')->user());

        $this->emit('notify', 'success', $this->reopeningPeriodLabel . ' reopened — postings may be made into it again.');
        $this->emit('period-updated');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.accounting.period-close.modals.period-reopen-modal');
    }
}
