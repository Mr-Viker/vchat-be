<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
  protected $table = 'moment';

  public function user() {
    return $this->belongsTo(User::class, 'uid', 'id');
  }

  public function like() {
    return $this->hasMany(Like::class, 'mid', 'id');
  }

  public function comment() {
    return $this->hasMany(Comment::class, 'mid', 'id');
  }
}
