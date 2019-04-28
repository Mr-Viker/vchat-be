<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\Chat::class, function (Faker $faker) {
	return [
		'from_id' => rand(84, 103),
		'to_id' => 1,
    'content' => $faker->sentence,
		'status' => 1,
	];
});
