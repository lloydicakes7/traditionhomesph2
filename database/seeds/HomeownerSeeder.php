<?php

use App\Models\Resident;
use App\Models\User;
use App\Utility\TextHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HomeownerSeeder extends Seeder
{
    const COUNT = 100;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = config('account.homeowner.password');

        if (empty($password)) {
            $this->command->error('HOMEOWNER_PASSWORD must be set in .env');
            return;
        }

        $residents = Resident::query()
            ->whereDoesntHave('user')
            ->inRandomOrder()
            ->limit(self::COUNT)
            ->get();

        if ($residents->isEmpty()) {
            $this->command->info('Every resident already holds a portal account.');
            return;
        }

        $bar = $this->command->getOutput()->createProgressBar($residents->count());
        $bar->start();

        foreach ($residents as $resident) {
            $resident->update(self::upperCaseProfile($resident));

            factory(User::class)->create([
                'resident_id' => $resident->id,
                'email'       => self::emailFor($resident->id),
                'password'    => Hash::make($password),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->command->line('');
        $this->command->info($residents->count() . ' homeowner accounts seeded successfully.');
    }

    /**
     * The profile text as the association stores it.
     *
     * @return array<string, string|null>
     */
    private static function upperCaseProfile(Resident $resident): array
    {
        $fields = [
            'surname', 'first_name', 'middle_name', 'birth_place',
            'religion', 'citizenship', 'occupation', 'spouse_name',
        ];

        $values = [];

        foreach ($fields as $field) {
            $values[$field] = TextHelper::upper($resident->{$field});
        }

        return $values;
    }

    /**
     * The account address a resident is seeded with. Derived from the resident
     * so seeding twice never collides on `users.email`.
     */
    private static function emailFor(int $residentId): string
    {
        return 'homeowner' . $residentId . '@gmail.com';
    }
}
