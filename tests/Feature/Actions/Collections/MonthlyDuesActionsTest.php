<?php

namespace Tests\Feature\Actions\Collections;

use App\Actions\Collections\MonthlyDues\ApplyAdvanceDepositsToDues;
use App\Actions\Collections\MonthlyDues\ApplyDuePenalties;
use App\Actions\Collections\MonthlyDues\CancelMonthlyDue;
use App\Actions\Collections\MonthlyDues\CreatePastMonthlyDues;
use App\Actions\Collections\MonthlyDues\DeleteMonthlyDues;
use App\Actions\Collections\MonthlyDues\GenerateMonthlyDues;
use App\Actions\Collections\Settings\UpdateCollectionSettings;
use App\Enums\AccountingPeriodStatus;
use App\Enums\AdvanceDepositStatus;
use App\Enums\BillStatus;
use App\Enums\BillType;
use App\Enums\BillingMethod;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Models\Account\AccountingPeriod;
use App\Models\Address;
use App\Models\Admin;
use App\Models\AdvanceDeposit;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MonthlyDuesActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 6, 10, 9, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ===== Generate =====

    public function test_generating_the_month_raises_one_due_per_occupied_unit(): void
    {
        $admin = $this->admin();
        $this->settings([
            'billing_method' => BillingMethod::FIXED_RATE,
            'monthly_rate'     => 1500,
            'generation_day' => 10,
        ]);

        $this->occupiedUnit();

        $result = app(GenerateMonthlyDues::class)->handle('2026-06', $admin);

        $this->assertSame(['created' => 1, 'skipped' => 0], $result);
        $this->assertSame(1500.0, (float) Bill::query()->monthlyDues()->first()->total);
        $this->assertSame($admin->id, (int) Bill::query()->monthlyDues()->first()->created_by_admin_id);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'generated',
            'module'   => 'monthly_due',
        ]);

        $this->assertSame(['created' => 0, 'skipped' => 1], app(GenerateMonthlyDues::class)->handle('2026-06', $admin));
    }

    // ===== Penalties =====

    public function test_a_due_past_its_due_date_is_penalised_once(): void
    {
        $admin = $this->admin();
        $this->settings(['penalty_type' => 'fixed', 'penalty_value' => 100]);

        $due = $this->due(1200, '2026-05-01');

        $this->assertSame(1, app(ApplyDuePenalties::class)->handle($admin));
        $this->assertSame(100.0, (float) $due->fresh()->penalty);
        $this->assertSame(1300.0, (float) $due->fresh()->total);
        $this->assertSame(0, app(ApplyDuePenalties::class)->handle($admin));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'penalised',
            'module'   => 'monthly_due',
        ]);
    }

    // ===== Credit sweep =====

    public function test_applying_advance_deposits_settles_the_month_from_the_oldest_credit(): void
    {
        $admin = $this->admin();
        $due = $this->due(1200, '2026-06-20');

        AdvanceDeposit::create([
            'user_id'      => $due->user_id,
            'amount'       => 500,
            'balance'      => 500,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now()->subMonth(),
        ]);

        AdvanceDeposit::create([
            'user_id'      => $due->user_id,
            'amount'       => 1000,
            'balance'      => 1000,
            'status'       => AdvanceDepositStatus::AVAILABLE,
            'deposited_at' => now()->subWeek(),
        ]);

        $this->assertSame(2, app(ApplyAdvanceDepositsToDues::class)->handle('2026-06', $admin));

        $this->assertSame(0.0, (float) $due->fresh()->balance);
        $this->assertSame(0.0, (float) AdvanceDeposit::orderBy('id')->first()->balance);
        $this->assertSame(300.0, (float) AdvanceDeposit::orderByDesc('id')->first()->balance);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'applied',
            'module'   => 'monthly_due',
        ]);
    }

    // ===== Withdraw =====

    public function test_cancelling_a_due_withdraws_it(): void
    {
        $admin = $this->admin();
        $due = $this->due(1200, '2026-06-20');

        $cancelled = app(CancelMonthlyDue::class)->handle($due, $admin);

        $this->assertSame(BillStatus::CANCELLED, $cancelled->status);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'cancelled',
            'module'   => 'monthly_due',
        ]);
    }

    public function test_deleting_a_due_leaves_a_paid_one_alone(): void
    {
        $admin = $this->admin();
        $free = $this->due(1200, '2026-06-20');
        $paid = $this->due(1200, '2026-06-20');

        Payment::create([
            'bill_id' => $paid->id,
            'user_id' => $paid->user_id,
            'amount'  => 1200,
            'paid_at' => now(),
            'status'  => 'recorded',
        ]);

        $deleted = app(DeleteMonthlyDues::class)->handle([$free->id, $paid->id], $admin);

        $this->assertSame(1, $deleted);
        $this->assertNull(Bill::find($free->id));
        $this->assertNotNull(Bill::find($paid->id));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'monthly_due',
        ]);
    }

    // ===== Past dues =====

    public function test_past_dues_are_billed_for_every_month_and_skipped_on_a_second_run(): void
    {
        $admin = $this->admin();
        $this->settings([
            'billing_method' => BillingMethod::FIXED_RATE,
            'monthly_rate'   => 1200,
        ]);

        $user = $this->occupiedUnit();

        $result = app(CreatePastMonthlyDues::class)->handle([
            'scope'   => 'single',
            'user_id' => $user->id,
            'from'    => '2026-01',
            'to'      => '2026-03',
        ], $admin);

        $this->assertSame(['created' => 3, 'skipped' => 0], $result);
        $this->assertSame(3, Bill::query()->monthlyDues()->where('user_id', $user->id)->count());
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'generated',
            'module'   => 'monthly_due',
        ]);

        $again = app(CreatePastMonthlyDues::class)->handle([
            'scope'   => 'single',
            'user_id' => $user->id,
            'from'    => '2026-01',
            'to'      => '2026-03',
        ], $admin);

        $this->assertSame(['created' => 0, 'skipped' => 3], $again);
        $this->assertSame(3, Bill::query()->monthlyDues()->where('user_id', $user->id)->count());
    }

    public function test_a_closed_month_rolls_the_whole_past_dues_batch_back(): void
    {
        $admin = $this->admin();
        $this->settings([
            'billing_method' => BillingMethod::FIXED_RATE,
            'monthly_rate'   => 1200,
        ]);

        $user = $this->occupiedUnit();

        AccountingPeriod::create([
            'period' => '2026-02',
            'status' => AccountingPeriodStatus::CLOSED,
        ]);

        try {
            app(CreatePastMonthlyDues::class)->handle([
                'scope'   => 'single',
                'user_id' => $user->id,
                'from'    => '2026-01',
                'to'      => '2026-03',
            ], $admin);

            $this->fail('A closed month should refuse the batch.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('February 2026', $e->errors()['from'][0]);
        }

        $this->assertSame(0, Bill::query()->monthlyDues()->count());
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'dues-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }

    private function settings(array $data): void
    {
        app(UpdateCollectionSettings::class)->handle($data, 'Updated the collections settings', $this->admin());
    }

    private function occupiedUnit(float $sqm = 100): User
    {
        $address = Address::create([
            'label'       => 'Unit 1-2-' . uniqid(),
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Poblacion',
            'city'        => 'Santa Rosa',
            'province'    => 'Laguna',
            'region'      => 'IV-A',
        ]);

        $property = Property::create([
            'block'      => random_int(1, 90),
            'lot'        => random_int(1, 90),
            'sqm'        => $sqm,
            'type'       => PropertyType::HOUSE,
            'address_id' => $address->id,
        ]);

        $resident = factory(Resident::class)->create();
        $user = factory(User::class)->create(['resident_id' => $resident->id]);

        $household = Household::create([
            'property_id'      => $property->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($resident->id, 'father');

        return $user;
    }

    private function due(float $total, string $dueDate): Bill
    {
        $user = factory(User::class)->create();

        $due = Bill::create([
            'reference'      => 'DUE-' . uniqid(),
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => BillType::MONTHLY_DUE,
            'billing_period' => '2026-06',
            'bill_date'      => now()->toDateString(),
            'due_date'       => $dueDate,
            'subtotal'       => $total,
            'total'          => $total,
            'paid_amount'    => 0,
            'balance'        => $total,
            'status'         => 'unpaid',
        ]);

        $due->items()->create([
            'name'       => 'Monthly Dues',
            'quantity'   => 1,
            'unit_price' => $total,
            'amount'     => $total,
        ]);

        return $due;
    }
}
