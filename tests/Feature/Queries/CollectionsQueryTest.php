<?php

namespace Tests\Feature\Queries;

use App\Enums\AdvanceDepositStatus;
use App\Enums\BillType;
use App\Enums\HouseholdType;
use App\Enums\MonthlyDueGenerationStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyType;
use App\Models\Address;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Property;
use App\Models\Resident;
use App\Models\SoaEmail;
use App\Models\User;
use App\Queries\Collections\AdvanceDeposits\AdvanceDepositListQuery;
use App\Queries\Collections\Invoices\InvoiceDetailQuery;
use App\Queries\Collections\Invoices\InvoiceListQuery;
use App\Queries\Collections\MonthlyDues\AdvancePaymentQuery;
use App\Queries\Collections\MonthlyDues\MonthlyDueGenerationQuery;
use App\Queries\Collections\MonthlyDues\MonthlyDueListQuery;
use App\Queries\Collections\MonthlyDues\MonthlyDueMatrixQuery;
use App\Queries\Collections\Payments\BillOptionQuery;
use App\Queries\Collections\Payments\PaymentDetailQuery;
use App\Queries\Collections\Payments\PaymentListQuery;
use App\Queries\Collections\Payments\PortalPaymentQuery;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use App\Queries\Collections\PaymentMethods\PaymentMethodQuery;
use App\Queries\Collections\ProductCategories\ProductCategoryListQuery;
use App\Queries\Collections\Products\ProductListQuery;
use App\Queries\Collections\Soa\StatementDataQuery;
use App\Queries\Collections\Soa\StatementListQuery;
use App\Utility\MoneyHelper;
use App\Utility\SettingsHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CollectionsQueryTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // Payments
    // =====================================================================

    public function test_the_payment_list_filters_and_sorts(): void
    {
        $homeowner = factory(User::class)->create();
        $gcash = $this->method('GCash');
        $bank = $this->method('Bank');

        $older = $this->bill($homeowner, 'INV-2026-0001', 500);
        $newer = $this->bill($homeowner, 'INV-2026-0002', 900);

        $this->payment($older, $homeowner, 500, $gcash, 'GCASH-1', '2026-09-02');
        $this->payment($newer, $homeowner, 900, $bank, 'BANK-1', '2026-10-02');

        $query = app(PaymentListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate(['search' => 'BANK-1'])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['method' => $gcash->id]])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['month' => '2026-09']])->total());

        $ascending = $query->paginate(['sort' => 'amount', 'direction' => 'asc']);

        $this->assertSame(500.0, (float) $ascending->items()[0]->amount);
        $this->assertCount(2, $query->ids());
    }

    public function test_the_payment_list_stays_inside_its_query_budget(): void
    {
        $homeowner = factory(User::class)->create();
        $method = $this->method('GCash');

        foreach (range(1, 5) as $i) {
            $bill = $this->bill($homeowner, 'INV-2026-000' . $i, 100);
            $this->payment($bill, $homeowner, 100, $method, 'REF-' . $i, '2026-09-0' . $i);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        app(PaymentListQuery::class)->paginate();

        $this->assertLessThanOrEqual(15, count(DB::getQueryLog()));
    }

    public function test_the_payment_detail_reads_one_receipt_and_a_bills_receipts(): void
    {
        $homeowner = factory(User::class)->create();
        $bill = $this->bill($homeowner, 'INV-2026-0001', 500);
        $payment = $this->payment($bill, $homeowner, 500, null, 'REF-1', '2026-09-02');

        $query = app(PaymentDetailQuery::class);

        $this->assertSame($payment->id, $query->find($payment->id)->id);
        $this->assertCount(1, $query->forBill($bill));
    }

    // =====================================================================
    // Invoices and monthly dues
    // =====================================================================

    public function test_the_invoice_list_filters_sorts_and_counts(): void
    {
        $homeowner = factory(User::class)->create();

        $this->bill($homeowner, 'INV-2026-0001', 500);
        $this->bill($homeowner, 'INV-2026-0002', 900);
        $this->bill($homeowner, 'AMN-2026-0001', 2000, BillType::AMENITY_BOOKING);

        $query = app(InvoiceListQuery::class);

        $this->assertSame(3, $query->paginate()->total());
        $this->assertSame(3, $query->count());
        $this->assertSame(1, $query->paginate(['search' => 'INV-2026-0002'])->total());
        $this->assertCount(3, $query->ids());
        $this->assertNotEmpty($query->months());
    }

    public function test_the_monthly_due_list_is_scoped_to_dues(): void
    {
        $homeowner = factory(User::class)->create();

        $this->bill($homeowner, 'INV-2026-0001', 500);
        $due = $this->due($homeowner, 'DUE-2026-09', '2026-09');

        $query = app(MonthlyDueListQuery::class);

        $this->assertSame(1, $query->paginate()->total());
        $this->assertSame($due->id, $query->find($due->id)->id);
        $this->assertSame(1, $query->paginate(['filters' => ['month' => '2026-09']])->total());
        $this->assertSame(0, $query->paginate(['filters' => ['month' => '2026-08']])->total());
    }

    public function test_the_matrix_reads_each_propertys_dues_and_what_was_collected(): void
    {
        $homeowner = $this->occupiedUnit();

        $this->due($homeowner, 'DUE-2026-12', '2026-12')->settle(['amount' => 1200]);
        $this->due($homeowner, 'DUE-2026-10', '2026-10');

        $matrix = app(MonthlyDueMatrixQuery::class)->report('2026');

        $this->assertSame('2026', $matrix['year']);
        $this->assertCount(12, $matrix['months']);

        $row = $matrix['rows'][0];

        $this->assertSame($homeowner->id, $row['property']->owner->id);
        $this->assertNotNull($row['cells']['2026-12']);
        $this->assertNotNull($row['cells']['2026-10']);
        $this->assertNull($row['cells']['2026-11']);
        $this->assertSame('1200.00', $row['year_collected']);
        $this->assertSame('1200.00', $row['year_balance']);
        $this->assertSame(MoneyHelper::format(1200), $row['row_collected']);
        $this->assertSame(MoneyHelper::format(1200), $matrix['collected_display']['2026-12']);
        $this->assertSame(MoneyHelper::format(0), $matrix['collected_display']['2026-10']);
        $this->assertArrayHasKey('rows', app(MonthlyDueMatrixQuery::class)->matrix('2026'));
    }

    // =====================================================================
    // Options
    // =====================================================================

    public function test_the_bill_options_leave_out_a_bill_a_receipt_is_waiting_on(): void
    {
        $homeowner = factory(User::class)->create();
        $waiting = $this->bill($homeowner, 'INV-2026-0001', 500);
        $free = $this->bill($homeowner, 'INV-2026-0002', 900);

        Payment::create([
            'bill_id'      => $waiting->id,
            'user_id'      => $homeowner->id,
            'amount'       => 500,
            'reference_no' => 'GCASH-1',
            'paid_at'      => now(),
            'status'       => PaymentStatus::PENDING,
        ]);

        $query = app(BillOptionQuery::class);
        $options = $query->options($homeowner->id);

        $this->assertArrayNotHasKey((string) $waiting->id, $options);
        $this->assertArrayHasKey((string) $free->id, $options);
        $this->assertSame($free->id, $query->find((string) $free->id)->id);
        $this->assertSame((int) $waiting->id, (int) $query->pending($waiting)->bill_id);
    }

    public function test_the_payment_method_options_read_active_methods(): void
    {
        $this->method('GCash');
        $this->method('Retired', false);

        $query = app(PaymentMethodQuery::class);

        $this->assertCount(2, $query->options());
        $this->assertCount(1, $query->active());
        $this->assertSame(2, $query->paginate()->total());
    }

    public function test_a_method_added_after_the_first_read_is_offered_at_once(): void
    {
        $this->method('GCash');

        $query = app(PaymentMethodQuery::class);
        $this->assertCount(1, $query->active());

        $cash = PaymentMethod::create(['name' => 'Cash', 'is_active' => true, 'sort_order' => 2]);

        $this->assertTrue($query->active()->contains('id', $cash->id));
    }

    // =====================================================================
    // Advance deposits
    // =====================================================================

    public function test_the_advance_deposit_list_filters_status_and_sorts(): void
    {
        $homeowner = factory(User::class)->create();

        $this->deposit($homeowner, 500, AdvanceDepositStatus::AVAILABLE(), '2026-07-01');
        $this->deposit($homeowner, 900, AdvanceDepositStatus::REFUNDED(), '2026-09-01');

        $query = app(AdvanceDepositListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate(['filters' => ['status' => AdvanceDepositStatus::REFUNDED()->value]])->total());

        $ascending = $query->paginate(['sort' => 'amount', 'direction' => 'asc']);

        $this->assertSame(500.0, (float) $ascending->items()[0]->amount);
        $this->assertArrayHasKey($homeowner->id, $query->holderOptions());
    }

    // =====================================================================
    // Products
    // =====================================================================

    public function test_the_product_list_filters_type_and_category(): void
    {
        $category = ProductCategory::create(['name' => 'Stickers']);

        Product::create(['name' => 'Car Sticker', 'type' => 'product', 'price' => 100, 'product_category_id' => $category->id, 'is_active' => true]);
        Product::create(['name' => 'Gate Pass', 'type' => 'service', 'price' => 50, 'is_active' => true]);

        $query = app(ProductListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate(['filters' => ['type' => 'service']])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['category' => $category->id]])->total());

        $byPrice = $query->paginate(['sort' => 'price', 'direction' => 'asc']);

        $this->assertSame('Gate Pass', $byPrice->items()[0]->name);
        $this->assertCount(2, $query->ids());
        $this->assertCount(2, $query->billable());
        $this->assertTrue($query->hasBillable());
        $this->assertArrayHasKey($category->id, $query->categoryOptions());
    }

    public function test_a_category_added_after_the_options_are_read_appears_in_them(): void
    {
        $query = app(ProductListQuery::class);

        $this->assertSame([], $query->categoryOptions());

        $category = ProductCategory::create(['name' => 'Stickers']);

        $this->assertSame([$category->id => 'Stickers'], $query->categoryOptions());
    }

    public function test_the_product_category_list_reads_its_rows_and_ids(): void
    {
        ProductCategory::create(['name' => 'Stickers']);
        ProductCategory::create(['name' => 'Permits']);

        $query = app(ProductCategoryListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate(['search' => 'Permits'])->total());
        $this->assertCount(1, $query->ids(['search' => 'Permits']));
        $this->assertSame('Permits', $query->find(ProductCategory::where('name', 'Permits')->first()->id)->name);
    }

    // =====================================================================
    // Statements
    // =====================================================================

    public function test_the_advance_payment_preview_prices_the_months_to_bill(): void
    {
        $homeowner = $this->occupiedUnit();

        SettingsHelper::bulkSet([
            'billing_method' => 'fixed_rate',
            'monthly_rate'   => 1200,
        ], CollectionSettingsQuery::GROUP);
        SettingsHelper::forgetCache(CollectionSettingsQuery::CACHE_KEY);

        $this->due($homeowner, 'DUE-2026-06', '2026-06');
        $this->deposit($homeowner, 5000, AdvanceDepositStatus::AVAILABLE(), '2026-07-01');

        $query = app(AdvancePaymentQuery::class);
        $preview = $query->preview($homeowner->id, '2026-06', '2026-08');

        $this->assertSame(2, $preview['created']);
        $this->assertSame(1, $preview['settled']);
        $this->assertSame(0, $preview['skipped']);
        $this->assertSame(3600.0, (float) $preview['total']);
        $this->assertArrayNotHasKey('rows', $preview);
        $this->assertStringContainsString('available', $query->depositOptions($homeowner->id)[AdvanceDeposit::first()->id]);
    }

    public function test_the_statement_list_filters_status_and_finds_one(): void
    {
        $homeowner = factory(User::class)->create();

        $statement = SoaEmail::create([
            'user_id' => $homeowner->id,
            'email'   => $homeowner->email,
            'period'  => '2026-09',
            'status'  => 'pending',
        ]);

        SoaEmail::create([
            'user_id' => $homeowner->id,
            'email'   => $homeowner->email,
            'period'  => '2026-10',
            'status'  => 'sent',
        ]);

        $query = app(StatementListQuery::class);

        $this->assertSame(2, $query->paginate()->total());
        $this->assertSame(1, $query->paginate(['filters' => ['status' => 'sent']])->total());
        $this->assertSame(1, $query->paginate(['filters' => ['period' => '2026-09']])->total());
        $this->assertSame($statement->id, $query->find($statement->id)->id);
        $this->assertNotEmpty($query->periods());
    }

    public function test_the_statement_data_groups_the_accounts_open_bills_by_kind(): void
    {
        $homeowner = factory(User::class)->create();
        $this->bill($homeowner, 'INV-2026-0001', 500);
        $this->due($homeowner, 'DUE-2026-09', '2026-09');

        $data = app(StatementDataQuery::class)->forUser($homeowner->id, null);
        $groups = collect($data['groups'])->keyBy('key');

        $this->assertArrayHasKey('groups', $data);
        $this->assertArrayHasKey('balance_display', $data);
        $this->assertSame(1700.0, (float) $data['balance']);
        $this->assertSame('Monthly Dues', $groups['dues']['label']);
        $this->assertCount(1, $groups['dues']['bills']);
        $this->assertSame(1200.0, $groups['dues']['total']);
        $this->assertSame('Invoices', $groups['invoices']['label']);
        $this->assertCount(1, $groups['invoices']['bills']);
        $this->assertSame(500.0, $groups['invoices']['total']);
        $this->assertArrayHasKey($homeowner->id, app(StatementDataQuery::class)->accounts());
    }

    // =====================================================================
    // Detail, portal, generator and settings
    // =====================================================================

    public function test_the_invoice_detail_reads_only_invoices(): void
    {
        $homeowner = factory(User::class)->create();
        $invoice = $this->bill($homeowner, 'INV-2026-0001', 500);
        $due = $this->due($homeowner, 'DUE-2026-09', '2026-09');

        $query = app(InvoiceDetailQuery::class);

        $this->assertSame($invoice->id, $query->find($invoice->id)->id);
        $this->assertSame($invoice->id, $query->findForEdit($invoice->id)->id);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $query->find($due->id);
    }

    public function test_the_portal_payment_query_stays_inside_the_account(): void
    {
        $homeowner = factory(User::class)->create();
        $other = factory(User::class)->create();
        $method = $this->method('GCash');

        $mine = $this->bill($homeowner, 'INV-2026-0001', 500);
        $theirs = $this->bill($other, 'INV-2026-0002', 500);

        $this->payment($mine, $homeowner, 500, $method, 'MINE-1', '2026-09-02');
        $this->payment($theirs, $other, 500, $method, 'THEIRS-1', '2026-09-03');

        $query = app(PortalPaymentQuery::class);

        $this->assertSame(['MINE-1'], $query->forUser($homeowner->id)->pluck('reference_no')->all());
        $this->assertCount(1, $query->openBills($homeowner->id));
        $this->assertSame('GCash', $query->forUser($homeowner->id)->first()->paymentMethod->name);
    }

    public function test_the_generator_preview_prices_every_unit(): void
    {
        $this->occupiedUnit();

        $preview = app(MonthlyDueGenerationQuery::class)->preview('2026-09');

        $this->assertSame('2026-09', $preview['month']);
        $this->assertSame(1, $preview['property_count']);
        $this->assertSame(0, $preview['generated_count']);
        $this->assertSame(MonthlyDueGenerationStatus::TO_BILL, $preview['rows']->first()['status']->value);
        $this->assertCount(1, $preview['rows']);
        $this->assertArrayHasKey('rate_cards', $preview);
        $this->assertArrayHasKey('method', $preview);
        $this->assertArrayHasKey('month_options', $preview);
    }

    public function test_the_generator_preview_reports_a_billed_unit_and_a_paid_one(): void
    {
        $homeowner = $this->occupiedUnit();

        $query = app(MonthlyDueGenerationQuery::class);

        $bill = $this->due($homeowner, 'DUE-2026-09', '2026-09');

        $preview = $query->preview('2026-09');

        $this->assertSame(1, $preview['generated_count']);
        $this->assertSame(MonthlyDueGenerationStatus::BILLED, $preview['rows']->first()['status']->value);

        $bill->settle(['amount' => 1200]);

        $this->assertSame(MonthlyDueGenerationStatus::PAID, $query->preview('2026-09')['rows']->first()['status']->value);
    }

    public function test_the_settings_query_reads_the_form_and_renders_the_instructions(): void
    {
        SettingsHelper::flush(CollectionSettingsQuery::GROUP);

        $query = app(CollectionSettingsQuery::class);

        $this->assertArrayHasKey('generation_day', $query->form());
        $this->assertContains('{{OWNER_NAME}}', $query->placeholders());
        $this->assertSame('', $query->renderInstructions());

        SettingsHelper::bulkSet(['invoice_instructions' => '<p>Hi {{OWNER_NAME}}</p>'], CollectionSettingsQuery::GROUP);
        SettingsHelper::forgetCache(CollectionSettingsQuery::CACHE_KEY);

        $this->assertStringContainsString('Hi Juan Dela Cruz', $query->previewInstructions());
        $this->assertStringContainsString('Hi Ana Reyes', $query->renderInstructions(['OWNER_NAME' => 'Ana Reyes']));
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function occupiedUnit(): User
    {
        $resident = factory(Resident::class)->create();
        $homeowner = factory(User::class)->create(['resident_id' => $resident->id]);

        $address = Address::create([
            'label'       => 'Unit 1-2-test',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Poblacion',
            'city'        => 'Santa Rosa',
            'province'    => 'Laguna',
            'region'      => 'IV-A',
        ]);

        $property = Property::create([
            'block'      => 1,
            'lot'        => 2,
            'sqm'        => 100,
            'type'       => PropertyType::HOUSE,
            'address_id' => $address->id,
        ]);

        $household = Household::create([
            'property_id' => $property->id,
            'type'        => HouseholdType::OWNER,
        ]);

        $household->placeHead($resident->id, 'father');

        return $homeowner;
    }

    private function method(string $name, bool $active = true): PaymentMethod
    {
        return PaymentMethod::create([
            'name'       => $name,
            'is_active'  => $active,
            'sort_order' => PaymentMethod::query()->max('sort_order') + 1,
        ]);
    }

    private function bill(User $user, string $reference, float $total, string $type = BillType::INVOICE): Bill
    {
        $bill = Bill::create([
            'reference'      => $reference,
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => $type,
            'bill_date'      => '2026-09-01',
            'due_date'       => '2026-09-10',
            'subtotal'       => $total,
            'total'          => $total,
            'paid_amount'    => 0,
            'balance'        => $total,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Charge ' . $reference,
            'quantity'   => 1,
            'unit_price' => $total,
            'amount'     => $total,
        ]);

        return $bill;
    }

    private function due(User $user, string $reference, string $period): Bill
    {
        $bill = Bill::create([
            'reference'      => $reference,
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => BillType::MONTHLY_DUE,
            'billing_period' => $period,
            'bill_date'      => '2026-09-01',
            'due_date'       => '2026-09-10',
            'subtotal'       => 1200,
            'total'          => 1200,
            'paid_amount'    => 0,
            'balance'        => 1200,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Monthly Dues ' . $period,
            'quantity'   => 1,
            'unit_price' => 1200,
            'amount'     => 1200,
        ]);

        return $bill;
    }

    private function payment(
        Bill $bill,
        User $user,
        float $amount,
        ?PaymentMethod $method,
        string $reference,
        string $paidAt
    ): Payment {
        return Payment::create([
            'bill_id'           => $bill->id,
            'user_id'           => $user->id,
            'payment_method_id' => $method ? $method->id : null,
            'amount'            => $amount,
            'reference_no'      => $reference,
            'paid_at'           => $paidAt,
            'status'            => PaymentStatus::RECORDED,
        ]);
    }

    private function deposit(User $user, float $amount, AdvanceDepositStatus $status, string $depositedAt): AdvanceDeposit
    {
        return AdvanceDeposit::create([
            'user_id'      => $user->id,
            'amount'       => $amount,
            'balance'      => $status === AdvanceDepositStatus::AVAILABLE() ? $amount : 0,
            'status'       => $status,
            'deposited_at' => $depositedAt,
        ]);
    }
}
