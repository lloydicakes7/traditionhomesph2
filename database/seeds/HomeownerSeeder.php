<?php

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\Sex;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HomeownerSeeder extends Seeder
{
    const COUNT = 99;
    const PASSWORD = "dontshare123!@";

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createExampleHomeowner();

        $residentIds = Resident::query()
            ->whereDoesntHave('user')
            ->inRandomOrder()
            ->limit(self::COUNT)
            ->pluck('id');

        if ($residentIds->isEmpty()) {
            $this->command->info('Every resident already holds a portal account.');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($residentIds->count());
        $bar->start();

        foreach ($residentIds as $residentId) {
            factory(User::class)->create([
                'resident_id' => $residentId,
                'email'       => self::emailFor($residentId),
                'password'    => Hash::make(self::PASSWORD),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info($residentIds->count() . ' homeowner accounts seeded successfully.');
    }

    /**
     * The account address a resident is seeded with. Derived from the resident
     * so seeding twice never collides on `users.email`.
     */
    private static function emailFor(int $residentId): string
    {
        return 'homeowner' . $residentId . '@example.com';
    }

    /**
     * The fixed example homeowner: John Doe, with the person to match.
     */
    private function createExampleHomeowner(): void
    {
        $resident = Resident::firstOrCreate(['mobile_no' => '09170000001'], [
            'surname'                => 'Doe',
            'first_name'             => 'John',
            'middle_name'            => null,
            'suffix'                 => null,
            'birth_date'             => '1990-01-01',
            'birth_place'            => 'Manila',
            'sex'                    => Sex::MALE,
            'civil_status'           => CivilStatus::SINGLE,
            'citizenship'            => 'Filipino',
            'religion'               => 'Roman Catholic',
            'occupation'             => 'Homeowner',
            'mobile_no'              => '09170000001',
            'educational_attainment' => EducationalAttainment::COLLEGE_GRADUATE,
            'profile_photo_path'     => null,
        ]);

        // -----------------------------------------------------------------------
        // Create user account
        // -----------------------------------------------------------------------
        $email    = config('account.homeowner.email');
        $password = config('account.homeowner.password');

        if (empty($email) || empty($password)) {
            $this->command->error('HOMEOWNER_EMAIL and HOMEOWNER_PASSWORD must be set in .env');
            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'resident_id' => $resident->id,
                'password'    => Hash::make($password),
                'is_active'   => true,
            ]
        );
    }
}
