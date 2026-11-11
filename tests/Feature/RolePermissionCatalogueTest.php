<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\UserManagement\Role\Modals\RoleFormModal;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Setting;
use App\Utility\PermissionHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RolePermissionCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_homeowner_import_and_export_can_be_granted(): void
    {
        $this->assertTrue(PermissionHelper::isValid('homeowners.import'));
        $this->assertTrue(PermissionHelper::isValid('homeowners.export'));

        $role = Role::create([
            'name'        => 'Records Officer ' . uniqid(),
            'description' => 'Records officer',
            'permissions' => ['homeowners.view', 'homeowners.import', 'homeowners.export'],
        ]);

        $this->assertSame(
            ['homeowners.view', 'homeowners.import', 'homeowners.export'],
            $role->permissionKeys()
        );
    }

    public function test_the_authentication_settings_are_the_administrators_alone(): void
    {
        $this->assertFalse(PermissionHelper::isValid('authentication.view'));
        $this->assertFalse(PermissionHelper::isValid('authentication.update'));

        $role = Role::create([
            'name'        => 'Registration Officer ' . uniqid(),
            'description' => 'Registration officer',
            'permissions' => ['authentication.view', 'authentication.update'],
        ]);

        $officer = Admin::create([
            'name'                => 'Registration Officer',
            'email'               => 'registration-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->assertFalse($officer->can('viewAny', Setting::class));
        $this->assertFalse($officer->can('update', Setting::class));

        $administrator = $this->signInAdministrator();

        $this->assertTrue($administrator->can('viewAny', Setting::class));
        $this->assertTrue($administrator->can('update', Setting::class));
    }

    public function test_the_role_form_offers_the_homeowner_import_and_export_actions(): void
    {
        $this->signInAdministrator();

        Livewire::test(RoleFormModal::class)
            ->call('openCreate')
            ->call('selectModule', 'Homeowners')
            ->assertViewHas('activePermissions', function (array $permissions) {
                $keys = array_column($permissions, 'key');

                return in_array('homeowners.import', $keys, true)
                    && in_array('homeowners.export', $keys, true);
            });
    }

    private function signInAdministrator(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Administrator',
            'email'               => 'role-catalogue-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }
}
