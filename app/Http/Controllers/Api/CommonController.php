<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;

class CommonController extends Controller {

  /**
   * @api
   * @name    配置信息
   * @url     /api/config
   * @method  POST
   * @desc    vip套餐列表
   */
  public function config(Request $req) {
    $data = Config::getWebConfig();
    return api('00', $data);
  }

}