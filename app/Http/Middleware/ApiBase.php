<?php
namespace App\Http\Middleware;

use Tymon\JWTAuth\Facades\JWTAuth;


class ApiBase {

  public function handle($req, \Closure $next) {
    $jwt = $req->header('token');
    
    if (!empty($jwt) && $jwt != 'null') {
      // 解密token并将用户信息赋值给$req->userInfo
	    try {
		    $req->userInfo = JWTAuth::parseToken("bearer", "token")->authenticate();
	    } catch(\Exception $e) {
	    	return error('500', $e->getMessage());
	    }
    }

    return $next($req);
  }

}