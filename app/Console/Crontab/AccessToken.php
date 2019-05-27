<?php
/**
 * AccessToken
 * User: Viker
 * Date: 2019/5/27 13:34
 */


namespace App\Console\Crontab;


use App\Models\WX;
use Illuminate\Support\Facades\Log;

class AccessToken {

	/**
	 * 重新获取微信接口调用凭证access_token
	 */
	public static function refresh() {
    Log::info('================== 触发获取微信access_token任务 ====================');
		$accessToken = WX::getAccessToken();
    Log::info('================== 结束获取微信access_token任务: ' . $accessToken . '====================');
	}

}