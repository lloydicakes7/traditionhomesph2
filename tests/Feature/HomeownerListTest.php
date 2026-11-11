<?php

namespace Tests\Feature;

use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Http\Livewire\Admin\Homeowners\Homeowner\HomeownerTableList;
use App\Http\Livewire\Admin\Homeowners\Homeowner\Modals\HomeownerViewModal;
use App\Models\Admin;
use App\Models\Bill\Bill;
use App\Models\Request;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Violation;
use App\Queries\Homeowners\Homeowners\HomeownerListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Homeowners list's account-facing behaviour: the last sign-in it reports,
 * the enable/disable it offers, and the background it tints a row by.
 */
class HomeownerListTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // Last login
    // =====================================================================

    /**
     * A successful portal sign-in stamps the account, and the list then reports
     * that moment instead of "Never".
     */
    public function test_a_portal_sign_in_stamps_the_last_login_the_list_shows(): void
    {
        $homeowner = factory(User::class)->create();

        $this->assertNull($homeowner->last_login_at, 'A fresh account should not have a last login yet.');

        $this->post(route('login'), [
            'email'    => $homeowner->email,
            'password' => 'password',
        ])->assertRedirect();

        $homeowner->refresh();

        $this->assertNotNull($homeowner->last_login_at, 'The sign-in did not stamp the account.');

        $this->signInAdmin();

        Livewire::test(HomeownerTableList::class)
            ->assertSee($homeowner->last_login_at->format('M d, Y h:i A'))
            ->assertDontSee('Never');
    }

    // =====================================================================
    // Enable / disable
    // =====================================================================

    /**
     * An officer who may edit homeowners can turn an account off and back on
     * from the row's Actions menu.
     */
    public function test_the_edit_permission_enables_and_disables_an_account(): void
    {
        $homeowner = factory(User::class)->create(['is_active' => true]);

        $this->signInAdmin();

        Livewire::test(HomeownerTableList::class)
            ->assertSee('Deactivate Account')
            ->call('toggleAccountStatus', $homeowner->id)
            ->assertHasNoErrors();

        $this->assertFalse($homeowner->fresh()->is_active, 'The toggle did not disable the account.');

        Livewire::test(HomeownerTableList::class)
            ->assertSee('Activate Account')
            ->call('toggleAccountStatus', $homeowner->id)
            ->assertHasNoErrors();

        $this->assertTrue($homeowner->fresh()->is_active, 'The toggle did not re-enable the account.');
    }

    /**
     * An officer who may only read the page is offered no enable/disable and is
     * refused the ability behind it.
     */
    public function test_the_toggle_is_not_offered_without_the_edit_permission(): void
    {
        $homeowner = factory(User::class)->create(['is_active' => true]);

        $reader = $this->signInAdmin(['homeowners.view']);

        Livewire::test(HomeownerTableList::class)
            ->assertDontSee('Deactivate Account')
            ->assertDontSee('Activate Account');

        $this->assertFalse(Gate::forUser($reader)->allows('toggleActive', $homeowner));
    }

    // =====================================================================
    // Row alerts
    // =====================================================================

    /**
     * One row carries one tint, so the outstanding items are ranked: an active
     * violation, then an unpaid bill, then a pending request.
     */
    public function test_the_row_alert_ranks_a_violation_over_a_bill_over_a_request(): void
    {
        $clean = $this->account('clean');
        $requester = $this->account('requester');
        $debtor = $this->account('debtor');
        $violator = $this->account('violator');
        $both = $this->account('both');

        Request::create([
            'resident_id' => $requester->resident_id,
            'type'        => RequestType::MEMBERSHIP_FORM,
            'status'      => RequestStatus::PENDING,
        ]);

        $this->unpaidBill($debtor);

        $this->activeViolation($violator);
        $this->activeViolation($both);
        $this->unpaidBill($both);

        $rows = app(HomeownerListQuery::class)->paginate()->keyBy('id');

        $this->assertNull($rows[$clean->id]->row_alert);
        $this->assertSame('warning', $rows[$requester->id]->row_alert);
        $this->assertSame('info', $rows[$debtor->id]->row_alert);
        $this->assertSame('danger', $rows[$violator->id]->row_alert);
        $this->assertSame('danger', $rows[$both->id]->row_alert, 'The more urgent item must win.');
    }

    /**
     * The tints reach the screen: the desktop row and the mobile card both wear
     * the ranked `table-*` class.
     */
    public function test_the_list_renders_the_row_tints(): void
    {
        $this->activeViolation($this->account('violator'));
        $this->unpaidBill($this->account('debtor'));

        Request::create([
            'resident_id' => $this->account('requester')->resident_id,
            'type'        => RequestType::MEMBERSHIP_FORM,
            'status'      => RequestStatus::PENDING,
        ]);

        $this->signInAdmin();

        Livewire::test(HomeownerTableList::class)
            ->assertSee('table-danger', false)
            ->assertSee('table-info', false)
            ->assertSee('table-warning', false);
    }

    // =====================================================================
    // View dialog
    // =====================================================================

    /**
     * The dialog carries the account's own records as tabs, each read for the
     * homeowner on screen.
     */
    public function test_the_view_dialog_offers_the_account_record_tabs(): void
    {
        $homeowner = $this->account('tabs');

        Request::create([
            'resident_id' => $homeowner->resident_id,
            'type'        => RequestType::MEMBERSHIP_FORM,
            'status'      => RequestStatus::PENDING,
        ]);

        $this->signInAdmin();

        Livewire::test(HomeownerViewModal::class)
            ->call('load', $homeowner->resident_id)
            ->assertSee('Request Documents')
            ->assertSee('Complaints')
            ->assertSee('Invoices')
            ->assertSee('Payments');
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * A homeowner account with a person behind it.
     */
    private function account(string $label): User
    {
        $resident = factory(Resident::class)->create(['surname' => ucfirst($label)]);

        return factory(User::class)->create(['resident_id' => $resident->id]);
    }

    private function activeViolation(User $account): Violation
    {
        return Violation::create([
            'resident_id'    => $account->resident_id,
            'violation_type' => ViolationType::EXCESSIVE_NOISE,
            'occurred_on'    => now()->toDateString(),
            'location'       => 'Tradition Homes',
            'description'    => 'Test citation.',
            'status'         => ViolationStatus::ACTIVE,
        ]);
    }

    private function unpaidBill(User $account): Bill
    {
        return Bill::create([
            'reference'      => 'INV-2026-' . str_pad((string) $account->id, 4, '0', STR_PAD_LEFT),
            'user_id'        => $account->id,
            'billed_to_name' => $account->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 500,
            'total'          => 500,
            'balance'        => 500,
            'status'         => 'unpaid',
        ]);
    }

    /**
     * Sign in as the Administrator (full access), or as an officer holding only
     * the given permission keys.
     *
     * @param  array<int, string>|null  $permissions
     */
    private function signInAdmin(?array $permissions = null): Admin
    {
        $name = $permissions === null ? 'Administrator' : 'Homeowners Reader';

        $role = Role::firstOrCreate(
            ['name' => $name],
            ['description' => $name, 'permissions' => $permissions ?? []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'homeowner-list-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }
}
