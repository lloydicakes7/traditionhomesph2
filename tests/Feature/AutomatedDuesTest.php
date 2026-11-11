<?php

namespace Tests\Feature;

use App\Enums\BillingMethod;
use App\Enums\HouseholdType;
use App\Enums\PropertyType;
use App\Http\Livewire\Admin\Collections\MonthlyDues\MonthlyDuesGenerator;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Notifications\DuesReminderNotification;
use App\Actions\Collections\Settings\UpdateCollectionSettings;
use App\Queries\Collections\Settings\CollectionSettingsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Collections settings that are not a page: what the scheduler raises, what
 * it penalises, and what it reminds.
 *
 * All three read the same `collections` group through
 * `CollectionSettingsQuery`, and all three do nothing at all until the office
 * asks for them — which is the first thing each test pins down.
 */
class AutomatedDuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A fixed day the whole file can reason about: the 10th of June, so the
        // month's billing period, a due date three days out and the generation
        // day are all known rather than "whenever this ran".
        Carbon::setTestNow(Carbon::create(2026, 6, 10, 9, 0, 0));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // =====================================================================
    // Fixtures
    // =====================================================================

    /** @var CollectionSettingsQuery */
    private $settings;

    private function settings(): CollectionSettingsQuery
    {
        return $this->settings ?: $this->settings = app(CollectionSettingsQuery::class);
    }

    private function saveSettings(array $data): void
    {
        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'settings-fixture-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);

        app(UpdateCollectionSettings::class)->handle($data, 'Updated the collections settings', $admin);
    }

    /**
     * An officer with the panel's full access — the generator is theirs to run.
     */
    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'dues-generator-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * One occupied unit, billed to its own account.
     */
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
            'block'      => 1,
            'lot'        => 2,
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

    /**
     * A month's due, with the one line every recalculation reads its total back
     * from.
     */
    private function due(User $user, float $amount, ?string $dueDate): Bill
    {
        $bill = Bill::create([
            'reference'      => 'DUE-202606-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => 'monthly_due',
            'billing_period' => '2026-06',
            'bill_date'      => '2026-06-01',
            'due_date'       => $dueDate,
            'subtotal'       => $amount,
            'total'          => $amount,
            'balance'        => $amount,
            'status'         => 'unpaid',
        ]);

        $bill->items()->create([
            'name'       => 'Monthly Dues June 2026',
            'quantity'   => 1,
            'unit_price' => $amount,
            'amount'     => $amount,
        ]);

        return $bill;
    }

    // =====================================================================
    // Bill Generation
    // =====================================================================

    public function test_automatic_mode_raises_the_month_once_the_generation_day_arrives(): void
    {
        $this->occupiedUnit();

        $this->saveSettings([
            'generation_mode' => 'automatic',
            'generation_day'  => 10,
            'billing_method'  => BillingMethod::FIXED_RATE,
            'monthly_rate'      => 1500,
        ]);

        $this->artisan('dues:generate-monthly')->assertExitCode(0);

        $this->assertSame(1, Bill::query()->monthlyDues()->count());
        $this->assertSame('2026-06', Bill::query()->monthlyDues()->first()->billing_period);

        // ...and running it again the next day bills nobody twice.
        $this->artisan('dues:generate-monthly')->assertExitCode(0);

        $this->assertSame(1, Bill::query()->monthlyDues()->count());
    }

    public function test_automatic_mode_waits_for_the_generation_day(): void
    {
        $this->occupiedUnit();

        $this->saveSettings([
            'generation_mode' => 'automatic',
            'generation_day'  => 20,
            'billing_method'  => BillingMethod::FIXED_RATE,
            'monthly_rate'      => 1500,
        ]);

        $this->artisan('dues:generate-monthly')->assertExitCode(0);

        $this->assertSame(0, Bill::query()->monthlyDues()->count());
    }

    public function test_manual_mode_raises_nothing_on_its_own(): void
    {
        $this->occupiedUnit();

        $this->saveSettings([
            'generation_mode' => 'manual',
            'generation_day'  => 1,
            'billing_method'  => BillingMethod::FIXED_RATE,
            'monthly_rate'      => 1500,
        ]);

        $this->artisan('dues:generate-monthly')->assertExitCode(0);

        $this->assertSame(0, Bill::query()->monthlyDues()->count());
    }

    /**
     * The same month, raised by the officer on the generator page: the mode is
     * the office's, not the scheduler's, and the run is theirs to answer for.
     */
    public function test_the_generator_page_raises_the_month_for_the_officer(): void
    {
        $admin = $this->signInAdmin();

        $this->occupiedUnit();

        $this->saveSettings([
            'generation_mode'   => 'manual',
            'billing_method'    => BillingMethod::FIXED_RATE,
            'monthly_rate'        => 1500,
            'generation_day'    => 25,
            'grace_period_days' => 5,
        ]);

        Livewire::test(MonthlyDuesGenerator::class)
            ->set('month', '2026-06')
            ->call('generate')
            ->assertHasNoErrors();

        $due = Bill::query()->monthlyDues()->firstOrFail();

        $this->assertSame('2026-06', $due->billing_period);
        $this->assertSame('1500.00', $due->total);
        $this->assertSame('2026-06-30', $due->due_date->toDateString());
        // The run is the officer's, not the scheduler's.
        $this->assertSame($admin->id, (int) $due->created_by_admin_id);
    }

    /**
     * A month generated after its own generation day is due from the grace
     * period onwards, never already overdue: the 1st has long passed on the
     * 10th, so the invoice is dated from today.
     */
    public function test_a_month_whose_generation_day_has_passed_is_due_from_today(): void
    {
        $this->saveSettings([
            'generation_day'    => 1,
            'grace_period_days' => 7,
        ]);

        $this->assertSame(
            '2026-06-17',
            $this->settings()->dueDateFor('2026-06')->toDateString()
        );
    }

    // =====================================================================
    // Penalty Settings
    // =====================================================================

    public function test_an_overdue_due_is_penalised_once_at_the_configured_amount(): void
    {
        $user = $this->occupiedUnit();

        $this->saveSettings([
            'penalty_type'  => 'fixed',
            'penalty_value' => 100,
        ]);

        $due = $this->due($user, 1000, '2026-06-05');

        $this->artisan('dues:apply-penalties')->assertExitCode(0);

        $due->refresh();

        $this->assertSame('100.00', $due->penalty);
        $this->assertSame('1100.00', $due->total);
        $this->assertSame('1100.00', $due->balance);

        // A second run must not charge it again.
        $this->artisan('dues:apply-penalties')->assertExitCode(0);

        $this->assertSame('1100.00', $due->refresh()->balance);
    }

    public function test_a_percentage_penalty_is_charged_on_what_is_still_owed(): void
    {
        $user = $this->occupiedUnit();

        $this->saveSettings([
            'penalty_type'  => 'percent',
            'penalty_value' => 2,
        ]);

        $due = $this->due($user, 1000, '2026-06-05');
        $due->update(['balance' => 500]);

        $this->artisan('dues:apply-penalties')->assertExitCode(0);

        $this->assertSame('10.00', $due->refresh()->penalty);
    }

    public function test_a_due_that_is_not_overdue_is_left_alone(): void
    {
        $user = $this->occupiedUnit();

        $this->saveSettings([
            'penalty_type'  => 'fixed',
            'penalty_value' => 100,
        ]);

        // Due tomorrow.
        $due = $this->due($user, 1000, '2026-06-11');

        $this->artisan('dues:apply-penalties')->assertExitCode(0);

        $this->assertSame('0.00', $due->refresh()->penalty);
    }

    public function test_no_penalty_is_configured_so_none_is_charged(): void
    {
        $user = $this->occupiedUnit();

        $this->saveSettings([
            'penalty_type'  => 'fixed',
            'penalty_value' => 0,
        ]);

        $due = $this->due($user, 1000, '2026-06-05');

        $this->artisan('dues:apply-penalties')->assertExitCode(0);

        $this->assertSame('0.00', $due->refresh()->penalty);
        $this->assertSame('1000.00', $due->balance);
    }

    // =====================================================================
    // Automated email reminders
    // =====================================================================

    public function test_a_due_close_to_its_due_date_is_reminded_once(): void
    {
        Notification::fake();

        $user = $this->occupiedUnit();

        $this->saveSettings(['auto_email_reminders' => true]);

        // Three days out — inside the reminder window.
        $due = $this->due($user, 1000, '2026-06-13');

        $this->artisan('dues:send-reminders')->assertExitCode(0);

        Notification::assertSentTo($user, DuesReminderNotification::class, 1);
        $this->assertNotNull($due->refresh()->reminder_sent_at);

        // The next day's run finds nothing left to remind.
        $this->artisan('dues:send-reminders')->assertExitCode(0);

        Notification::assertSentToTimes($user, DuesReminderNotification::class, 1);
    }

    public function test_nothing_is_reminded_while_the_switch_is_off(): void
    {
        Notification::fake();

        $user = $this->occupiedUnit();

        $this->saveSettings(['auto_email_reminders' => false]);

        $due = $this->due($user, 1000, '2026-06-13');

        $this->artisan('dues:send-reminders')->assertExitCode(0);

        Notification::assertNotSentTo($user, DuesReminderNotification::class);
        $this->assertNull($due->refresh()->reminder_sent_at);
    }

    public function test_a_due_further_out_than_the_window_is_not_reminded_yet(): void
    {
        Notification::fake();

        $user = $this->occupiedUnit();

        $this->saveSettings(['auto_email_reminders' => true]);

        $this->due($user, 1000, '2026-06-20');

        $this->artisan('dues:send-reminders')->assertExitCode(0);

        Notification::assertNotSentTo($user, DuesReminderNotification::class);
    }
}
