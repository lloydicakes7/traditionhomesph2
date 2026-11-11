<?php

namespace App\Actions\Collections\Invoices;

use App\Actions\Collections\Invoices\Concerns\ResolvesInvoiceLines;
use App\Enums\BillStatus;
use App\Enums\BillType;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CreateInvoice
{
    use ResolvesInvoiceLines;

    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): Collection
    {
        $users = $data['scope'] === 'all'
            ? User::query()->get()
            : User::query()->whereKey($data['user_id'])->get();

        $lines = $this->resolveLines($data['items'] ?? []);

        $invoices = DB::transaction(function () use ($users, $lines, $data, $by) {
            $invoices = new Collection();

            foreach ($users as $user) {
                $invoices->push($this->raise($user, $lines, $data, $by->id));
            }

            return $invoices;
        });

        foreach ($invoices as $invoice) {
            $this->log($invoice, $by);
        }

        return $invoices;
    }

    // ===== Helpers =====

    private function raise(User $user, array $lines, array $data, int $adminId): Bill
    {
        $invoice = Bill::create([
            'reference'           => Bill::nextReference(BillType::INVOICE()),
            'user_id'             => $user ? $user->id : null,
            'billed_to_name'      => ($data['billed_to_name'] ?? null) ?: ($user ? $user->name : null),
            'type'                => BillType::INVOICE,
            'bill_date'           => $data['bill_date'],
            'due_date'            => ($data['due_date'] ?? null) ?: null,
            'discount'            => $data['discount'] ?? 0,
            'notes'               => ($data['notes'] ?? null) ?: null,
            'status'              => BillStatus::UNPAID,
            'created_by_admin_id' => $adminId,
        ]);

        $invoice->items()->createMany($lines);

        $invoice->recalculate();

        return $invoice;
    }

    private function log(Bill $invoice, Admin $by): void
    {
        $this->activity->log(
            $by,
            'created',
            'invoice',
            "Raised invoice {$invoice->reference} for {$invoice->issued_to_name} ({$invoice->total_display})",
            ['bill_id' => $invoice->id, 'user_id' => $invoice->user_id]
        );
    }
}
