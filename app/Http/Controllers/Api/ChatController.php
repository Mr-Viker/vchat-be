<?php
/**
 * SocketController
 * User: Viker
 * Date: 2019/4/22 11:21
 */


namespace App\Http\Controllers\Api;


use App\Models\Chat;
use App\Models\User;
use App\Validators\CommonValidator;
use GatewayClient\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController {

	/**
	 * @api
	 * @name    绑定用户ID与客户端ID
	 * @url     /api/chat/bindUid
	 * @method  POST
	 * @desc
	 * @param   id          string  [选填]  用户ID 不传表示自己
	 * @param   clientId    string  [必填]  socket客户端ID
	 */
	public function bindUid(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'clientId');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$uid = empty($form['id']) ? $req->userInfo->id : $form['id'];
		Gateway::bindUid($form['clientId'], $uid);
		return api('00');
	}

	/**
	 * @api
	 * @name    发送消息
	 * @url     /api/chat/send
	 * @method  POST
	 * @desc
	 * @param   id          string  [必填]  用户ID
	 * @param   content     string  [必填]  消息内容
	 * @param   type        string  [选填]  类型 0用户-用户 1用户-群组 2群组-用户 默认0
	 */
	public function send(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'msg');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		// 保存消息
		$chat = new Chat();
		$chat->from_id = $req->userInfo->id;
		$chat->to_id = $form['id'];
		$chat->content = $form['content'];
		$chat->type = isset($form['type']) ? $form['type'] : 0;
		// 如果对方在线则发送给对方
		if (Gateway::isUidOnline($form['id'])) {
			Gateway::sendToUid($form['id'], json_encode(['type' => 'say', 'content' => $form['content']]));
			$chat->status = 1;
		}

		if ($chat->save()) {
			return api('00');
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    获取最新未读消息数
	 * @url     /api/chat/getNewChatNum
	 * @method  POST
	 * @desc
	 */
	public function getNewChatNum(Request $req) {
		$data = Chat::where(['to_id' => $req->userInfo->id, 'is_read' => 0])->count();
		return api('00', $data);
	}


	/**
	 * @api
	 * @name    获取最新未读消息
	 * @url     /api/chat/getNewChat
	 * @method  POST
	 * @desc
	 */
	public function getNewChat(Request $req) {
		DB::beginTransaction();
		try {
			// 将未发送成功的消息更新为发送成功状态
			Chat::where(['to_id' => $req->userInfo->id, 'status' => 0, 'type' => 0])->update(['status' => 1]);

			$data = DB::table('chat')
							->join('user', 'chat.from_id', '=', 'user.id')
							->select('chat.id', 'chat.from_id', 'user.username', 'user.avatar', 'chat.content', 'chat.type', 'chat.created_at')
							->where(['chat.to_id' => $req->userInfo->id, 'chat.is_read' => 0, 'chat.type' => 0])
							->orderBy('chat.updated_at', 'desc')
							->get()->toArray();

			$newData = array_column($data, Null, 'from_id'); // 以from_id为key的数组
			$contents = []; // 以from_id为key的内容数组
			$res = []; //最终结果数组
			foreach ($data as $val) {
				$contents[$val->from_id][] = ['id' => $val->id, 'msg' => $val->content, 'time' => $val->created_at];
			}
			foreach ($newData as $key => $item) {
				unset($item->id);
				unset($item->content);
				unset($item->created_at);
				$item->contents = $contents[$key];
				$res[] = $item;
			}

			DB::commit();
			return api('00', $res);
		} catch(\Exception $e) {
			DB::rollBack();
			return error('500', $e->getMessage());
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    已阅新聊天消息
	 * @url     /api/chat/readChat
	 * @method  POST
	 * @desc
	 * @param   id          string  [必填]  用户ID
	 * @param   type        string  [选填]  类型 0用户-用户 1用户-群组 2群组-用户 默认0
	 */
	public function readChat(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'id');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$type = isset($form['type']) ? $form['type'] : 0;
		// 将与该用户的聊天记录改为已读
		if (Chat::where(['from_id' => $form['id'], 'to_id' => $req->userInfo->id, 'type' => $type, 'status' => 1, 'is_read' => 0])->update(['is_read' => 1])) {
			return api('00');
		}
		return error('500');
	}


}