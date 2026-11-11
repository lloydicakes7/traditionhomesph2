<?php

use Illuminate\Database\Seeder;
use App\Models\Address;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaults = [
            [
                'label'       => 'TH 2',
                'street'      => '',
                'subdivision' => 'Tradition Homes Ph-2',
                'barangay'    => 'Inocencio',
                'region'      => 'Region IV-A (CALABARZON)',
                'city'        => 'Trece Martires City',
                'province'    => 'Cavite',
                'zip_code'    => '4109',
                'country'     => 'Philippines',
                'is_primary'  => true,
            ],
        ];

        foreach ($defaults as $address) {
            Address::firstOrCreate(['label' => $address['label']], $address);
        }
    }
}
