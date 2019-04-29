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


  /**
   * @api
   * @name    上传头像
   * @url     /api/upload
   * @method  POST
   * @desc
   * @param   avatar       object  [必填]  头像名
   */
  public function upload(Request $req) {
    $avatar = $req->file('avatar');

    if (empty($avatar)) {
      return error('01', '未接收到头像');
    }

    $fileName = saveFile($avatar);
    if ($fileName) {
      return api('00', ['avatar' => $fileName]);
    }
    return error('500', '上传头像失败');
  }


}
