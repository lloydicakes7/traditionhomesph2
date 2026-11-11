<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * The Reports pages — everything the association's records roll up into, from
 * the money movement reports out to the community data beyond finance.
 *
 * Thin by design, like the rest of the panel: each action authorizes the report
 * and returns its page. A report is a view of other records rather than a
 * record of its own, so the Reports suite's own gate authorizes the page (see
 * \App\Policies\ReportPolicy) and the figures belong to the Livewire component
 * the page embeds.
 */
class ReportController extends Controller
{
    /**
     * Who may read the reports.
     */
    const GATE = 'report.viewAny';

    /**
     * The AR Aging Report — what homeowners owe, by how overdue it is.
     *
     * @return \Illuminate\View\View
     */
    public function arAging()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.ar-aging');
    }

    /**
     * The Monthly Income Report — the year's income by month and account.
     *
     * @return \Illuminate\View\View
     */
    public function monthlyIncome()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.monthly-income');
    }

    /**
     * The Monthly Expense Report — the year's spending, on the same basis.
     *
     * @return \Illuminate\View\View
     */
    public function monthlyExpenses()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.monthly-expenses');
    }

    /**
     * The Daily Collection Report — the day's collection register.
     *
     * @return \Illuminate\View\View
     */
    public function dailyCollection()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.daily-collection');
    }

    /**
     * The Daily Income Report — what a day earned, by income account.
     *
     * @return \Illuminate\View\View
     */
    public function dailyIncome()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.daily-income');
    }

    /**
     * The Daily Expenses Report — what a day spent, by expense account.
     *
     * @return \Illuminate\View\View
     */
    public function dailyExpenses()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.daily-expenses');
    }

    /**
     * The Profit and Loss Statement — what the period earned and spent.
     *
     * @return \Illuminate\View\View
     */
    public function profitAndLoss()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.profit-and-loss');
    }

    /**
     * The Statement of Account — one unit's account for one period.
     *
     * @return \Illuminate\View\View
     */
    public function statementOfAccount()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.statement-of-account');
    }

    /**
     * The Cash Flow Statement — what the money accounts took in and paid out.
     *
     * @return \Illuminate\View\View
     */
    public function cashFlow()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.cash-flow');
    }

    /**
     * The Community Reports page — the community data beyond the books.
     *
     * @return \Illuminate\View\View
     */
    public function community()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.community');
    }

    /**
     * The Residents Report — the community read as a population.
     *
     * @return \Illuminate\View\View
     */
    public function residents()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.residents');
    }

    /**
     * The Amenities Report — what the shared facilities are booking.
     *
     * @return \Illuminate\View\View
     */
    public function amenities()
    {
        $this->authorize(self::GATE);

        return view('admin.reports.amenities');
    }
}
