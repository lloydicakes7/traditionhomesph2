<?php

namespace App\Models\Concerns;

/**
 * The reference number a transaction slip is filed under: `REQ-2026-000045`
 * for a request, `BKG-2026-000012` for a booking.
 *
 * The shape is a bill's own numbering (see \App\Models\Bill\Bill::nextReference),
 * so the association numbers its records one way: a prefix, the year, then a
 * six-digit sequence read off the last row. The number is written once, when
 * the record is created (see \App\Observers\SlipReferenceObserver), and is what
 * the homeowner reads on the slip and the office searches the queue by.
 */
trait HasSlipReference
{
    // ===== References =====

    /**
     * The next reference for this record, continuing the year's own sequence.
     */
    public static function nextReference(): string
    {
        $prefix = static::referencePrefix() . '-' . now()->year . '-';

        $last = static::query()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
