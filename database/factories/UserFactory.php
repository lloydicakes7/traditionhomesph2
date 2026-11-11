<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Resident;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| User Factory
|--------------------------------------------------------------------------
|
| A homeowner portal account. An account always belongs to a person, so the
| default creates the resident it is issued against; pass an existing
| `resident_id` to link the account to that resident instead.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'resident_id'       => factory(Resident::class),
        'email'             => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'is_active'         => true,
        'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // "password"
        'remember_token'    => Str::random(10),
    ];
});
