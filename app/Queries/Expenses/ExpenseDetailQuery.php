<?php

namespace App\Queries\Expenses;

use App\Models\Expense\Expense;
use App\Utility\MoneyHelper;

class ExpenseDetailQuery
{
    // ===== Record =====

    public function forView(int $id): Expense
    {
        return Expense::with([
            'items.category',
            'createdByAdmin',
            'journalEntry.lines',
        ])->findOrFail($id);
    }

    public function forEdit(int $id): Expense
    {
        return Expense::with('items')->findOrFail($id);
    }

    public function forPosting(int $id): Expense
    {
        return Expense::with(['items.category', 'journalEntry'])->findOrFail($id);
    }

    // ===== Lines =====

    public function categoryBreakdown(Expense $expense): array
    {
        $total = (float) $expense->subtotal;

        $groups = [];

        foreach ($expense->items as $item) {
            $label = $item->category_label;

            if (! isset($groups[$label])) {
                $groups[$label] = ['category' => $label, 'amount' => 0.0];
            }

            $groups[$label]['amount'] += (float) $item->amount;
        }

        return array_map(function (array $group) use ($total) {
            $group['amount'] = round($group['amount'], 2);
            $group['display'] = MoneyHelper::format($group['amount']);
            $group['percent'] = $total > 0 ? round($group['amount'] / $total * 100) : 0;

            return $group;
        }, array_values($groups));
    }
}
