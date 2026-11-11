<?php

namespace Tests\Feature;

use App\Actions\Collections\Payments\RecordPayment;
use App\Actions\Expenses\Expenses\ApproveExpense;
use App\Actions\Expenses\Expenses\LogExpense;
use App\Enums\JournalSource;
use App\Http\Livewire\Admin\Accounting\JournalEntries\Modals\PostToJournalModal;
use App\Http\Livewire\Admin\Collections\Payments\PaymentTableList;
use App\Http\Livewire\Admin\Expenses\ExpenseTableList;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Expense\Expense;
use App\Models\Expense\ExpenseCategory;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class PostToJournalTest extends TestCase
{
    use RefreshDatabase;

    // ===== Sources =====

    public function test_the_journal_lists_only_the_five_supported_sources(): void
    {
        $this->assertSame(
            ['manual', 'payment', 'expense', 'transfer', 'fund_addition'],
            array_keys(JournalSource::labels())
        );

        $this->assertSame('Cash Funding', JournalSource::labels()[JournalSource::FUND_ADDITION]);
    }

    // ===== Payments =====

    public function test_the_payment_list_opens_the_accounting_posting_modal(): void
    {
        $this->signIn();

        $payment = $this->recordedPayment();

        Livewire::test(PaymentTableList::class)
            ->call('postToJournal', $payment->id)
            ->assertEmitted('openPostToJournal', ['source' => 'payment', 'id' => $payment->id])
            ->assertDispatchedBrowserEvent('modal-open');
    }

    public function test_accounting_posts_a_recorded_payment_from_the_modal(): void
    {
        $this->signIn();

        $payment = $this->recordedPayment();
        $cash = Account::cashOnHand();

        Livewire::test(PostToJournalModal::class)
            ->emit('openPostToJournal', ['source' => 'payment', 'id' => $payment->id])
            ->assertSet('loaded', true)
            ->assertSet('source', 'payment')
            ->assertSet('sourceId', $payment->id)
            ->set('account_id', (string) $cash->id)
            ->call('confirm')
            ->assertHasNoErrors()
            ->assertEmitted('journal-entry-posted');

        $payment->refresh();

        $this->assertNotNull($payment->journal_entry_id);
        $this->assertSame(JournalSource::PAYMENT, $payment->journalEntry->source_type);
        $this->assertSame($payment->id, (int) $payment->journalEntry->source_id);
    }

    // ===== Expenses =====

    public function test_the_expense_list_opens_the_accounting_posting_modal(): void
    {
        $this->signIn();

        $expense = $this->approvedExpense();

        Livewire::test(ExpenseTableList::class)
            ->call('postToJournal', $expense->id)
            ->assertEmitted('openPostToJournal', ['source' => 'expense', 'id' => $expense->id])
            ->assertDispatchedBrowserEvent('modal-open');
    }

    public function test_accounting_posts_an_approved_expense_from_the_modal(): void
    {
        $this->signIn();

        $expense = $this->approvedExpense();
        $cash = Account::cashOnHand();

        Livewire::test(PostToJournalModal::class)
            ->emit('openPostToJournal', ['source' => 'expense', 'id' => $expense->id])
            ->assertSet('loaded', true)
            ->assertSet('source', 'expense')
            ->assertSet('sourceId', $expense->id)
            ->set('account_id', (string) $cash->id)
            ->call('confirm')
            ->assertHasNoErrors()
            ->assertEmitted('journal-entry-posted');

        $expense->refresh();

        $this->assertNotNull($expense->journal_entry_id);
        $this->assertSame(JournalSource::EXPENSE, $expense->journalEntry->source_type);
        $this->assertSame($expense->id, (int) $expense->journalEntry->source_id);
    }

    // ===== Pages =====

    public function test_the_recording_pages_no_longer_ask_for_an_account(): void
    {
        $this->signIn();

        $this->get(route('collections.payments'))
            ->assertOk()
            ->assertSee('Post to Journal')
            ->assertDontSee('Received Into');

        $this->get(route('expenses.index'))
            ->assertOk()
            ->assertSee('Post to Journal')
            ->assertDontSee('Funded From');
    }

    // ===== Schema =====

    public function test_expenses_no_longer_store_a_funding_account(): void
    {
        $this->assertFalse(Schema::hasColumn('expenses', 'account_id'));
    }

    // ===== Helpers =====

    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Accountant',
            'email'               => 'posting-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function recordedPayment(): Payment
    {
        $user = factory(User::class)->create();

        $bill = Bill::create([
            'reference'      => 'INV-' . uniqid(),
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 500,
            'total'          => 500,
            'paid_amount'    => 0,
            'balance'        => 500,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Clearance',
            'quantity'   => 1,
            'unit_price' => 500,
            'amount'     => 500,
        ]);

        return app(RecordPayment::class)->handle($bill, [
            'amount'  => 500,
            'paid_at' => now(),
        ], auth('web')->user());
    }

    private function approvedExpense(): Expense
    {
        $category = ExpenseCategory::create(['name' => 'Security']);

        $expense = app(LogExpense::class)->handle([
            'expense_date' => now()->toDateString(),
            'description'  => 'Gate guard wages',
            'items'        => [[
                'expense_category_id' => $category->id,
                'description'         => 'Gate guard',
                'amount'              => 350,
            ]],
        ], auth('web')->user());

        return app(ApproveExpense::class)->handle($expense, auth('web')->user());
    }
}
