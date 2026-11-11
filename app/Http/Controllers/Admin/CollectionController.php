<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Payment;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\SoaEmail;

/**
 * The Collections pages — the association's billing and receipts.
 *
 * Thin by design: each action authorizes the page and returns its view. The
 * data, the filters and the actions belong to the Livewire components the views
 * embed, and the rules behind them to the module's services.
 */
class CollectionController extends Controller
{
    /**
     * Show the Billing & Invoices page — one-off charges, monthly dues excluded.
     *
     * @return \Illuminate\View\View
     */
    public function invoices()
    {
        $this->authorize('viewAnyInvoices', Bill::class);

        return view('admin.collections.invoices');
    }

    /**
     * Show the Monthly Dues Invoice page — the recurring dues, reviewed.
     *
     * @return \Illuminate\View\View
     */
    public function monthlyDueInvoices()
    {
        $this->authorize('viewAnyMonthlyDues', Bill::class);

        return view('admin.collections.monthly-dues-invoices');
    }

    /**
     * Show the Monthly Dues Table View — one year of dues as a grid.
     *
     * @return \Illuminate\View\View
     */
    public function monthlyDueTable()
    {
        $this->authorize('viewAnyMonthlyDues', Bill::class);

        return view('admin.collections.monthly-dues-table');
    }

    /**
     * Show the Monthly Dues Generator — where a month is billed.
     *
     * @return \Illuminate\View\View
     */
    public function monthlyDuesGenerator()
    {
        $this->authorize('viewAnyMonthlyDues', Bill::class);

        return view('admin.collections.monthly-dues-generator');
    }

    /**
     * Show the Payments page — the money received, one ledger for both bills.
     *
     * @return \Illuminate\View\View
     */
    public function payments()
    {
        $this->authorize('viewAny', Payment::class);

        return view('admin.collections.payments');
    }

    /**
     * Show the Advance Deposits page — credit held ahead of a bill.
     *
     * @return \Illuminate\View\View
     */
    public function advanceDeposits()
    {
        $this->authorize('viewAny', AdvanceDeposit::class);

        return view('admin.collections.advance-deposits');
    }

    /**
     * Show the Products and Services page — what the association bills for.
     *
     * @return \Illuminate\View\View
     */
    public function products()
    {
        $this->authorize('viewAny', Product::class);

        return view('admin.collections.products');
    }

    /**
     * Show the Product Categories page — the groups the price list is filed
     * under.
     *
     * @return \Illuminate\View\View
     */
    public function productCategories()
    {
        $this->authorize('viewAny', ProductCategory::class);

        return view('admin.collections.product-categories');
    }

    /**
     * Show the Email SOA page — statements of account, prepared and mailed.
     *
     * @return \Illuminate\View\View
     */
    public function soa()
    {
        $this->authorize('viewAny', SoaEmail::class);

        return view('admin.collections.soa');
    }
}
