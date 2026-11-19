<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaults = [
            // HOA Info
            ['key' => 'hoa_name',       'value' => config('thha.hoa_name'),       'group' => 'hoa_info'],
            ['key' => 'hoa_short_name', 'value' => config('thha.hoa_short_name'), 'group' => 'hoa_info'],
            ['key' => 'address',        'value' => config('thha.address'),        'group' => 'hoa_info'],
            ['key' => 'contact_number', 'value' => config('thha.contact_number'), 'group' => 'hoa_info'],
            ['key' => 'working_hours',  'value' => config('thha.working_hours'),  'group' => 'hoa_info'],
            ['key' => 'email',          'value' => config('thha.email'),          'group' => 'hoa_info'],
            ['key' => 'website',        'value' => config('thha.website'),        'group' => 'hoa_info'],

            // Registration
            ['key' => 'allow_admin_registration',    'value' => '0', 'group' => 'auth'],
            ['key' => 'allow_resident_registration', 'value' => '1', 'group' => 'auth'],
            ['key' => 'auto_accept_registration',    'value' => '0', 'group' => 'auth'],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
