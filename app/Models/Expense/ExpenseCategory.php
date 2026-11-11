<?php

namespace App\Models\Expense;

use App\Models\Account\Account;
use Illuminate\Database\Eloquent\Model;

/**
 * A group the association files its spending under — security, utilities,
 * repairs and maintenance, supplies.
 *
 * Categories are the office's own labels: a name, an optional note, and the
 * account its spending is charged to. What makes it a table rather than an enum
 * is exactly that — the office adds and renames its own as it goes. Lines
 * snapshot the name, so removing one never rewrites an expense already recorded.
 */
class ExpenseCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'account_id',
    ];

    // =====================================================================
    // Relationships
    // =====================================================================

    /**
     * The expense lines filed under this category.
     */
    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }

    /**
     * The account spending filed here is charged to.
     *
     * Posting an expense debits this account (see
     * \App\Models\Account\Account::forExpenseCategory()), which
     * is what files the spend under the right heading in the books —
     * security under Security Services, utilities under Utilities.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // =====================================================================
    // Scopes
    // =====================================================================

    /**
     * Free-text search across the category's name and note.
     */
    public function scopeSearch($query, ?string $term)
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function ($query) use ($like) {
            $query->where('name', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    /**
     * Order categories by name — the ordering every list uses.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
