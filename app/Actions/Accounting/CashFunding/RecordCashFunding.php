<?php

namespace App\Actions\Accounting\CashFunding;

use App\Enums\JournalClassification;
use App\Enums\JournalSource;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Services\ActivityLogService;
use Illuminate\Validation\ValidationException;

class RecordCashFunding
{
    private ActivityLogService $activity;

    public function __construct(ActivityLogService $activity)
    {
        $this->activity = $activity;
    }

    // ===== Handle =====

    public function handle(array $data, Admin $by): JournalEntry
    {
        $amount = bcadd((string) $data['amount'], '0', 2);

        $to = Account::query()->cash()->find((int) $data['to_account_id']);

        if (! $to) {
            throw ValidationException::withMessages([
                'to_account_id' => 'Choose a cash or bank account.',
            ]);
        }

        $entry = JournalEntry::post([
            'entry_date'     => $data['entry_date'],
            'classification' => JournalClassification::STANDARD,
            'source_type'    => JournalSource::FUND_ADDITION,
            'source_id'      => null,
            'reference_no'   => filled($data['reference_no'] ?? null) ? trim((string) $data['reference_no']) : null,
            'memo'           => trim((string) $data['memo']),
            'lines'          => [
                [
                    'account_id' => $to->id,
                    'side'       => JournalLine::SIDE_DEBIT,
                    'amount'     => $amount,
                ],
                [
                    'account_name' => JournalEntry::EXTERNAL_ACCOUNT_NAME,
                    'side'         => JournalLine::SIDE_CREDIT,
                    'amount'       => $amount,
                ],
            ],
        ], $by->id);

        $this->activity->log(
            $by,
            'created',
            'Cash Funding',
            'Added cash to ' . $to->label . ' — journal entry ' . $entry->entry_no,
            ['journal_entry_id' => $entry->id]
        );

        return $entry->load(['lines.account', 'postedByAdmin']);
    }
}
