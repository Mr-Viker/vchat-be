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
   * @name    上传图片
   * @url     /api/upload
   * @method  POST
   * @desc
   * @param   file       object  [必填]  头像名
   */
  public function upload(Request $req) {
    $file = $req->file('file');

    if (empty($file)) {
      return error('01', '未接收到文件');
    }

	  if (is_array($file)) {
		  $fileName = saveMultiFile($file);
	  } else {
	  	$name = saveFile($file);
	  	$fileName = [$name];
	  }
    if ($fileName) {
      return api('00', $fileName);
    }
    return error('500', '上传失败');
  }


}
