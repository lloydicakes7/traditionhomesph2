<?php

namespace Tests\Feature;

use App\Enums\DefaultRoleSlug;
use App\Models\Role;
use App\Services\Access\DefaultRoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultRoleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_roles_the_software_runs_on(): void
    {
        app(DefaultRoleService::class)->ensure();

        $this->assertDatabaseHas('roles', ['name' => DefaultRoleSlug::ADMINISTRATOR]);

        $visitor = Role::where('name', DefaultRoleSlug::VISITOR)->first();

        $this->assertNotNull($visitor);
        $this->assertContains('addresses.view', $visitor->permissionKeys());
        $this->assertCount(2, Role::all());
    }

    public function test_the_panel_restores_the_roles_on_its_first_request(): void
    {
        $this->get('/admin/register');

        $this->assertDatabaseHas('roles', ['name' => DefaultRoleSlug::ADMINISTRATOR]);
        $this->assertDatabaseHas('roles', ['name' => DefaultRoleSlug::VISITOR]);
    }

    public function test_it_leaves_a_role_the_office_already_holds_alone(): void
    {
        Role::create([
            'name' => DefaultRoleSlug::VISITOR,
            'description' => 'Visitor',
            'permissions' => ['homeowners.view'],
        ]);

        app(DefaultRoleService::class)->ensure();

        $this->assertSame(
            ['homeowners.view'],
            Role::where('name', DefaultRoleSlug::VISITOR)->first()->permissionKeys()
        );
    }
}
