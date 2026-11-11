<?php

declare(strict_types=1);

namespace App\Services\PrintDocuments;

use App\Enums\PrintDocument;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Queries\Accounting\JournalEntryListQuery;
use App\Utility\PrintDocumentsHelper as Helper;

/**
 * The Journal Voucher — one posting in the association's books, on paper.
 *
 * The voucher is the paper behind a journal entry: what was posted, from which
 * record, by whom, and the two sides that make it a posting rather than a note.
 * It is read through the accounting module's own query, so the voucher and the
 * Journals page can never describe the same entry two ways.
 *
 * An unbalanced entry is called out on the paper rather than quietly printed:
 * the voucher's last line says so, because a voucher that does not balance is
 * not a voucher.
 */
final class JournalVoucherBuilder extends AbstractPrintDocumentBuilder
{
    /** @var JournalEntryListQuery */
    private $journal;

    public function __construct(PrintDocument $document, JournalEntryListQuery $journal)
    {
        parent::__construct($document);

        $this->journal = $journal;
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function compose(array $params): array
    {
        $id = (int) $this->intOrNull($params, 'journalEntryId');

        $entry = $id > 0 ? $this->journal->find($id) : null;

        return ['voucher' => $entry ? $this->voucher($entry) : null];
    }

    /**
     * The entry, and its lines, as the voucher prints them.
     *
     * @return array<string, mixed>
     */
    private function voucher(JournalEntry $entry): array
    {
        $lines = $this->journal->linesFor($entry);

        $debits = '0.00';
        $credits = '0.00';

        $printed = $lines->map(function (JournalLine $line) use (&$debits, &$credits) {
            $amount = (string) $line->amount;
            $debit = (string) $line->side === JournalLine::SIDE_DEBIT;

            if ($debit) {
                $debits = bcadd($debits, $amount, 2);
            } else {
                $credits = bcadd($credits, $amount, 2);
            }

            return [
                'code'    => $line->account ? (string) $line->account->code : '',
                'account' => (string) $line->account_name,
                'memo'    => trim((string) $line->memo) !== '' ? (string) $line->memo : '—',
                'debit'   => $debit ? Helper::money($amount, true) : '—',
                'credit'  => $debit ? '—' : Helper::money($amount, true),
            ];
        })->all();

        return [
            'entryNo'        => (string) $entry->entry_no,
            'date'           => (string) $entry->entry_date_display,
            'classification' => (string) $entry->classification_label,
            'source'         => (string) $entry->source_label,
            'reference'      => trim((string) $entry->reference_no) !== '' ? (string) $entry->reference_no : '—',
            'description'    => trim((string) $entry->memo) !== '' ? (string) $entry->memo : '—',
            'postedBy'       => $entry->postedByAdmin ? (string) $entry->postedByAdmin->name : 'System',
            'lines'          => $printed,
            'totalDebit'     => Helper::money($debits),
            'totalCredit'    => Helper::money($credits),
            'balanced'       => bccomp($debits, $credits, 2) === 0,
        ];
    }
}
