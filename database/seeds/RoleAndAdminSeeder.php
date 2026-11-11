<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Enums\DefaultRoleSlug;
use App\Models\Admin;
use App\Models\Role;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // -----------------------------------------------------------------------
        // Create the two roles the system runs on.
        // -----------------------------------------------------------------------
        $adminRole = Role::firstOrCreate(
            ['name' => DefaultRoleSlug::ADMINISTRATOR],
            ['description' => DefaultRoleSlug::ADMINISTRATOR()->description()]
        );

        $visitorRole = Role::firstOrCreate(
            ['name' => DefaultRoleSlug::VISITOR],
            ['description' => DefaultRoleSlug::VISITOR()->description()]
        );

        // -----------------------------------------------------------------------
        // Create admin account
        // -----------------------------------------------------------------------
        $email    = config('account.admin.email');
        $password = config('account.admin.password');

        if (empty($email) || empty($password)) {
            $this->command->error('ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env');
            return;
        }

        Admin::firstOrCreate(
            ['email' => $email],
            [
                'name'     => config('account.admin.name'),
                'password' => Hash::make($password),
                'role_id'  => $adminRole->id,
            ]
        );
    }
}
