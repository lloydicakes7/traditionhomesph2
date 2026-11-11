<?php

namespace Tests\Feature;

use App\Enums\RegistrationStatus;
use App\Http\Livewire\Admin\Homeowners\Homeowner\HomeownerTableList;
use App\Http\Livewire\Admin\Homeowners\Homeowner\Modals\PendingRegistrationViewModal;
use App\Http\Livewire\Admin\Homeowners\Homeowner\PendingRegistrationTableList;
use App\Models\Admin;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The accounts a self-registration files, and the office's decision on each.
 */
class PendingRegistrationsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Page =====

    public function test_the_page_lists_only_accounts_waiting_for_approval(): void
    {
        $this->signInAdmin();

        $waiting = $this->account('Waiting', RegistrationStatus::PENDING);
        $approved = $this->account('Approved', RegistrationStatus::APPROVED);

        $this->get(route('homeowners.pending-registrations'))->assertOk();

        Livewire::test(PendingRegistrationTableList::class)
            ->assertSee($waiting->email)
            ->assertDontSee($approved->email);
    }

    public function test_the_detail_dialog_shows_the_registration_and_its_profile(): void
    {
        $this->signInAdmin();

        $applicant = $this->account('Applicant', RegistrationStatus::PENDING);

        Livewire::test(PendingRegistrationViewModal::class)
            ->call('load', $applicant->id)
            ->assertSee($applicant->email)
            ->assertSee($applicant->resident->full_name);
    }

    // ===== Accept =====

    public function test_accepting_a_registration_moves_it_to_the_homeowners_list(): void
    {
        $this->signInAdmin();

        $applicant = $this->account('Applicant', RegistrationStatus::PENDING);

        Livewire::test(PendingRegistrationTableList::class)
            ->call('accept', $applicant->id)
            ->assertHasNoErrors();

        $applicant->refresh();

        $this->assertSame(RegistrationStatus::APPROVED, $applicant->registration_status->value);
        $this->assertTrue((bool) $applicant->is_active);

        Livewire::test(PendingRegistrationTableList::class)
            ->assertDontSee($applicant->email);

        Livewire::test(HomeownerTableList::class)
            ->assertSee($applicant->email);
    }

    // ===== Account filter =====

    public function test_the_homeowners_list_filters_activated_and_deactivated_accounts(): void
    {
        $this->signInAdmin();

        $enabled = $this->account('Enabled', RegistrationStatus::APPROVED, true);
        $disabled = $this->account('Disabled', RegistrationStatus::APPROVED, false);

        Livewire::test(HomeownerTableList::class)
            ->assertSee($enabled->email)
            ->assertSee($disabled->email)
            ->set('accountStatus', 'active')
            ->assertSee($enabled->email)
            ->assertDontSee($disabled->email)
            ->set('accountStatus', 'inactive')
            ->assertSee($disabled->email)
            ->assertDontSee($enabled->email);
    }

    // ===== Tools =====

    private function account(
        string $surname,
        string $registrationStatus,
        bool $isActive = true
    ): User {
        $resident = factory(Resident::class)->create(['surname' => $surname]);

        return factory(User::class)->create([
            'resident_id'         => $resident->id,
            'registration_status' => $registrationStatus,
            'is_active'           => $isActive,
        ]);
    }

    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'pending-registrations-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }
}
