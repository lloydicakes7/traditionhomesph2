<?php

namespace App\Queries\Reports;

use App\Enums\AgingBucket;
use App\Enums\BillStatus;
use App\Models\Bill\Bill;
use Carbon\Carbon;

/**
 * What is owed to the association — aged.
 *
 * The report reads both kinds of bill, because a homeowner with dues in arrears
 * and a homeowner with an unpaid clearance are the same question to the office:
 * what is outstanding, and how long has it been outstanding. Every bill is aged
 * from the day it fell due — the day the association could first have collected
 * it — and filed in the bucket it has sat in since.
 *
 * The buckets themselves are the AgingBucket enum's: the report reads the set,
 * never a list of its own.
 */
class AgingReportQuery
{
    /**
     * An empty tally for every bucket — what each report starts from.
     *
     * @return array<string, float>
     */
    private function emptyTotals(): array
    {
        return array_fill_keys(AgingBucket::values(), 0.0);
    }

    // =====================================================================
    // Receivables — what the association is owed
    // =====================================================================

    /**
     * Every homeowner's uncollected balance, bucketed by how long it has run.
     *
     * A unit's row is its unpaid bills together: how many there are, how long
     * the oldest has gone, and how much of the money has sat in each bucket.
     *
     * @param  string|null  $asOf
     * @param  array<string, mixed>  $filters  search, block, lot, include_dues, include_invoices
     * @return array<string, mixed>
     */
    public function receivable(?string $asOf, array $filters = []): array
    {
        $asOf = $asOf ?: now()->toDateString();
        $totals = $this->emptyTotals();

        $rows = [];

        foreach ($this->openBills($asOf, $filters) as $bill) {
            $key = $bill['property_id'];

            if (! isset($rows[$key])) {
                $rows[$key] = [
                    'property_id' => $bill['property_id'],
                    'property'    => $bill['property'],
                    'homeowner'   => $bill['homeowner'],
                    'buckets'     => $this->emptyTotals(),
                    'total'       => 0.0,
                    'bills'       => 0,
                    'oldest_days' => 0,
                    'oldest_bucket' => AgingBucket::NOT_DUE(),
                ];
            }

            $bucket = $this->bucketFor($bill['due_date'], $asOf);
            $days = $this->daysOutstanding($bill['due_date'], $asOf);

            $rows[$key]['buckets'][$bucket->value] = round($rows[$key]['buckets'][$bucket->value] + $bill['balance'], 2);
            $rows[$key]['total'] = round($rows[$key]['total'] + $bill['balance'], 2);
            $rows[$key]['bills']++;

            // The unit's oldest bill is the worst bucket it has sat in — the
            // one the office chases first.
            if ($days >= $rows[$key]['oldest_days']) {
                $rows[$key]['oldest_days'] = $days;
                $rows[$key]['oldest_bucket'] = $bucket;
            }

            $totals[$bucket->value] = round($totals[$bucket->value] + $bill['balance'], 2);
        }

        return [
            'as_of'    => $asOf,
            'buckets'  => AgingBucket::cases(),
            'rows'     => array_values($rows),
            'totals'   => $totals,
            'total'    => round(array_sum($totals), 2),
            'count'    => count($rows),
        ];
    }

    /**
     * The bills still outstanding on a date, whichever kind they are, with the
     * property and homeowner they belong to.
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Support\Collection<array<string, mixed>>
     */
    private function openBills(string $asOf, array $filters)
    {
        $includeDues = ($filters['include_dues'] ?? true) !== false;
        $includeCharges = ($filters['include_invoices'] ?? true) !== false;

        if (! $includeDues && ! $includeCharges) {
            return collect();
        }

        $query = Bill::query()
            ->where('balance', '>', 0)
            ->where('status', '!=', BillStatus::CANCELLED)
            ->with(['user.household.property', 'user.resident']);

        // One table, three kinds of bill: the two toggles pick which kinds this
        // report reads, and an amenity charge is a one-off charge like any
        // other, so it rides with the invoices.
        if ($includeDues !== $includeCharges) {
            $includeDues ? $query->monthlyDues() : $query->charges();
        }

        $bills = $query->get()->map(function (Bill $bill) {
            return [
                'property_id' => $bill->property?->id ?? 0,
                'property'    => $bill->property,
                'homeowner'   => $bill->issued_to_name,
                'due_date'    => $bill->due_date
                    ? $bill->due_date->toDateString()
                    : ($bill->bill_date ? $bill->bill_date->toDateString() : null),
                'balance'     => (float) $bill->balance,
            ];
        });

        return $bills->filter(function (array $bill) use ($filters) {
            $property = $bill['property'];

            if (($filters['block'] ?? null) && (int) $property?->block !== (int) $filters['block']) {
                return false;
            }

            if (($filters['lot'] ?? null) && (int) $property?->lot !== (int) $filters['lot']) {
                return false;
            }

            $term = trim((string) ($filters['search'] ?? ''));

            if ($term !== '') {
                $haystack = strtolower(
                    ($property?->block_lot_label ?: '') . ' ' . $bill['homeowner']
                );

                if (strpos($haystack, strtolower($term)) === false) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * The bucket a date falls in, counted from the day it fell due. A date not
     * yet reached is not yet due, which is a bucket of its own.
     */
    public function bucketFor(?string $dueDate, string $asOf): AgingBucket
    {
        return AgingBucket::forDays($this->daysOutstanding($dueDate, $asOf));
    }

    /**
     * How long a bill has gone unpaid on a date — its age, in whole days. A
     * bill that is not yet due has not aged, so it reads zero.
     */
    public function daysOutstanding(?string $dueDate, string $asOf): int
    {
        if (! $dueDate) {
            return 0;
        }

        $days = Carbon::parse($dueDate)->startOfDay()
            ->diffInDays(Carbon::parse($asOf)->startOfDay(), false);

        return max((int) $days, 0);
    }
}
