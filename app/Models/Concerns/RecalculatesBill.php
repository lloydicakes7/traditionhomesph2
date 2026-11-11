<?php

namespace App\Models\Concerns;

use App\Enums\BillStatus;
use App\Enums\PaymentStatus;
use App\Utility\MoneyHelper;

trait RecalculatesBill
{
    /**
     * Refresh the bill's money columns and its status from its own lines and
     * the payments recorded against it.
     *
     * Only receipts the office has **recorded** count: a payment filed from
     * the portal waits as `pending`, and until an officer records it the bill
     * still stands for the full amount (see \App\Enums\PaymentStatus).
     *
     * @return void
     */
    public function recalculate(): void
    {
        $paid = round((float) $this->payments()
            ->where('status', PaymentStatus::RECORDED)
            ->sum('amount'), 2);

        $this->subtotal = round($this->billSubtotal(), 2);
        $this->paid_amount = $paid;
        $this->total = round($this->subtotal - (float) $this->discount + (float) $this->penalty, 2);
        $this->balance = round($this->total - $paid, 2);
        $this->status = $this->resolvedStatus();

        $this->save();
    }

    /**
     * What the bill's own charges come to, before discount and penalty.
     *
     * @return float
     */
    abstract protected function billSubtotal(): float;

    /**
     * The status this bill now stands in.
     *
     */
    protected function resolvedStatus(): string
    {
        if ($this->status === BillStatus::CANCELLED) {
            return BillStatus::CANCELLED;
        }

        if ($this->balance <= 0) {
            return BillStatus::PAID;
        }

        if ($this->due_date && $this->due_date->endOfDay()->isPast()) {
            return BillStatus::OVERDUE;
        }

        return $this->paid_amount > 0 ? BillStatus::PARTIAL : BillStatus::UNPAID;
    }

    // =====================================================================
    // Display — the columns both bills carry
    // =====================================================================

    /**
     * The BillStatus enum instance for this bill.
     */
    public function getStatusEnumAttribute(): ?BillStatus
    {
        return $this->status ? BillStatus::tryFrom($this->status) : null;
    }

    /**
     * AdminLTE badge theme class for this bill's status.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status_enum
            ? $this->status_enum->badgeTheme()
            : 'secondary';
    }

    /**
     * The label of this bill's status, or the raw value when it is unknown.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status_enum
            ? $this->status_enum->label()
            : (string) $this->status;
    }

    /**
     * What the bill's own charges come to, before discount or penalty, as a
     * list writes it: "₱350.00".
     */
    public function getSubtotalDisplayAttribute(): string
    {
        return MoneyHelper::format($this->subtotal);
    }

    /**
     * The billed amount as a list writes it: "₱350.00".
     */
    public function getTotalDisplayAttribute(): string
    {
        return MoneyHelper::format($this->total);
    }

    /**
     * What has been paid as a list writes it: "₱350.00".
     */
    public function getPaidDisplayAttribute(): string
    {
        return MoneyHelper::format($this->paid_amount);
    }

    /**
     * The outstanding balance as a list writes it: "₱350.00".
     */
    public function getBalanceDisplayAttribute(): string
    {
        return MoneyHelper::format($this->balance);
    }

    /**
     * The penalty as a list writes it: "₱0.00".
     */
    public function getPenaltyDisplayAttribute(): string
    {
        return MoneyHelper::format($this->penalty);
    }

    /**
     * The discount as a list writes it: "₱0.00".
     */
    public function getDiscountDisplayAttribute(): string
    {
        return MoneyHelper::format($this->discount);
    }
}
