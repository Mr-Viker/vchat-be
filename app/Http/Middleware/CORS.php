<?php
namespace App\Http\Middleware;


class CORS {

  public function handle($req, \Closure $next) {
    header('Access-Control-Allow-Origin: *');
    // header("Access-Control-Allow-Credentials: true"); // 不能和上面的*同时设置
    header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
    // 允许前端在接口中传递的header字段
    header("Access-Control-Allow-Headers: Content-Type, Cache-Control, Authorization, x-requested-with, X-Requested_With, Access-Token, token, authorization");
    header("Access-Control-Expose-Headers: *");

    if ($req->method() == "OPTIONS") {
      return error('200', 'OK');
    }

    return $next($req);
  }

}
