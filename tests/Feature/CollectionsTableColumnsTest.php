<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CollectionsTableColumnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_monthly_dues_generator_and_payments_tables_have_five_columns(): void
    {
        $this->signIn();

        foreach (['collections.monthly-dues-generator', 'collections.payments'] as $route) {
            $html = $this->get(route($route))->assertOk()->getContent();

            preg_match_all('/<th\b/', $html, $columns);

            $this->assertCount(5, $columns[0], $route . ' should have five table columns.');
        }
    }

    private function signIn(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'collections-columns-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }
}
