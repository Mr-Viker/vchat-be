<?php
/**
 * MenuController
 * User: Viker
 * Date: 2019/5/27 10:30
 */


namespace App\Http\Controllers\WX;


use App\Models\Config;
use App\Models\WX;

class MenuController {

	/**
	 * @api
	 * @name    自定义公众号菜单
	 * @url     /wx/menu/create
	 * @method  GET
	 * @desc
	 */
	public function create() {
		$accessToken = Config::where('key', 'access_token')->value('value');
		if (!$accessToken) {
			return error('500', '无法获取access_token');
		}

		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$accessToken}";
		$data = ['button' => [
			['name' => '广场', 'type' => 'view', 'url' => 'http://120.79.174.159/#/find'],
			['name' => '聊天', 'type' => 'view', 'url' => 'http://120.79.174.159'],
			['name' => '我', 'type' => 'view', 'url' => 'http://120.79.174.159/#/person'],
		]];
		$menu = json_encode($data);
		$res = req($url, 'POST', $data);
		return api('00', $res);
	}

}