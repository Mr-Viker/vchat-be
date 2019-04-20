<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {
	use Authenticatable, CanResetPassword;

	protected $table = 'user';

	protected $hidden = [
		'password',
	];

	// 通讯录
	public function contact() {
		return $this->belongsToMany(User::class, 'contact', 'from_uid', 'to_uid');
	}

}
