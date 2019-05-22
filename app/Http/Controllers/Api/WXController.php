<?php
/**
 * WXController
 * User: Viker
 * Date: 2019/5/22 16:41
 */


namespace App\Http\Controllers\Api;


use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class WXController {

	/**
	 * @api
	 * @name    验证微信服务器
	 * @url     /api/wx
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

		$data = [$form['timestamp'], $form['nonce']];
		sort($data, SORT_STRING);
		$dataStr = sha1(implode($data));

		if ($form['signature'] == $dataStr) {
			return $form['echostr'];
		}
		return 'fail';
	}

}