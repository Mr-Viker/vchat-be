<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
	/**
	 * Seed the application's database.
	 *
	 * @return void
	 */
	public function run() {
		// $this->call(UsersTableSeeder::class);

//		factory(User::class, 20)->create()->each(function ($user) {
//			$v = User::find(1);
//			$v->contact()->attach($user);
//			$user->contact()->attach($v);
//		});

//		factory(User::class, 20)->create()->each(function ($user) {
//			$v = User::find(1);
//			$v->addContact()->attach($user, ['content' => '我是' . $user->username]);
//		});
	}
}
