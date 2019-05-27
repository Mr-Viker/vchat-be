<?php
/**
 * CommonController
 * User: Viker
 * Date: 2019/5/22 16:41
 */


namespace App\Http\Controllers\Api;


use App\Models\Config;
use App\Models\WX;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class CommonController {

	/**
	 * @api
	 * @name    验证微信服务器
	 * @url     /wx
	 * @method  POST
	 * @desc
	 * @param   signature     string  [必填]  微信加密签名
	 * @param   timestamp      string  [必填]  时间戳
	 * @param   nonce      string  [必填]  随机数
	 * @param   echostr      string  [必填]  随机字符串
	 */
	public function validate(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'wx');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$token = Config::where('key', 'wx_token')->pluck('value') ?: 'vchat';
		$data = [$form['timestamp'], $form['nonce'], $token];
		sort($data, SORT_STRING);
		$dataStr = sha1(implode($data));

		if ($form['signature'] == $dataStr) {
			return $form['echostr'];
		}
		return 'fail';
	}


	/**
	 * @api
	 * @name    获取access_token
	 * @url     /wx/getAccessToken
	 * @method  GET
	 * @desc
	 */
	public function getAccessToken() {
		$accessToken = WX::getAccessToken();
		if ($accessToken) {
			return api('00', $accessToken);
		}
		return error('500', '获取access_token失败');
	}

}