<?php

use App\Enums\CivilStatus;
use App\Enums\EducationalAttainment;
use App\Enums\Sex;
use App\Models\Resident;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Resident Factory
|--------------------------------------------------------------------------
|
| A profiled person, with or without a portal account. Every required column
| is filled, and the optional ones most of the time, so the lists and reports
| have something to read. Houses are not a resident's concern — a unit is
| reached through their household — so the factory leaves that alone.
|
*/

$factory->define(Resident::class, function (Faker $faker) {
    return [
        'surname'                => $faker->lastName,
        'first_name'             => $faker->firstName,
        'middle_name'            => $faker->optional(0.7)->lastName,
        'suffix'                 => $faker->optional(0.1)->suffix,
        'birth_date'             => $faker->dateTimeBetween('-90 years', '-1 year')->format('Y-m-d'),
        'birth_place'            => $faker->city,
        'sex'                    => $faker->randomElement(Sex::values()),
        'civil_status'           => $faker->randomElement(CivilStatus::values()),
        'spouse_name'            => $faker->optional(0.4)->name,
        'citizenship'            => 'Filipino',
        'religion'               => $faker->optional(0.8)->randomElement([
            'Roman Catholic',
            'Iglesia ni Cristo',
            'Islam',
            'Born Again Christian',
            'Seventh-day Adventist',
        ]),
        'occupation'             => $faker->optional(0.85)->jobTitle,
        'mobile_no'              => $faker->numerify('09#########'),
        'educational_attainment' => $faker->randomElement(EducationalAttainment::values()),
        'profile_photo_path'     => null,
    ];
});
