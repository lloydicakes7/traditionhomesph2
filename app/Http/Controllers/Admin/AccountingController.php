<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PrintDocument;
use App\Http\Controllers\Controller;
use App\Models\Account\Account;
use App\Models\Account\AccountingPeriod;
use App\Models\Account\AccountTransfer;
use App\Models\JournalEntry\JournalEntry;
use App\Queries\Settings\PrintSettingsQuery;
use App\Services\PrintDocuments\PrintDocumentService;
use App\Utility\PdfBuilder;

/**
 * The Accounting pages — the chart of accounts, the books every module posts
 * into, the cash and bank accounts those books keep money in (managed on the
 * chart itself), the transfers written against them, the register they are
 * read back through, and the month-end lock that closes a period.
 *
 * Thin by design: each action authorizes the page and returns its view. The
 * data, the filters and the actions belong to the Livewire components the views
 * embed, and the rules behind them to the module's services.
 */
class AccountingController extends Controller
{
    /**
     * Show the Chart of Accounts page — the association's full double-entry
     * structure, with each account's balance read from its postings.
     *
     * @return \Illuminate\View\View
     */
    public function chartOfAccounts()
    {
        $this->authorize('viewAny', Account::class);

        return view('admin.accounting.chart-of-accounts');
    }

    /**
     * Show the Journal Entries page — every posting in the books, what the
     * modules wrote and what the treasurer wrote by hand.
     *
     * @return \Illuminate\View\View
     */
    public function journalEntries()
    {
        $this->authorize('viewAny', JournalEntry::class);

        return view('admin.accounting.journal-entries');
    }

    /**
     * Print one entry's voucher.
     *
     * Livewire cannot stream a download, so the journal's view hands off to
     * this route — which builds the document the same way the Print Documents
     * page does, through the document's own builder, so the paper is identical
     * whichever door it was asked for from.
     *
     * @param  int  $id
     * @param  \App\Services\PrintDocuments\PrintDocumentService  $printDocuments
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function voucher($id, PrintDocumentService $printDocuments)
    {
        $this->authorize('viewAny', JournalEntry::class);

        $document = PrintDocument::JOURNAL_VOUCHER();
        $entry = JournalEntry::findOrFail((int) $id);

        $data = $printDocuments->build($document, ['journalEntryId' => $id], app(PrintSettingsQuery::class)->letterhead());

        return PdfBuilder::download(
            $document->view(),
            $data,
            PdfBuilder::filename('Journal-Voucher-' . $entry->entry_no),
            $data['letterhead']['paper'] ?? $document->paper(),
            $printDocuments->orientation($document)
        );
    }

    /**
     * Show the Transfers page — the money moved between the association's own
     * accounts.
     *
     * @return \Illuminate\View\View
     */
    public function transfers()
    {
        $this->authorize('viewAny', AccountTransfer::class);

        return view('admin.accounting.transfers');
    }

    /**
     * Show the Cash Funding page — cash the association already held
     * outside the books, added to one of its own accounts.
     *
     * @return \Illuminate\View\View
     */
    public function cashFunding()
    {
        $this->authorize('viewAny', JournalEntry::class);

        return view('admin.accounting.cash-funding');
    }

    /**
     * Show the Transactions page — the books read a line at a time, for
     * auditing and searching.
     *
     * @return \Illuminate\View\View
     */
    public function transactions()
    {
        $this->authorize('transaction.viewAny');

        return view('admin.accounting.transactions');
    }

    /**
     * Show the Period Close page — the month-end and year-end lock.
     *
     * @return \Illuminate\View\View
     */
    public function periodClose()
    {
        $this->authorize('viewAny', AccountingPeriod::class);

        return view('admin.accounting.period-close');
    }
}
