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

$factory->define(App\Models\User::class, function (Faker $faker) {
	return [
		'username' => $faker->name,
		'vchat_id' => getVChatID(),
		'phone' => $faker->unique()->phoneNumber,
		'password' => '$2y$10$heBaHkFje5uAJGKrM3YVWOY.huA2Yp5oQg9D2XCyEiQT0gRpQqdnK', // 123123
	];
});
