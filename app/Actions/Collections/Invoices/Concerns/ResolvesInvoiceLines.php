<?php

namespace App\Actions\Collections\Invoices\Concerns;

use App\Models\Product\Product;

trait ResolvesInvoiceLines
{
    // ===== Lines =====

    private function resolveLines(array $items): array
    {
        $products = Product::query()
            ->whereIn('id', array_filter(array_column($items, 'product_id')))
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach ($items as $item) {
            $product = $products->get($item['product_id'] ?? null);
            $name = trim((string) ($item['name'] ?? ($product ? $product->name : '')));

            if ($name === '') {
                continue;
            }

            $quantity = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $unitPrice = $unitPrice > 0 || ! $product ? $unitPrice : (float) $product->price;

            $lines[] = [
                'product_id'  => $product ? $product->id : null,
                'name'        => $name,
                'description' => $item['description'] ?? ($product ? $product->description : null),
                'quantity'    => $quantity,
                'unit_price'  => $unitPrice,
                'amount'      => round($quantity * $unitPrice, 2),
            ];
        }

        return $lines;
    }
}
