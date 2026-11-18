<?php

namespace App\Models\Expense;

use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Models\Supplier;
use App\Utility\MoneyHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Money the association spends — one row per expense, made of line items filed
 * under a category.
 *
 * An expense is logged through the office's two-step wizard: the details
 * (date, supplier, description), then the line items by category. Accounting
 * posts it to the books separately; a posted expense is locked, and deleting
 * it reverses its entry.
 */
class Expense extends Model
{
    protected $fillable = [
        'reference',
        'expense_date',
        'supplier_id',
        'description',
        'subtotal',
        'created_by_admin_id',
        'journal_entry_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'subtotal'     => 'decimal:2',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The lines this expense is made of.
     */
    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * The journal entry accounting posted for this expense.
     */
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * The officer who logged the expense.
     */
    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the reference, the supplier and the description.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('reference', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhereHas('supplier', function ($supplier) use ($like) {
                    $supplier->where('name', 'like', $like);
                })
                ->orWhereHas('items', function ($items) use ($like) {
                    $items->where('description', 'like', $like)
                        ->orWhere('category_name', 'like', $like);
                });
        });
    }

    /**
     * Only the expenses carrying one category on their items.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $categoryId ? $query->whereHas('items', function ($items) use ($categoryId) {
            $items->where('expense_category_id', $categoryId);
        }) : $query;
    }

    /**
     * The expenses recorded in a month, "Y-m".
     */
    public function scopeForMonth($query, ?string $month)
    {
        if (! $month) {
            return $query;
        }

        return $query->whereYear('expense_date', substr($month, 0, 4))
            ->whereMonth('expense_date', substr($month, 5, 2));
    }

    /**
     * Newest first — the ordering the expenses page opens on.
     */
    public function scopeOrdered($query)
    {
        return $query->orderByDesc('expense_date')->orderByDesc('id');
    }

    // =====================================================================
    // Money
    // =====================================================================

    /**
     * Refresh the expense's subtotal from its own lines.
     */
    public function recalculate(): void
    {
        $this->subtotal = round((float) $this->items()->sum('amount'), 2);
        $this->save();
    }

    // =====================================================================
    // Display
    // =====================================================================

    /**
     * The subtotal as a list writes it: "₱1,500.00".
     */
    public function getSubtotalDisplayAttribute(): string
    {
        return MoneyHelper::format($this->subtotal);
    }

    /**
     * The date the expense was recorded, as a list writes it: "Jun 8, 2026".
     */
    public function getExpenseDateDisplayAttribute(): string
    {
        return $this->expense_date ? $this->expense_date->format('M j, Y') : 'Not provided';
    }

    /**
     * What the expense was for, as a list writes it: its own note, else the
     * first line's.
     */
    public function getDescriptionLabelAttribute(): string
    {
        return $this->description
            ?: ($this->items->first()->description ?? '')
            ?: 'None';
    }

    /**
     * The categories the expense's lines are filed under.
     */
    public function getCategoryLabelAttribute(): string
    {
        return $this->items->pluck('category_name')->filter()->unique()->implode(', ') ?: 'None';
    }

    /**
     * The supplier as a record writes it.
     */
    public function getSupplierLabelAttribute(): string
    {
        return $this->supplier ? $this->supplier->name : 'None';
    }

    /**
     * Whether the expense may still be corrected — not once it is posted.
     */
    public function canBeEdited(): bool
    {
        return $this->journal_entry_id === null;
    }

}
