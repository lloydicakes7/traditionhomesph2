<?php

namespace App\Actions\Expenses\Expenses\Concerns;

use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;

trait ExpenseLines
{
    // ===== Lines =====

    private function resolveItems(array $items): array
    {
        $categories = ExpenseCategory::query()
            ->whereIn('id', array_filter(array_column($items, 'expense_category_id')))
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach ($items as $item) {
            $description = trim((string) ($item['description'] ?? ''));
            $amount = round((float) ($item['amount'] ?? 0), 2);

            if ($description === '' || $amount <= 0) {
                continue;
            }

            $category = $categories->get($item['expense_category_id'] ?? null);

            $lines[] = [
                'expense_category_id' => $category ? $category->id : null,
                'category_name'       => $category && $category->name ? $category->name : 'Uncategorised',
                'description'         => $description,
                'amount'              => $amount,
            ];
        }

        return $lines;
    }

    // ===== Reference =====

    private function nextReference(): string
    {
        $prefix = 'EXP-' . now()->year . '-';

        $last = Expense::query()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
