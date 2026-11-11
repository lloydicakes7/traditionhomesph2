<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense\Expense;
use App\Models\Supplier;
use App\Models\Expense\ExpenseCategory;

/**
 * The Expenses pages — everything the association spends.
 *
 * Thin by design: each action authorizes the page and returns its view. The
 * data, the filters and the actions belong to the Livewire components the views
 * embed, and the rules behind them to the module's services.
 */
class ExpenseController extends Controller
{
    /**
     * Show the Expenses page — the complete history of spending, with the
     * Record Expense wizard and each expense's tracking view behind its rows.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('viewAny', Expense::class);

        return view('admin.expenses.index');
    }

    /**
     * Show the Expense Categories page — the groups spending is filed under.
     *
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        $this->authorize('viewAny', ExpenseCategory::class);

        return view('admin.expenses.categories');
    }

    /**
     * Show the Expense Suppliers page — the groups of agency to payed for.
     *
     * @return \Illuminate\View\View
     */
    public function suppliers()
    {
        $this->authorize('viewAny', Supplier::class);

        return view('admin.expenses.suppliers');
    }
}
