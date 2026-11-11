<?php

use App\Models\Resident;
use Illuminate\Database\Seeder;

/**
 * The association's profiled population: 5000 random residents.
 *
 * These are people only — a resident may or may not hold a portal account.
 * HomeownerSeeder issues the accounts against them, so run this first.
 */
class ResidentSeeder extends Seeder
{
    /** Number of residents to create. */
    const COUNT = 99;

    /** Rows per insert batch. */
    const CHUNK = 500;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bar = $this->command->getOutput()->createProgressBar(self::COUNT);
        $bar->start();

        for ($created = 0; $created < self::COUNT; $created += self::CHUNK) {
            $batch = min(self::CHUNK, self::COUNT - $created);

            factory(Resident::class, $batch)->create();
            $bar->advance($batch);
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info(self::COUNT . ' residents seeded successfully.');
    }
}
