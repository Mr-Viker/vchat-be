<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
  protected $table = 'comment';

  public function from_user() {
    return $this->belongsTo(User::class, 'from_uid', 'id');
  }

  public function to_user() {
    return $this->belongsTo(User::class, 'to_uid', 'id');
  }
}
