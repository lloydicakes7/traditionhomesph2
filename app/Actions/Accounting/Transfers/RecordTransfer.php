<?php

namespace App\Actions\Accounting\Transfers;

use App\Enums\JournalClassification;
use App\Enums\JournalSource;
use App\Models\Account\AccountTransfer;
use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;

class RecordTransfer
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): AccountTransfer
    {
        $transfer = DB::transaction(function () use ($data, $by) {
            $amount = bcadd((string) $data['amount'], '0', 2);

            $transfer = AccountTransfer::create([
                'reference'           => $this->nextReference(),
                'transfer_date'       => $data['transfer_date'],
                'from_account_id'     => (int) $data['from_account_id'],
                'to_account_id'       => (int) $data['to_account_id'],
                'amount'              => $amount,
                'memo'                => trim((string) $data['memo']),
                'reference_no'        => filled($data['reference_no'] ?? null) ? trim((string) $data['reference_no']) : null,
                'created_by_admin_id' => $by->id,
            ]);

            $entry = JournalEntry::post([
                'entry_date'     => $transfer->transfer_date,
                'classification' => JournalClassification::STANDARD(),
                'source_type'    => JournalSource::TRANSFER,
                'source_id'      => $transfer->id,
                'reference_no'   => $transfer->reference,
                'memo'           => 'Transfer ' . $transfer->reference . ' · '
                    . $transfer->from_label . ' → ' . $transfer->to_label,
                'lines'          => [
                    [
                        'account_id' => $transfer->to_account_id,
                        'side'       => JournalLine::SIDE_DEBIT,
                        'amount'     => $amount,
                        'memo'       => 'Money in',
                    ],
                    [
                        'account_id' => $transfer->from_account_id,
                        'side'       => JournalLine::SIDE_CREDIT,
                        'amount'     => $amount,
                        'memo'       => 'Money out',
                    ],
                ],
            ], $by->id);

            $transfer->update(['journal_entry_id' => $entry->id]);

            return $transfer;
        });

        $this->activity->log(
            $by,
            'created',
            'Transfers',
            'Recorded transfer ' . $transfer->reference . ' — ' . $transfer->from_label . ' to ' . $transfer->to_label,
            ['account_transfer_id' => $transfer->id]
        );

        return $transfer->fresh(['fromAccount', 'toAccount', 'journalEntry']);
    }

    // ===== Helpers =====

    private function nextReference(): string
    {
        $prefix = 'TRF-' . now()->year . '-';

        $last = AccountTransfer::query()
            ->where('reference', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
