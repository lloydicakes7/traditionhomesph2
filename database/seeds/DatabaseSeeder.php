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
        $this->call(AddressSeeder::class);
        $this->call(AccountSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(RoleAndAdminSeeder::class);
    }
}
