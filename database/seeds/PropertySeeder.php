<?php

use Illuminate\Database\Seeder;
use App\Models\Property;
use App\Models\Address;
use App\Enums\PropertyType;

class PropertySeeder extends Seeder
{
    /**
     * Number of properties to create.
     */
    const COUNT = 999;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $addressCount = Address::count();
        if ($addressCount === 0) {
            $this->command->error('No addresses found. Seed addresses first.');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar(self::COUNT);
        $bar->start();

        $chunkSize = 100;
        $records   = [];

        $types = PropertyType::values();

        for ($i = 1; $i <= self::COUNT; $i++) {
            $block = intdiv($i - 1, 100) + 1;
            $lot   = (($i - 1) % 100) + 1;

            $records[] = [
                'block'      => $block,
                'lot'        => $lot,
                'address_id' => Address::inRandomOrder()->first()->id,
                'sqm'        => round(rand(30, 500) + rand(0, 99) / 100, 2),
                'type'       => $types[array_rand($types)],
                'picture'    => null,
                'created_at' => now()->subDays(rand(0, 365)),
                'updated_at' => now(),
            ];

            if (count($records) >= $chunkSize) {
                Property::insert($records);
                $records = [];
                $bar->advance($chunkSize);
            }
        }

        if (! empty($records)) {
            Property::insert($records);
            $bar->advance(count($records));
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info(self::COUNT . ' properties seeded successfully.');
    }
}
