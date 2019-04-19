<?php
namespace App\Http\Middleware;

use Tymon\JWTAuth\Facades\JWTAuth;


class ApiAuth {

  public function handle($req, \Closure $next) {
    if (empty($req->userInfo)) {
      return error('401', '用户未登录');
    }

    return $next($req);
  }

}