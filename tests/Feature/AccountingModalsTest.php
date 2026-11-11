<?php

namespace Tests\Feature;

use App\Enums\AccountKind;
use App\Enums\AccountType;
use App\Actions\Accounting\CashFunding\RecordCashFunding;
use App\Enums\JournalClassification;
use App\Enums\JournalEntryStatus;
use App\Enums\JournalSource;
use App\Http\Livewire\Admin\Accounting\ChartOfAccounts\Modals\AccountDeleteModal;
use App\Http\Livewire\Admin\Accounting\ChartOfAccounts\Modals\AccountFormModal;
use App\Http\Livewire\Admin\Accounting\CashFunding\Modals\CashFundingFormModal;
use App\Http\Livewire\Admin\Accounting\JournalEntries\Modals\JournalEntryFormModal;
use App\Http\Livewire\Admin\Accounting\JournalEntries\Modals\JournalEntryViewModal;
use App\Http\Livewire\Admin\Accounting\PeriodClose\Modals\PeriodCloseModal;
use App\Http\Livewire\Admin\Accounting\PeriodClose\Modals\PeriodReopenModal;
use App\Http\Livewire\Admin\Accounting\Transfers\Modals\TransferDeleteModal;
use App\Http\Livewire\Admin\Accounting\Transfers\Modals\TransferFormModal;
use App\Models\Account\Account;
use App\Models\Account\AccountTransfer;
use App\Models\Admin;
use App\Models\Expense\Expense;
use App\Models\JournalEntry\JournalEntry;
use App\Models\JournalEntry\JournalLine;
use App\Models\Product\ProductCategory;
use App\Models\Role;
use App\Queries\Accounting\ChartOfAccountsQuery;
use App\Actions\Accounting\Periods\ClosePeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Accounting page modals, now on the ModalComponent + modal-loader contract.
 *
 * Three things are worth pinning down. First, every dialog carries the
 * skeleton/content markers the loader keys on — a dialog without them opens
 * onto an endless spinner because `isLazyModal()` never matches. Second, the
 * load methods the loader fires (`load{Stem}Modal` / `open{Stem}Modal`) leave
 * the dialog in its loaded state with the right record in hand. Third, the
 * confirm dialogs still refuse what they always refused.
 */
class AccountingModalsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The page's modals, one component per action.
     *
     * @return array<int, class-string>
     */
    private function modalClasses(): array
    {
        return [
            AccountFormModal::class,
            AccountDeleteModal::class,
            JournalEntryFormModal::class,
            JournalEntryViewModal::class,
            CashFundingFormModal::class,
            TransferFormModal::class,
            TransferDeleteModal::class,
            PeriodCloseModal::class,
            PeriodReopenModal::class,
        ];
    }

    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'accounting-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    private function account(string $code, string $name, bool $cash = true, string $type = AccountType::ASSET): Account
    {
        return Account::create([
            'code'        => $code,
            'name'        => $name,
            'type'        => $type,
            'description' => '',
            'is_cash'     => $cash,
        ]);
    }

    private function journalEntry(string $entryNo, array $extra = []): JournalEntry
    {
        return JournalEntry::create(array_merge([
            'entry_no'       => $entryNo,
            'entry_date'     => now()->subDay(),
            'classification' => JournalClassification::STANDARD,
            'status'         => JournalEntryStatus::POSTED,
            'memo'           => 'Opening balances',
        ], $extra));
    }

    // =====================================================================
    // The shared contract
    // =====================================================================

    /**
     * The page's HTML holds a trigger for every dialog and the dialog itself.
     *
     * @param  array<int, string>  $modalIds
     */
    private function assertPageRendersModals(string $route, array $modalIds, array $params = []): void
    {
        $response = $this->get(route($route, $params));

        $response->assertOk();

        foreach ($modalIds as $id) {
            // The trigger the officer clicks...
            $response->assertSee('data-target="#' . $id . '"', false);
            // ...and the dialog it opens, already carrying the skeleton the
            // loader swaps for the loaded content.
            $response->assertSee('id="' . $id . '"', false);
        }

        // One skeleton per mounted dialog, not one shared between them.
        $this->assertSame(
            count($modalIds),
            substr_count($response->getContent(), 'data-modal-skeleton class='),
            "$route does not render one skeleton per dialog"
        );
    }

    public function test_every_accounting_page_renders_its_list_triggers_and_its_modals()
    {
        $admin = $this->signIn();

        // Chart of accounts — a row carries Edit and Remove.
        $account = $this->account('1500', 'Operating Account');
        $this->assertPageRendersModals('accounting.chart-of-accounts', [
            'accountFormModal', 'accountDeleteModal',
        ]);

        // Journal entries — a posted entry carries View, Print and Edit.
        $this->journalEntry('GJ-2026-0100');
        $this->assertPageRendersModals('accounting.journal-entries', [
            'journalEntryFormModal', 'journalEntryViewModal',
        ]);

        // Transfers — a transfer with a posting behind it carries View Journal
        // and Remove.
        $posting = $this->journalEntry('GJ-2026-0101');
        AccountTransfer::create([
            'reference'           => 'TRF-2026-0100',
            'transfer_date'       => now()->subDay(),
            'from_account_id'     => $account->id,
            'to_account_id'       => $this->account('1520', 'Savings')->id,
            'amount'              => 500,
            'memo'                => 'Fund savings',
            'reference_no'        => null,
            'journal_entry_id'    => $posting->id,
            'created_by_admin_id' => $admin->id,
        ]);
        $this->assertPageRendersModals('accounting.transfers', [
            'transferFormModal', 'transferDeleteModal', 'journalEntryViewModal',
        ]);

        // Cash funding — the register and its Add Cash dialog.
        app(RecordCashFunding::class)->handle([
            'entry_date'    => now()->toDateString(),
            'to_account_id' => $account->id,
            'amount'        => '250.00',
            'memo'          => 'Association fund injection',
            'reference_no'  => null,
        ], $admin);
        $this->assertPageRendersModals('accounting.cash-funding', [
            'cashFundingFormModal', 'journalEntryViewModal',
        ]);

        // Period close — a closed month carries Reopen, and the rest Close.
        app(ClosePeriod::class)->handle(now()->startOfMonth()->subMonth()->format('Y-m'), $admin);
        $this->assertPageRendersModals('accounting.period-close', ['periodCloseModal', 'periodReopenModal']);

        // Transactions — a movement carries the row's link into the journal.
        JournalLine::create([
            'journal_entry_id' => $posting->id,
            'account_id'       => $account->id,
            'account_name'     => $account->name,
            'side'             => JournalLine::SIDE_DEBIT,
            'amount'           => 500,
            'memo'             => 'Money arrived',
        ]);
        $this->assertPageRendersModals('accounting.transactions', ['journalEntryViewModal']);
    }

    public function test_every_accounting_modal_carries_the_lazy_load_markers()
    {
        foreach ($this->modalClasses() as $class) {
            Livewire::test($class)
                ->assertSet('loaded', false)
                // The loader's `isLazyModal()` keys on the marker attribute
                // itself, so the whole attribute is what has to be there.
                ->assertSee('data-modal-skeleton class=', false)
                ->assertSee('data-modal-content class=', false);
        }
    }

    // =====================================================================
    // Chart of accounts
    // =====================================================================

    public function test_the_account_form_suggests_an_income_code_and_saves_it()
    {
        $this->signIn();

        Livewire::test(AccountFormModal::class)
            ->call('openCreate')
            ->assertSet('loaded', true)
            ->assertSet('isEditing', false)
            ->assertSet('form.kind', AccountKind::INCOME)
            ->assertSet('form.code', '4010')
            ->set('form.name', 'Rental Income')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accounts', [
            'code'    => '4010',
            'name'    => 'Rental Income',
            'type'    => AccountType::INCOME,
            'is_cash' => false,
        ]);
    }

    public function test_the_suggested_code_follows_the_kind_until_the_officer_types_their_own()
    {
        $this->signIn();

        Livewire::test(AccountFormModal::class)
            ->call('openCreate')
            ->assertSet('form.code', '4010')
            ->set('form.kind', AccountKind::EXPENSE)
            ->assertSet('form.code', '5010')
            ->set('form.code', '5555')
            ->set('form.kind', AccountKind::INCOME)
            ->assertSet('form.code', '5555');
    }

    public function test_an_officer_can_use_their_own_unused_code()
    {
        $this->signIn();

        Livewire::test(AccountFormModal::class)
            ->call('openCreate')
            ->set('form.code', '4321')
            ->set('form.name', 'Rental Income')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accounts', ['code' => '4321', 'name' => 'Rental Income']);
    }

    public function test_a_duplicate_or_malformed_code_is_rejected()
    {
        $this->signIn();

        $this->account('4321', 'Existing Account');

        Livewire::test(AccountFormModal::class)
            ->call('openCreate')
            ->set('form.code', '4321')
            ->set('form.name', 'Another Account')
            ->call('save')
            ->assertHasErrors('form.code');

        Livewire::test(AccountFormModal::class)
            ->call('openCreate')
            ->set('form.code', '12')
            ->set('form.name', 'Short Code')
            ->call('save')
            ->assertHasErrors('form.code');

        Livewire::test(AccountFormModal::class)
            ->call('openCreate')
            ->set('form.code', '12AB')
            ->set('form.name', 'Letters')
            ->call('save')
            ->assertHasErrors('form.code');
    }

    public function test_adding_a_money_account_posts_its_starting_balance()
    {
        $this->signIn();

        Livewire::test(AccountFormModal::class)
            ->call('openCreate')
            ->set('form.kind', AccountKind::MONEY)
            ->assertDontSee('Kept As')
            ->set('form.name', 'BPI Checking')
            ->set('form.starting_balance', '1500')
            ->set('form.balance_as_of', '2026-01-15')
            ->call('save')
            ->assertHasNoErrors();

        $account = Account::where('name', 'BPI Checking')->firstOrFail();

        $this->assertSame('1010', $account->code);
        $this->assertTrue($account->is_cash);
        $this->assertFalse(Schema::hasColumn('accounts', 'kept_as'));

        $entry = JournalEntry::where('source_type', JournalSource::MANUAL)
            ->where('source_id', $account->id)
            ->firstOrFail();

        $external = $entry->lines()->whereNull('account_id')->firstOrFail();

        $this->assertSame('1500.00', $entry->lines()->where('account_id', $account->id)->value('amount'));
        $this->assertSame('1500.00', $external->amount);
        $this->assertSame(JournalLine::SIDE_CREDIT, $external->side);
        $this->assertSame(JournalEntry::EXTERNAL_ACCOUNT_NAME, $external->account_name);
        $this->assertDatabaseMissing('accounts', ['system_key' => 'opening_balance']);
    }

    public function test_the_account_form_prefills_an_existing_account_and_saves_the_edit()
    {
        $this->signIn();

        $account = $this->account('1100', 'Operating Account');

        Livewire::test(AccountFormModal::class)
            ->call('openEdit', $account->id)
            ->assertSet('loaded', true)
            ->assertSet('isEditing', true)
            ->assertSet('form.kind', AccountKind::MONEY)
            ->assertSet('form.name', 'Operating Account')
            ->assertSet('form.code', '1100')
            ->set('form.name', 'Operating Checking')
            ->set('form.code', '1110')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accounts', [
            'id'   => $account->id,
            'code' => '1110',
            'name' => 'Operating Checking',
        ]);
    }

    public function test_an_account_with_entries_cannot_change_kind()
    {
        $this->signIn();

        $account = $this->account('1100', 'Operating Account');
        $entry = $this->journalEntry('GJ-2026-0350');

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $account->id,
            'account_name'     => $account->name,
            'side'             => JournalLine::SIDE_DEBIT,
            'amount'           => 100,
        ]);

        Livewire::test(AccountFormModal::class)
            ->call('openEdit', $account->id)
            ->assertSet('kindLocked', true)
            ->set('form.kind', AccountKind::INCOME)
            ->call('save')
            ->assertHasErrors('form.kind');

        $this->assertDatabaseHas('accounts', [
            'id'      => $account->id,
            'type'    => AccountType::ASSET,
            'is_cash' => true,
        ]);
    }

    public function test_a_category_owned_account_is_read_only_in_the_chart_form()
    {
        $this->signIn();

        $account = $this->account('4200', 'Dues Income', false, AccountType::INCOME);

        ProductCategory::create([
            'name'       => 'Monthly Dues',
            'account_id' => $account->id,
        ]);

        Livewire::test(AccountFormModal::class)
            ->call('openEdit', $account->id)
            ->assertSet('managedBy', 'the product category page')
            ->assertSee('managed from', false)
            ->set('form.name', 'Renamed Here')
            ->call('save');

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'name' => 'Dues Income']);
    }

    public function test_the_account_delete_modal_removes_an_account_with_no_books()
    {
        $this->signIn();

        $account = $this->account('1300', 'Closed Account');

        Livewire::test(AccountDeleteModal::class)
            ->call('load', $account->id)
            ->assertSet('loaded', true)
            ->assertSet('deletingAccountLabel', $account->label)
            ->assertSet('deleteBlocker', null)
            ->call('delete');

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    public function test_the_chart_groups_income_expense_and_money_with_this_year_amounts()
    {
        $this->signIn();

        $cash = $this->account('1010', 'Cash on Hand');
        $income = $this->account('4000', 'Monthly Dues', false, AccountType::INCOME);
        $expense = $this->account('5100', 'Others', false, AccountType::EXPENSE);

        $entry = $this->journalEntry('GJ-2026-0400', ['entry_date' => now()->toDateString()]);

        $entry->lines()->createMany([
            ['account_id' => $cash->id, 'account_name' => $cash->name, 'side' => JournalLine::SIDE_DEBIT, 'amount' => 500],
            ['account_id' => $income->id, 'account_name' => $income->name, 'side' => JournalLine::SIDE_CREDIT, 'amount' => 500],
        ]);

        $paid = $this->journalEntry('GJ-2026-0401', ['entry_date' => now()->toDateString()]);

        $paid->lines()->createMany([
            ['account_id' => $expense->id, 'account_name' => $expense->name, 'side' => JournalLine::SIDE_DEBIT, 'amount' => 120],
            ['account_id' => $cash->id, 'account_name' => $cash->name, 'side' => JournalLine::SIDE_CREDIT, 'amount' => 120],
        ]);

        $groups = collect(app(ChartOfAccountsQuery::class)->groups())->keyBy('key');

        $this->assertEquals(500.0, $groups['income']['total']);
        $this->assertEquals(120.0, $groups['expense']['total']);
        $this->assertEquals(380.0, $groups['money']['total']);
        $this->assertFalse($groups->has('system'));
    }

    public function test_the_chart_names_its_groups_plainly_and_lists_cash_on_hand_under_cash()
    {
        $this->signIn();

        Account::cashOnHand();

        $groups = collect(app(ChartOfAccountsQuery::class)->groups());

        $this->assertSame(['Income', 'Expenses', 'Cash'], $groups->pluck('label')->all());

        $cash = $groups->firstWhere('key', 'money');
        $this->assertSame('Cash', $cash['label']);
        $this->assertSame(['Cash on Hand'], collect($cash['rows'])->pluck('account.name')->all());
    }

    public function test_default_accounts_are_found_by_their_stable_key_after_a_code_change()
    {
        $dues = Account::duesIncome();
        $dues->update(['code' => '4500', 'name' => 'Association Dues']);

        $this->assertSame($dues->id, Account::duesIncome()->id);
    }

    public function test_the_account_seeder_creates_only_the_default_cash_account()
    {
        $this->seed(\AccountSeeder::class);

        $this->assertDatabaseHas('accounts', [
            'code'       => '1000',
            'name'       => 'Cash on Hand',
            'system_key' => Account::KEY_CASH_ON_HAND,
            'is_cash'    => true,
        ]);

        $this->assertDatabaseMissing('accounts', ['system_key' => 'opening_balance']);
        $this->assertDatabaseMissing('accounts', ['system_key' => 'funds_added']);
    }

    public function test_the_default_cash_account_can_be_renamed_but_not_removed_or_reclassed()
    {
        $this->signIn();

        $cash = Account::cashOnHand();

        Livewire::test(AccountFormModal::class)
            ->call('openEdit', $cash->id)
            ->assertSet('kindLocked', true)
            ->set('form.name', 'Office Cash')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('accounts', ['id' => $cash->id, 'name' => 'Office Cash']);

        Livewire::test(AccountFormModal::class)
            ->call('openEdit', $cash->id)
            ->set('form.kind', AccountKind::INCOME)
            ->call('save')
            ->assertHasErrors('form.kind');

        $this->assertDatabaseHas('accounts', [
            'id'      => $cash->id,
            'type'    => AccountType::ASSET,
            'is_cash' => true,
        ]);

        Livewire::test(AccountDeleteModal::class)
            ->call('load', $cash->id)
            ->assertSee('default cash account', false)
            ->call('delete');

        $this->assertDatabaseHas('accounts', ['id' => $cash->id]);
    }

    public function test_the_account_delete_modal_refuses_an_account_with_entries()
    {
        $this->signIn();

        $account = $this->account('1400', 'Still In Use');
        $entry = $this->journalEntry('GJ-2026-0300');

        JournalLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $account->id,
            'account_name'     => $account->name,
            'side'             => JournalLine::SIDE_DEBIT,
            'amount'           => 100,
        ]);

        Livewire::test(AccountDeleteModal::class)
            ->call('load', $account->id)
            ->assertSet('loaded', true)
            ->assertSee('journal entry', false)
            ->call('delete');

        $this->assertDatabaseHas('accounts', ['id' => $account->id]);
    }

    // =====================================================================
    // Journal entries
    // =====================================================================

    public function test_the_journal_list_has_five_columns_and_no_status_column()
    {
        $this->signIn();

        $this->journalEntry('GJ-2026-0500');

        $response = $this->get(route('accounting.journal-entries'));

        $response->assertOk();
        $response->assertSee('journal-entry-lines.js', false);

        $this->assertSame(1, preg_match(
            '/<table class="table table-hover table-sm mb-0">(.*?)<\/table>/s',
            $response->getContent(),
            $matches
        ));

        $this->assertSame(5, substr_count($matches[1], '<th scope="col"'));
        $this->assertStringNotContainsString('>Status<', $matches[1]);
        $this->assertStringNotContainsString('>Posted<', $matches[1]);
    }

    public function test_journal_account_lines_use_select2_and_keep_the_chosen_account_selected()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');

        Livewire::test(JournalEntryFormModal::class)
            ->call('openCreate')
            ->assertSee('data-thha-select2', false)
            ->assertSee('form-control', false)
            ->set('lines.0.account_id', (string) $cash->id)
            ->assertSee('value="' . $cash->id . '" selected', false)
            ->set('lines.0.amount', '10.00')
            ->assertSee('value="' . $cash->id . '" selected', false)
            ->call('addLine')
            ->assertSee('journalLineAccount2', false)
            ->assertSee('value="' . $cash->id . '" selected', false);
    }

    public function test_the_journal_entry_view_modal_loads_an_entry()
    {
        $this->signIn();

        $entry = $this->journalEntry('GJ-2026-0001');

        Livewire::test(JournalEntryViewModal::class)
            ->call('load', $entry->id)
            ->assertSet('loaded', true)
            ->assertSet('entryId', $entry->id)
            ->assertSee('GJ-2026-0001', false);
    }

    public function test_empty_journal_fields_show_a_muted_not_provided_placeholder()
    {
        $this->signIn();

        $entry = $this->journalEntry('GJ-2026-0002', [
            'memo'         => null,
            'reference_no' => null,
        ]);

        $this->get(route('accounting.journal-entries'))
            ->assertOk()
            ->assertSee('<span class="text-muted">Not provided</span>', false)
            ->assertDontSee('—');

        Livewire::test(JournalEntryViewModal::class)
            ->call('load', $entry->id)
            ->assertSee('<span class="text-muted">Not provided</span>', false)
            ->assertDontSee('—');
    }

    public function test_a_manual_entry_posts_multiple_lines_between_accounts()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');
        $dues = $this->account('4000', 'Monthly Dues', false, AccountType::INCOME);
        $fees = $this->account('4100', 'Fees and Charges', false, AccountType::INCOME);

        Livewire::test(JournalEntryFormModal::class)
            ->call('openCreate')
            ->set('entry_date', '2026-09-15')
            ->set('classification', JournalClassification::CORRECTING)
            ->set('lines', [
                ['account_id' => (string) $cash->id, 'side' => 'debit', 'amount' => '700.00'],
                ['account_id' => (string) $dues->id, 'side' => 'credit', 'amount' => '500.00'],
                ['account_id' => (string) $fees->id, 'side' => 'credit', 'amount' => '200.00'],
            ])
            ->set('memo', 'Corrected dues and fees posting')
            ->call('save')
            ->assertHasNoErrors();

        $entry = JournalEntry::where('memo', 'Corrected dues and fees posting')->firstOrFail();

        $this->assertSame('700.00', $entry->total_amount);
        $this->assertSame('1000 · Cash on Hand', $entry->goes_to);
        $this->assertSame('Several', $entry->comes_from);
        $this->assertCount(3, $entry->lines);
        $this->assertSame(1, $entry->lines()->where('side', JournalLine::SIDE_DEBIT)->count());
        $this->assertSame(2, $entry->lines()->where('side', JournalLine::SIDE_CREDIT)->count());
    }

    public function test_a_manual_entry_must_balance_before_it_is_posted()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');
        $income = $this->account('4000', 'Monthly Dues', false, AccountType::INCOME);

        Livewire::test(JournalEntryFormModal::class)
            ->call('openCreate')
            ->set('classification', JournalClassification::ADJUSTING)
            ->set('lines', [
                ['account_id' => (string) $cash->id, 'side' => 'debit', 'amount' => '500.00'],
                ['account_id' => (string) $income->id, 'side' => 'credit', 'amount' => '450.00'],
            ])
            ->set('memo', 'Unbalanced adjustment')
            ->call('save')
            ->assertHasErrors('lines');

        $this->assertDatabaseMissing('journal_entries', ['memo' => 'Unbalanced adjustment']);
    }

    public function test_every_journal_classification_is_available()
    {
        $this->assertSame(
            ['standard', 'adjusting', 'correcting', 'closing', 'reversing', 'opening'],
            array_keys(JournalClassification::options())
        );
    }

    public function test_a_manual_entry_can_be_revised_and_removed()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');
        $income = $this->account('4000', 'Monthly Dues', false, AccountType::INCOME);

        $entry = JournalEntry::post([
            'entry_date'     => now()->subDay(),
            'classification' => JournalClassification::STANDARD,
            'memo'           => 'Original memo',
            'lines'          => [
                ['account_id' => $cash->id, 'side' => 'debit', 'amount' => 500],
                ['account_id' => $income->id, 'side' => 'credit', 'amount' => 500],
            ],
        ], auth('web')->id());

        $this->assertSame(1, preg_match('/^JE-\d{4}-\d{6}$/', $entry->entry_no));

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->assertSet('isSystemEntry', false)
            ->set('lines', [
                ['account_id' => (string) $cash->id, 'side' => 'debit', 'amount' => '750.00'],
                ['account_id' => (string) $income->id, 'side' => 'credit', 'amount' => '750.00'],
            ])
            ->set('memo', 'Corrected memo')
            ->call('save')
            ->assertHasNoErrors();

        $entry->refresh();

        $this->assertSame('750.00', $entry->total_amount);
        $this->assertSame('Corrected memo', $entry->memo);

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->call('removeEntry');

        $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);
        $this->assertDatabaseMissing('journal_lines', ['journal_entry_id' => $entry->id]);
    }

    public function test_a_module_entry_keeps_its_amount_and_accounts_when_its_memo_is_revised()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');
        $income = $this->account('4000', 'Monthly Dues', false, AccountType::INCOME);

        $entry = JournalEntry::post([
            'entry_date'     => now()->subDay(),
            'classification' => JournalClassification::STANDARD,
            'memo'           => 'Payment OR-0001',
            'source_type'    => 'payment',
            'source_id'      => 999,
            'lines'          => [
                ['account_id' => $cash->id, 'side' => 'debit', 'amount' => 500],
                ['account_id' => $income->id, 'side' => 'credit', 'amount' => 500],
            ],
        ], auth('web')->id());

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->assertSet('isSystemEntry', true)
            ->set('lines.0.amount', '9999.00')
            ->set('memo', '')
            ->call('save')
            ->assertHasErrors('memo');

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->set('lines.0.amount', '9999.00')
            ->set('memo', 'Clarified payment memo')
            ->call('save')
            ->assertHasNoErrors();

        $entry->refresh();

        $this->assertSame('500.00', $entry->total_amount);
        $this->assertSame('Clarified payment memo', $entry->memo);
        $this->assertSame('1000 · Cash on Hand', $entry->goes_to);
    }

    public function test_a_closed_month_refuses_an_edit_and_a_removal()
    {
        $admin = $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');
        $income = $this->account('4000', 'Monthly Dues', false, AccountType::INCOME);

        $date = now()->startOfMonth()->subMonth()->addDays(2);

        $entry = JournalEntry::post([
            'entry_date'     => $date,
            'classification' => JournalClassification::STANDARD,
            'memo'           => 'Entry in a month that will close',
            'lines'          => [
                ['account_id' => $cash->id, 'side' => 'debit', 'amount' => 500],
                ['account_id' => $income->id, 'side' => 'credit', 'amount' => 500],
            ],
        ], $admin->id);

        app(ClosePeriod::class)->handle($date->format('Y-m'), $admin);

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->set('memo', 'Changed after the close')
            ->call('save')
            ->assertHasErrors('entry_date');

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->call('removeEntry')
            ->assertHasErrors('entry_date');

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);
    }

    public function test_a_module_entry_cannot_be_removed_from_the_journal()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');
        $income = $this->account('4000', 'Monthly Dues', false, AccountType::INCOME);

        $entry = JournalEntry::post([
            'entry_date'     => now()->subDay(),
            'classification' => JournalClassification::STANDARD,
            'memo'           => 'Payment OR-0002',
            'source_type'    => 'payment',
            'source_id'      => 1000,
            'lines'          => [
                ['account_id' => $cash->id, 'side' => 'debit', 'amount' => 500],
                ['account_id' => $income->id, 'side' => 'credit', 'amount' => 500],
            ],
        ], auth('web')->id());

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->call('removeEntry')
            ->assertHasErrors('entry');

        $this->assertDatabaseHas('journal_entries', ['id' => $entry->id]);
    }

    public function test_the_account_delete_modal_refuses_an_account_the_books_already_name()
    {
        $admin = $this->signIn();

        $cash = $this->account('1000', 'Petty Cash');
        $savings = $this->account('1020', 'Savings');

        AccountTransfer::create([
            'reference'           => 'TRF-2026-0099',
            'transfer_date'       => now()->subDay()->toDateString(),
            'from_account_id'     => $cash->id,
            'to_account_id'       => $savings->id,
            'amount'              => 100,
            'memo'                => 'Moved savings',
            'created_by_admin_id' => $admin->id,
        ]);

        Livewire::test(AccountDeleteModal::class)
            ->call('load', $cash->id)
            ->assertSet('loaded', true)
            ->assertSet('deleteBlocker', '1 recorded transfer(s) name this account, and a transfer is part of the books — it cannot be removed.')
            ->call('delete');

        $this->assertDatabaseHas('accounts', ['id' => $cash->id]);
    }

    public function test_manual_journal_lines_cannot_use_system_accounts()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');
        $system = $this->account('3000', 'System Equity', false, AccountType::EQUITY);

        Livewire::test(JournalEntryFormModal::class)
            ->call('openCreate')
            ->assertDontSee('System Equity')
            ->set('memo', 'Should not post')
            ->set('lines', [
                ['account_id' => (string) $cash->id, 'side' => 'debit', 'amount' => '10.00'],
                ['account_id' => (string) $system->id, 'side' => 'credit', 'amount' => '10.00'],
            ])
            ->call('save')
            ->assertHasErrors('lines.1.account_id');

        $this->assertSame(0, JournalEntry::count());
    }

    public function test_a_module_entry_still_names_its_external_counterparty()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');

        $entry = JournalEntry::post([
            'entry_date'     => now()->subDay(),
            'classification' => JournalClassification::STANDARD,
            'memo'           => 'Cash funding',
            'source_type'    => 'fund_addition',
            'source_id'      => $cash->id,
            'lines'          => [
                ['account_id' => $cash->id, 'side' => 'debit', 'amount' => 500],
                ['account_name' => JournalEntry::EXTERNAL_ACCOUNT_NAME, 'side' => 'credit', 'amount' => 500],
            ],
        ], auth('web')->id());

        Livewire::test(JournalEntryFormModal::class)
            ->call('openEdit', $entry->id)
            ->assertSee(JournalEntry::EXTERNAL_ACCOUNT_NAME);
    }

    public function test_the_transfer_cash_funding_and_transaction_tables_have_five_columns()
    {
        $this->signIn();

        foreach (['accounting.transfers', 'accounting.cash-funding', 'accounting.transactions'] as $route) {
            $html = $this->get(route($route))->assertOk()->getContent();

            preg_match_all('/<th\b/', $html, $columns);

            $this->assertCount(5, $columns[0], $route . ' should have five table columns.');
        }
    }

    // =====================================================================
    // Cash funding
    // =====================================================================

    public function test_the_cash_funding_modal_records_money_against_an_external_counterparty()
    {
        $this->signIn();

        $cash = $this->account('1000', 'Cash on Hand');

        Livewire::test(CashFundingFormModal::class)
            ->call('openCreate')
            ->set('entry_date', '2026-09-15')
            ->set('to_account_id', (string) $cash->id)
            ->set('amount', '2500.00')
            ->set('memo', 'HOA fund injection')
            ->set('reference_no', 'BNK-100')
            ->call('save')
            ->assertHasNoErrors();

        $entry = JournalEntry::where('source_type', JournalSource::FUND_ADDITION)->firstOrFail();
        $external = $entry->lines()->whereNull('account_id')->firstOrFail();

        $this->assertSame('2500.00', $entry->total_amount);
        $this->assertSame('HOA fund injection', $entry->memo);
        $this->assertSame('BNK-100', $entry->reference_no);
        $this->assertSame(JournalClassification::STANDARD, $entry->classification->value);
        $this->assertSame('2500.00', $entry->lines()->where('account_id', $cash->id)->value('amount'));
        $this->assertSame('2500.00', $external->amount);
        $this->assertSame(JournalLine::SIDE_DEBIT, $entry->lines()->where('account_id', $cash->id)->value('side'));
        $this->assertSame(JournalLine::SIDE_CREDIT, $external->side);
        $this->assertSame(JournalEntry::EXTERNAL_ACCOUNT_NAME, $external->account_name);
        $this->assertDatabaseMissing('accounts', ['system_key' => 'funds_added']);
    }

    // =====================================================================
    // Transfers
    // =====================================================================

    public function test_the_transfer_delete_modal_removes_the_transfer()
    {
        $admin = $this->signIn();

        $from = $this->account('1010', 'Cash on Hand');
        $to = $this->account('1020', 'BPI Checking');

        $transfer = AccountTransfer::create([
            'reference'            => 'TRF-2026-0001',
            'transfer_date'        => now()->subDay(),
            'from_account_id'      => $from->id,
            'to_account_id'        => $to->id,
            'amount'               => 1000,
            'memo'                 => 'Fund the operating account',
            'reference_no'         => null,
            'created_by_admin_id'  => $admin->id,
        ]);

        Livewire::test(TransferDeleteModal::class)
            ->call('load', $transfer->id)
            ->assertSet('loaded', true)
            ->assertSet('deletingTransferLabel', 'TRF-2026-0001')
            ->call('delete');

        $this->assertDatabaseMissing('account_transfers', ['id' => $transfer->id]);
    }

    // =====================================================================
    // Period close
    // =====================================================================

    public function test_the_period_close_and_reopen_modals_lock_and_unlock_a_month()
    {
        $this->signIn();

        $period = now()->startOfMonth()->subMonth()->format('Y-m');

        Livewire::test(PeriodCloseModal::class)
            ->call('load', $period)
            ->assertSet('loaded', true)
            ->assertSet('closingPeriod', $period)
            ->set('notes', 'Month-end close')
            ->call('closePeriod');

        $this->assertDatabaseHas('accounting_periods', [
            'period' => $period,
            'status' => 'closed',
            'notes'  => 'Month-end close',
        ]);

        Livewire::test(PeriodReopenModal::class)
            ->call('load', $period)
            ->assertSet('loaded', true)
            ->assertSet('reopeningPeriod', $period)
            ->call('reopenPeriod');

        $this->assertDatabaseHas('accounting_periods', ['period' => $period, 'status' => 'open']);
    }

}
