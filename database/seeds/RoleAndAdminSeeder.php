<?php

use App\Enums\DefaultRoleSlug;
use App\Models\Admin;
use App\Services\Access\DefaultRoleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAndAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = app(DefaultRoleService::class);

        $roles->ensure();

        $email = config('account.admin.email');
        $password = config('account.admin.password');

        if (empty($email) || empty($password)) {
            $this->command->error('ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env');

            return;
        }

        Admin::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('account.admin.name'),
                'password' => Hash::make($password),
                'role_id' => $roles->role(DefaultRoleSlug::ADMINISTRATOR)->id,
            ]
        );
    }
}
