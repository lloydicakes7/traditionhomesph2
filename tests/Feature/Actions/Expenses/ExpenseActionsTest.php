<?php

namespace Tests\Feature\Actions\Expenses;

use App\Actions\Accounting\JournalEntries\PostExpenseJournal;
use App\Actions\Expenses\Expenses\DeleteExpenses;
use App\Actions\Expenses\Expenses\LogExpense;
use App\Actions\Expenses\Expenses\UpdateExpense;
use App\Enums\AccountType;
use App\Enums\JournalSource;
use App\Models\Account\AccountingPeriod;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Models\Supplier;
use App\Services\Accounting\ExpensePostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ExpenseActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Logging =====

    public function test_logging_an_expense_files_it_and_snapshots_its_categories(): void
    {
        $admin = $this->admin();
        $security = $this->category('Security');
        $supplier = Supplier::create(['name' => 'Acme Security']);

        $expense = app(LogExpense::class)->handle($this->payload($security->id, [
            ['description' => 'Gate guard', 'amount' => 500],
            ['description' => 'Night roving', 'amount' => 250],
        ], $supplier->id), $admin);

        $this->assertSame('EXP-' . now()->year . '-0001', $expense->reference);
        $this->assertSame(750.0, (float) $expense->subtotal);
        $this->assertSame(['Security', 'Security'], $expense->items->pluck('category_name')->all());
        $this->assertNull($expense->journal_entry_id);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'expense',
        ]);
    }

    public function test_a_line_without_a_description_or_an_amount_is_left_out(): void
    {
        $admin = $this->admin();
        $category = $this->category('Utilities');

        $expense = app(LogExpense::class)->handle($this->payload($category->id, [
            ['description' => 'Meralco', 'amount' => 1200],
            ['description' => '', 'amount' => 300],
            ['description' => 'Water', 'amount' => 0],
        ]), $admin);

        $this->assertSame(1200.0, (float) $expense->subtotal);
        $this->assertCount(1, $expense->items);
    }

    public function test_correcting_a_logged_expense_replaces_its_lines(): void
    {
        $admin = $this->admin();
        $category = $this->category('Repairs');
        $expense = $this->logged($admin, $category, 'Gate repair', 400);

        $updated = app(UpdateExpense::class)->handle($expense, $this->payload($category->id, [
            ['description' => 'Gate repair — parts', 'amount' => 450],
            ['description' => 'Gate repair — labour', 'amount' => 150],
        ]), $admin);

        $this->assertSame(600.0, (float) $updated->subtotal);
        $this->assertCount(2, $updated->items);
        $this->assertDatabaseMissing('expense_items', ['description' => 'Gate repair']);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'updated',
            'module'   => 'expense',
        ]);
    }

    public function test_a_posted_expense_cannot_be_edited(): void
    {
        $admin = $this->admin();
        $category = $this->category('Security');
        $expense = $this->logged($admin, $category, 'Gate guard', 350);
        $this->postExpense($expense, $this->account(), $admin);

        $this->expectException(ValidationException::class);

        app(UpdateExpense::class)->handle($expense->fresh(), $this->payload($category->id, [
            ['description' => 'Changed', 'amount' => 999],
        ]), $admin);
    }

    // ===== Posting =====

    public function test_accounting_posts_an_expense_to_the_journal(): void
    {
        $admin = $this->admin();
        $expenseAccount = $this->account('5100', 'Security Services', false, AccountType::EXPENSE);
        $category = $this->category('Security', $expenseAccount);
        $expense = $this->logged($admin, $category, 'Gate guard', 350);

        $posted = $this->postExpense($expense->fresh(), $this->account(), $admin);

        $this->assertNotNull($posted->journal_entry_id);
        $this->assertSame(1, JournalEntry::count());

        $entry = JournalEntry::with('lines')->first();

        $this->assertSame(JournalSource::EXPENSE, $entry->source_type);
        $this->assertSame((int) $expense->id, (int) $entry->source_id);
        $this->assertSame(350.0, (float) $entry->lines->where('side', JournalLine::SIDE_DEBIT)->sum('amount'));
        $this->assertSame(350.0, (float) $entry->lines->where('side', JournalLine::SIDE_CREDIT)->sum('amount'));
        $this->assertSame((int) $expenseAccount->id, (int) $entry->lines->firstWhere('side', JournalLine::SIDE_DEBIT)->account_id);
        $this->assertSame((int) $this->account()->id, (int) $entry->lines->firstWhere('side', JournalLine::SIDE_CREDIT)->account_id);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'posted',
            'module'   => 'expense',
        ]);
    }

    public function test_posting_an_expense_requires_a_cash_account(): void
    {
        $admin = $this->admin();
        $expense = $this->logged($admin, $this->category('Security'), 'Gate guard', 350);

        $income = $this->account('4000', 'Income', false, AccountType::INCOME);

        $this->expectException(ValidationException::class);

        try {
            $this->postExpense($expense->fresh(), $income, $admin);
        } finally {
            $this->assertSame(0, JournalEntry::count());
            $this->assertNull($expense->fresh()->journal_entry_id);
        }
    }

    public function test_posting_an_expense_twice_posts_nothing_more(): void
    {
        $admin = $this->admin();
        $expense = $this->logged($admin, $this->category('Security'), 'Gate guard', 350);

        $this->postExpense($expense->fresh(), $this->account(), $admin);
        $this->postExpense($expense->fresh(), $this->account(), $admin);

        $this->assertSame(1, JournalEntry::count());
    }

    public function test_a_closed_month_keeps_the_expense_unposted(): void
    {
        $admin = $this->admin();
        $expense = $this->logged($admin, $this->category('Security'), 'Gate guard', 350);

        AccountingPeriod::create([
            'period' => now()->format('Y-m'),
            'status' => 'closed',
        ]);

        try {
            $this->postExpense($expense->fresh(), $this->account(), $admin);
            $this->fail('Posting into a closed month should be refused.');
        } catch (ValidationException $exception) {
            $this->assertNull($expense->fresh()->journal_entry_id);
            $this->assertSame(0, JournalEntry::count());
        }
    }

    // ===== Deleting =====

    public function test_deleting_an_unposted_expense_posts_nothing(): void
    {
        $admin = $this->admin();
        $expense = $this->logged($admin, $this->category('Security'), 'Gate guard', 350);

        $this->assertSame(1, app(DeleteExpenses::class)->handle([$expense->id], $admin));

        $this->assertNull(Expense::find($expense->id));
        $this->assertSame(0, JournalEntry::count());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'expense',
        ]);
    }

    public function test_deleting_a_posted_expense_posts_its_reversal_then_removes_it(): void
    {
        $admin = $this->admin();
        $expense = $this->logged($admin, $this->category('Security'), 'Gate guard', 350);

        $this->postExpense($expense->fresh(), $this->account(), $admin);

        $this->assertSame(1, app(DeleteExpenses::class)->handle([$expense->id], $admin));

        $this->assertNull(Expense::find($expense->id));
        $this->assertSame(2, JournalEntry::count());

        $reversal = JournalEntry::with('lines')->orderByDesc('id')->first();

        $this->assertSame(JournalSource::EXPENSE, $reversal->source_type);
        $this->assertSame((int) $expense->id, (int) $reversal->source_id);
        $this->assertSame(350.0, (float) $reversal->lines->where('side', JournalLine::SIDE_DEBIT)->sum('amount'));
        $this->assertSame(350.0, (float) $reversal->lines->where('side', JournalLine::SIDE_CREDIT)->sum('amount'));
        $this->assertSame((int) $this->account()->id, (int) $reversal->lines->firstWhere('side', JournalLine::SIDE_DEBIT)->account_id);
    }

    public function test_a_closed_month_refuses_to_delete_a_posted_expense(): void
    {
        $admin = $this->admin();
        $expense = $this->logged($admin, $this->category('Security'), 'Gate guard', 350);

        $this->postExpense($expense->fresh(), $this->account(), $admin);

        AccountingPeriod::create([
            'period' => now()->format('Y-m'),
            'status' => 'closed',
        ]);

        try {
            app(DeleteExpenses::class)->handle([$expense->id], $admin);
            $this->fail('Deleting across a closed month should be refused.');
        } catch (ValidationException $exception) {
            $this->assertNotNull(Expense::find($expense->id));
            $this->assertSame(1, JournalEntry::count());
        }
    }

    // ===== Helpers =====

    private function postExpense(Expense $expense, Account $account, Admin $admin): Expense
    {
        $draft = app(ExpensePostingService::class)->draft($expense, $account);

        return app(PostExpenseJournal::class)->handle($expense, [
            'entry_date'     => $draft['entry_date'],
            'classification' => $draft['classification'],
            'reference_no'   => $draft['reference_no'],
            'memo'           => $draft['memo'],
            'lines'          => $draft['lines'],
        ], $admin);
    }

    private function logged(Admin $admin, ExpenseCategory $category, string $description, float $amount): Expense
    {
        return app(LogExpense::class)->handle($this->payload($category->id, [
            ['description' => $description, 'amount' => $amount],
        ]), $admin);
    }

    private function payload(int $categoryId, array $items, ?int $supplierId = null): array
    {
        return [
            'expense_date' => now()->toDateString(),
            'supplier_id'  => $supplierId,
            'description'  => 'Association spending',
            'items'        => array_map(function (array $item) use ($categoryId) {
                return [
                    'expense_category_id' => $categoryId,
                    'description'         => $item['description'],
                    'amount'              => $item['amount'],
                ];
            }, $items),
        ];
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'expense-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'is_active'           => true,
        ]);
    }

    private function category(string $name, ?Account $account = null): ExpenseCategory
    {
        return ExpenseCategory::create([
            'name'       => $name,
            'account_id' => $account ? $account->id : null,
        ]);
    }

    private function account(
        string $code = '1010',
        string $name = 'Cash in Bank — BPI',
        bool $cash = true,
        string $type = AccountType::ASSET
    ): Account {
        return Account::firstOrCreate(
            ['code' => $code],
            [
                'name'      => $name,
                'type'      => $type,
                'is_cash'   => $cash,
                'is_active' => true,
            ]
        );
    }
}
