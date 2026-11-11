<?php

namespace App\Http\Livewire\Admin\Accounting\PeriodClose\Modals;

use App\Actions\Accounting\Periods\ClosePeriod;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Account\AccountingPeriod;
use App\Utility\PeriodHelper;

/**
 * Close the Books — the confirmation a month's Close Month action opens.
 *
 * Opened client-side by Bootstrap (`#periodCloseModal`) with the month — a
 * "Y-m" string, which is what the register keys its rows on — on the trigger,
 * and loaded lazily by public/js/modal-loader.js. Closing is what stops every
 * module from posting into the month, so the dialog states it and takes an
 * optional note before it does.
 */
class PeriodCloseModal extends AdminModalComponent
{
    const MODAL_ID = 'periodCloseModal';

    /** @var string|null Month the confirmation applies to, "Y-m" */
    public $closingPeriod = null;

    /** @var string Label of the month the confirmation names */
    public $closingPeriodLabel = '';

    /** @var string Notes typed for the closing */
    public $notes = '';

    /** @var array<string, string> Modal loading + close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadPeriodCloseModal'  => 'load',
        'closePeriodCloseModal' => 'closeModal',
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

        $this->authorize('close', AccountingPeriod::class);

        $this->closingPeriod = (string) $period;
        $this->closingPeriodLabel = PeriodHelper::label($period);
        $this->notes = '';

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->closingPeriod = null;
        $this->closingPeriodLabel = '';
        $this->notes = '';

        parent::resetModal();
    }

    // =====================================================================
    // Close
    // =====================================================================

    public function closePeriod(): void
    {
        if (! $this->loaded || ! $this->closingPeriod) {
            return;
        }

        $this->authorize('close', AccountingPeriod::class);

        app(ClosePeriod::class)->handle($this->closingPeriod, auth('web')->user(), $this->notes);

        $this->emit('notify', 'success', $this->closingPeriodLabel . ' closed — nothing can be posted into it now.');
        $this->emit('period-updated');

        $this->resetModal();
        $this->dispatchBrowserEvent('modal-close', ['id' => self::MODAL_ID]);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.accounting.period-close.modals.period-close-modal');
    }
}
