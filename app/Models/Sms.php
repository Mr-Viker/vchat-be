<?php

namespace App\Models;

use Flc\Alidayu\App;
use Flc\Alidayu\Client;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
	protected $table = 'sms';

	// 发送短信
	public static function send($phone, $code) {
		// 配置信息
		$config = Config::getConfig();
		$info = [
			'app_key'    => $config['sms_key'],
			'app_secret' => $config['sms_secret'],
		];

		$client = new Client(new App($info));
		$req    = new AlibabaAliqinFcSmsNumSend;
		$req->setRecNum($phone)
			->setSmsParam(['code' => $code, 'product' => $config['system_name']])
			->setSmsFreeSignName($config['sms_signName'])
			->setSmsTemplateCode($config['sms_templateCode']);

		$res = $client->execute($req);
		\Log::info('================== 发送短信结果 ==================: 手机号：'. $phone . '  验证码：' . $code . '  返回结果：' . json_encode($res));
		return $res;
	}


	// 验证短信验证码
	public static function check($phone, $code, $type = 0) {
		$sms = Sms::where(['phone' => $phone, 'code' => $code, 'type' => $type])->first();
		if ($sms) {
			$sms->status = 1;
			if ($sms->save()) {
				return true;
			}
		}
		return false;
	}
}
