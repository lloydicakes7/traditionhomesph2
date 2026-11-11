<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // HOA Initial homeowners config
        $this->call(AddressSeeder::class);
        $this->call(ResidentSeeder::class);
        $this->call(HomeownerSeeder::class);
        $this->call(PropertySeeder::class);

        // Chart of Accounts
        $this->call(AccountSeeder::class);

        // HOA Center Seeders
        $this->call(DirectorySeeder::class);
        $this->call(HoaMemberSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(FaqSeeder::class);

        // HOA Initial settings config
        $this->call(SettingsSeeder::class);

        // HOA Initial admins permissions config
        $this->call(RoleAndAdminSeeder::class);
        $this->call(PermissionSeeder::class);
    }
}
