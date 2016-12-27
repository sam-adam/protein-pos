<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name'           => $faker->name,
        'username'       => $faker->unique()->userName,
        'password'       => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Models\Branch::class, function (Faker\Generator $faker) {
    return [
        'name'                 => $faker->company,
        'address'              => $faker->address,
        'contact_person_name'  => $faker->name,
        'contact_person_phone' => $faker->phoneNumber,
    ];
});

$factory->define(App\Models\Customer::class, function (Faker\Generator $faker) {
    $branch  = \App\Models\Branch::orderBy(\Illuminate\Support\Facades\DB::raw('RAND()'))->first();
    $creator = \App\Models\User::orderBy(\Illuminate\Support\Facades\DB::raw('RAND()'))->first();

    return [
        'name'                 => $faker->name,
        'phone'                => $faker->phoneNumber,
        'email'                => $faker->email,
        'registered_branch_id' => $branch->id,
        'address'              => $faker->address,
        'created_by'           => $creator->id
    ];
});