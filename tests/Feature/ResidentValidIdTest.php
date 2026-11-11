<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * A submitted valid ID has no public URL: only an officer whose role may view
 * residents can read it back, and the file lives on the private disk.
 */
class ResidentValidIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_officer_with_the_residents_permission_downloads_the_id(): void
    {
        Storage::fake('local');

        $resident = factory(Resident::class)->create(['valid_id_path' => 'ids/residents/id.jpg']);

        Storage::disk('local')->put('ids/residents/id.jpg', 'image-bytes');

        $this->actingAs($this->officer(['residents.view']), 'web')
            ->get(route('homeowners.residents.valid-id', $resident))
            ->assertOk();
    }

    public function test_an_officer_without_the_permission_is_refused(): void
    {
        Storage::fake('local');

        $resident = factory(Resident::class)->create(['valid_id_path' => 'ids/residents/id.jpg']);

        Storage::disk('local')->put('ids/residents/id.jpg', 'image-bytes');

        $this->actingAs($this->officer([]), 'web')
            ->get(route('homeowners.residents.valid-id', $resident))
            ->assertForbidden();
    }

    public function test_a_guest_is_sent_to_the_unified_login(): void
    {
        $resident = factory(Resident::class)->create(['valid_id_path' => 'ids/residents/id.jpg']);

        $this->get(route('homeowners.residents.valid-id', $resident))
            ->assertRedirect('/login');
    }

    public function test_a_homeowner_cannot_reach_the_officer_route(): void
    {
        $resident = factory(Resident::class)->create(['valid_id_path' => 'ids/residents/id.jpg']);
        $user = factory(User::class)->create();

        $this->actingAs($user, 'homeowner')
            ->get(route('homeowners.residents.valid-id', $resident))
            ->assertRedirect('/login');
    }

    public function test_an_authorized_download_of_a_missing_file_is_a_404(): void
    {
        Storage::fake('local');

        $resident = factory(Resident::class)->create(['valid_id_path' => null]);

        $this->actingAs($this->officer(['residents.view']), 'web')
            ->get(route('homeowners.residents.valid-id', $resident))
            ->assertNotFound();
    }

    /**
     * An officer whose role holds the given permission keys.
     */
    private function officer(array $permissions): Admin
    {
        $role = Role::create([
            'name'        => 'Resident viewer',
            'permissions' => $permissions,
        ]);

        return Admin::create([
            'name'      => 'Officer',
            'email'     => 'officer.valid-id@example.com',
            'password'  => Hash::make('password'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }
}
