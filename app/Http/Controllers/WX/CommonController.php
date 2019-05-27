<?php
/**
 * CommonController
 * User: Viker
 * Date: 2019/5/22 16:41
 */


namespace App\Http\Controllers\WX;


use App\Models\Config;
use App\Models\WX;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class CommonController {


	/**
	 * @api
	 * @name    接收微信公众号发送的消息
	 * @url     /wx
	 * @method  POST
	 * @desc
	 */
	public function index(Request $req) {
		//1.获取到微信推送过来post数据（xml格式）
		$msg = $req->getContent();
		$msgObj = simplexml_load_string($msg, 'SimpleXMLElement', LIBXML_NOCDATA);
		\Log::info('=========接收微信公众号发送的消息=========='.json_encode($msgObj));

		// 回复消息模板
		$template = "<xml>
			 <ToUserName><![CDATA[%s]]></ToUserName>
			 <FromUserName><![CDATA[%s]]></FromUserName>
			 <CreateTime>%s</CreateTime>
			 <MsgType><![CDATA[%s]]></MsgType>
			 <Content><![CDATA[%s]]></Content>
			 </xml>";
		$toUser = $msgObj->FromUserName;
		$fromUser = $msgObj->ToUserName;
		$time = time();

		//判断该数据包是否是订阅的事件推送
		switch (strtoupper($msgObj->MsgType)) {
			// 事件
			case 'EVENT':
				//如果是关注 subscribe 事件
				if (strtoupper($msgObj->Event) == 'SUBSCRIBE') {
					// 回复消息
					$msgType = 'text';
					$content = '我说你听，你说我听。 \n 感谢你长得这么好看还关注我，真的是受宠若惊^_^ \n 我是鹿先森，一个平时喜欢写写字，看看书的文字工作者。 \n 不定期更新，如果你不想错过，那就置顶我呀，别担心，我不恐高。 \n 点击进入未聊：http://120.79.174.159/#/find ';
					$res = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
					echo $res;
				}
				break;

			// 文本消息
			case 'TEXT':
				// 回复消息
				$msgType = 'text';
				$content = $msgObj->Content;
				$res = sprintf($template, $toUser, $fromUser, $time, $msgType, $content);
				echo $res;
				break;

			default:
				echo 'success';
				break;
		}

		return;
	}


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

		$token = Config::where('key', 'wx_token')->value('value') ?: 'vchat';
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