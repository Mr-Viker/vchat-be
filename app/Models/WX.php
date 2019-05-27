<?php
/**
 * WX
 * User: Viker
 * Date: 2019/5/27 11:47
 */


namespace App\Models;


class WX {

	/**
	 * 获取微信调用各接口的凭证
	 * @return bool|mixed
	 */
	public static function getAccessToken() {
		$config = Config::getConfig();
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$config['appid']}&secret={$config['secret']}";

		$data = req($url, 'GET');
		$record = Config::where('key', 'access_token')->first();
		if ($record) {
			$record->value = $data['access_token'];
		} else {
			$record = new Config();
			$record->key = 'access_token';
			$record->value = $data['access_token'];
			$record->remark = '微信调用各接口凭证';
		}

		if ($record->save()) {
			return $data['access_token'];
		}
		return false;
	}

}