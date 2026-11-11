<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Property;
use App\Models\Address;
use App\Enums\PropertyType;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Property Factory
|--------------------------------------------------------------------------
|
| Generates realistic property records with random addresses, types, and
| financial values. Block/Lot are left null in the factory because the
| seeder assigns unique sequential values.
|
*/

$factory->define(Property::class, function (Faker $faker) {
    $types = PropertyType::values();
    $sqm   = $faker->randomFloat(2, 30, 500);

    return [
        'address_id' => Address::inRandomOrder()->first()->id ?? factory(Address::class),
        'sqm'        => $sqm,
        'type'       => $faker->randomElement($types),
        'deposit'    => $faker->randomFloat(2, 5000, 200000),
        'balance'    => $faker->randomFloat(2, 0, 50000),
        'picture'    => null,
    ];
});
