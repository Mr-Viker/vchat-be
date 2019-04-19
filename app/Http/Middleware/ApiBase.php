<?php
namespace App\Http\Middleware;

use Tymon\JWTAuth\Facades\JWTAuth;


class ApiBase {

  public function handle($req, \Closure $next) {
    $jwt = $req->header('token');
    
    if (isset($jwt) && $jwt != 'null') {
      // 解密token并将用户信息赋值给$req->userInfo
	    $req->userInfo = JWTAuth::parseToken("bearer", "token")->authenticate();
    }

    return $next($req);
  }

}