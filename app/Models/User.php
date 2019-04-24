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

	// 添加通讯录好友
	public function addContact() {
		return $this->belongsToMany(User::class, 'add_contact', 'to_uid', 'from_uid')->withPivot('content');
	}

	// 聊天记录
	public function chat() {
		return $this->hasMany(Chat::class, 'to_id', 'id');
	}

}
