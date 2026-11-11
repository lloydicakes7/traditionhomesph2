<?php

namespace Tests\Feature\UserManagement;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScratchUsersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page(): void
    {
        $role = Role::create(['name' => 'Administrator', 'description' => 'Admin', 'permissions' => []]);
        $by = Admin::create(['name' => 'Actor', 'email' => 'actor@example.test', 'password' => Hash::make('x'), 'role_id' => $role->id, 'is_active' => true]);
        Admin::create(['name' => 'Officer', 'email' => 'officer@example.test', 'password' => Hash::make('x'), 'role_id' => $role->id, 'is_active' => true]);

        $html = $this->actingAs($by, 'web')->get('/admin/users')->assertOk()->getContent();

        echo "\n--- trigger count: " . substr_count($html, 'data-target="#adminResetPasswordModal"');
        echo "\n--- modal id count: " . substr_count($html, 'adminResetPasswordModal');
        echo "\n--- has skeleton+key: " . (strpos($html, 'users.reset') !== false ? 'lang key leaked' : 'no raw key');
        file_put_contents(base_path('storage/app/scratch-users.html'), $html);
    }
}
