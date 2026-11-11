<?php

namespace Tests\Feature\Queries;

use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseItem;
use App\Models\Supplier;
use App\Queries\Expenses\ExpenseCategoryListQuery;
use App\Queries\Expenses\ExpenseDetailQuery;
use App\Queries\Expenses\ExpenseListQuery;
use App\Queries\Expenses\ExpenseOptionQuery;
use App\Queries\Expenses\SupplierListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpensesQueryTest extends TestCase
{
    use RefreshDatabase;

    // ===== Expenses =====

    public function test_the_expense_list_filters_searches_and_sorts(): void
    {
        $security = $this->category('Security');
        $utilities = $this->category('Utilities');

        $this->expense('EXP-2026-0001', $security, 500, 'draft', '2026-09-10', 'Gate guard');
        $this->expense('EXP-2026-0002', $utilities, 900, 'approved', '2026-10-10', 'Meralco');

        $query = app(ExpenseListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate(['search' => 'Meralco'])->total());
        $this->assertSame(1, $query->paginate(['search' => 'EXP-2026-0001'])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['status' => 'approved']])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['category' => $utilities->id]])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['month' => '2026-09']])->total());

        $ascending = $query->paginate(['sort' => 'subtotal', 'direction' => 'asc']);

        $this->assertSame(500.0, (float) $ascending->items()[0]->subtotal);
        $this->assertCount(2, $query->ids(['filters' => ['status' => '']]));
    }

    public function test_the_expense_summary_reads_what_was_spent_this_month_and_what_is_open(): void
    {
        $category = $this->category('Security');

        $this->expense('EXP-2026-0001', $category, 500, 'draft', now()->toDateString());
        $this->expense('EXP-2026-0002', $category, 250, 'approved', now()->toDateString());
        $this->expense('EXP-2026-0003', $category, 900, 'draft', '2025-01-10');

        $totals = app(ExpenseListQuery::class)->summary();

        $this->assertSame(250.0, $totals['month_total']);
        $this->assertSame(1400.0, $totals['awaiting_total']);
        $this->assertSame(250.0, $totals['approved_total']);
        $this->assertSame(3, $totals['total_count']);
    }

    public function test_the_expense_detail_splits_its_lines_by_category(): void
    {
        $security = $this->category('Security');
        $utilities = $this->category('Utilities');

        $expense = $this->expense('EXP-2026-0001', $security, 300, 'draft', '2026-09-10');
        $expense->items()->create([
            'expense_category_id' => $utilities->id,
            'category_name'       => 'Utilities',
            'description'         => 'Water',
            'amount'              => 100,
        ]);

        $expense->recalculate();

        $detail = app(ExpenseDetailQuery::class);
        $record = $detail->forView($expense->id);

        $this->assertCount(2, $record->items);

        $breakdown = $detail->categoryBreakdown($record);

        $this->assertSame(['Security', 'Utilities'], array_column($breakdown, 'category'));
        $this->assertSame([75.0, 25.0], array_column($breakdown, 'percent'));
        $this->assertSame('₱300.00', $breakdown[0]['display']);
    }

    // ===== Categories =====

    public function test_the_category_list_searches_and_orders_by_name(): void
    {
        $this->category('Utilities');
        $this->category('Security');

        $query = app(ExpenseCategoryListQuery::class);

        $this->assertSame(['Security', 'Utilities'], $query->paginate()->getCollection()->pluck('name')->all());
        $this->assertSame(1, $query->paginate(['search' => 'Sec'])->total());
        $this->assertCount(2, $query->ids());
    }

    // ===== Suppliers =====

    public function test_the_supplier_picker_lists_every_supplier_by_name(): void
    {
        $acme = Supplier::create(['name' => 'Acme Security']);
        $vendor = Supplier::create(['name' => 'Retired Vendor']);

        $this->assertSame(
            [$acme->id, $vendor->id],
            app(ExpenseOptionQuery::class)->suppliers()->pluck('id')->all()
        );
        $this->assertSame(1, app(SupplierListQuery::class)->paginate(['search' => 'Acme'])->total());
    }

    // ===== Helpers =====

    private function expense(
        string $reference,
        ExpenseCategory $category,
        float $amount,
        string $status,
        string $date,
        string $description = 'Association spending'
    ): Expense {
        $expense = Expense::create([
            'reference'    => $reference,
            'expense_date' => $date,
            'description'  => $description,
            'subtotal'     => $amount,
            'status'       => $status,
        ]);

        ExpenseItem::create([
            'expense_id'          => $expense->id,
            'expense_category_id' => $category->id,
            'category_name'       => $category->name,
            'description'         => $description,
            'amount'              => $amount,
        ]);

        return $expense->fresh(['items']);
    }

    private function category(string $name): ExpenseCategory
    {
        return ExpenseCategory::create(['name' => $name]);
    }
}
