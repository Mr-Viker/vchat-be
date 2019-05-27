<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
	protected $table = 'config';

	// 获取前台需要的配置信息
	public static function getWebConfig() {
		$key = ['app_version', 'app_update_tip', 'app_update_url', 'system_name'];
		$data = Config::whereIn('key', $key)->pluck('value', 'key');
		return $data;
	}


	// 获取所有配置信息
	public static function getConfig() {
		$config = [
			// 系统设置
			'system_name' => 'VChat',
			'custom_qrcode' => '',
			'app_version' => '1.0.0',
			'app_update_tip' => 'App升级提示',
			'app_update_url' => '',
			// 短信设置
			'sms_key' => '23877387',
			'sms_secret' => '3ea296e94a8695fde292323b52d72041',
			'sms_signName' => '拼团验证',
			'sms_templateCode' => 'SMS_60475099',
			// 微信
			'appid' => 'wx7b4df9dc23ccfc07',
			'secret' => 'c3171ce60cdc1c3eeb09efa7e511866d',
		];

		Config::all()->map(function($item) use (&$config) {
			$config[$item->key] = $item->value;
		});
		return $config;
	}

}
