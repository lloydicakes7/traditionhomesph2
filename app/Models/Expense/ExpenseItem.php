<?php

namespace App\Models\Expense;

use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * One line of an expense — what was bought, filed under which category, and
 * what it came to.
 *
 * The category's name is snapshotted onto the line at billing time, so
 * renaming or retiring a category never rewrites an expense already recorded —
 * the same rule an invoice line follows with its product.
 */
class ExpenseItem extends Model
{
    protected $fillable = [
        'expense_id',
        'expense_category_id',
        'category_name',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The expense this line belongs to.
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * The category this line was filed under.
     */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The category as a record writes it — the snapshot taken at billing time.
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->category_name ?: ($this->category?->name ?? 'Uncategorised');
    }

    /**
     * The amount as a record writes it: "₱350.00".
     */
    public function getAmountDisplayAttribute(): string
    {
        return MoneyHelper::format($this->amount);
    }
}
