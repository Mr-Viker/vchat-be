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
   * @name    聊天列表
   * @url     /api/chat/list
   * @method  POST
   * @desc
   */
  public function list(Request $req) {
    $form = $req->all();
    // 验证
    $valid = CommonValidator::handle($form, 'list');
    if (true !== $valid) {
      return error('01', $valid->first());
    }

    // 按时间顺序获取和聊过天的用户的记录
    $acceptData = Chat::select('from_id as uid', 'content', 'type', 'is_read', 'created_at')->where('to_id', $req->userInfo->id)->orderBy('created_at', 'desc')->get()->map(function($item) {
      $item->is_accept = 1;
      return $item;
    })->toArray();
    $sendData = Chat::select('to_id as uid', 'content', 'type', 'is_read', 'created_at')->where('from_id', $req->userInfo->id)->orderBy('created_at', 'desc')->get()->map(function($item) {
      $item->is_accept = 0;
      return $item;
    })->toArray();
    $data = array_merge($acceptData, $sendData);

    // 格式化聊天列表 注：只有is_accept == 1 && is_read == 0 才表示是自己的未读消息
    $res = formatChatList($data, $req->userInfo->id);
    return api('00', $res);
  }


	/**
	 * @api
	 * @name    聊天记录
	 * @url     /api/chat/record
	 * @method  POST
	 * @desc
	 * @param   id       string  [必填]  聊天对象的用户ID
	 * @param   page     string  [选填]  当前页数 不传默认1
	 * @param   pageNum  string  [选填]  每页显示数量 不传默认15
	 */
	public function record(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'record');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$temp = Chat::where(['from_id' => $form['id'], 'to_id' => $req->userInfo->id]);
		$paginate = Chat::where(['from_id' => $req->userInfo->id, 'to_id' => $form['id']])->union($temp)->orderBy('created_at', 'desc')->paginate($req->pageNum);
		$data = $paginate->items();
		$addition = getAddition($paginate);
		return api('00', $data, $addition);
	}


	/**
	 * @api
	 * @name    发送消息
	 * @url     /api/chat/send
	 * @method  POST
	 * @desc
	 * @param   id          string  [必填]  接收消息的用户ID
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
		$chat->status = 1;

		if ($chat->save()) {
			// 如果对方在线则发送给对方
			if (Gateway::isUidOnline($form['id'])) {
				$data = ['username' => $req->userInfo->username, 'avatar' => $req->userInfo->avatar, 'uid' => $req->userInfo->id, 'content' => $chat->content, 'created_at' => $chat->created_at->toDateTimeString(), 'is_accept' => 1, 'is_read' => 0, 'new_chat_num' => 1, 'type' => 0];
				Gateway::sendToUid($form['id'], json_encode($data));
			}
			return api('00', $chat);
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
//		$data = Chat::where(['to_id' => $req->userInfo->id, 'is_read' => 0])->count();
		$data = DB::table('chat')->join('contact', 'chat.from_id', '=', 'contact.to_uid')
			->where(['chat.to_id' => $req->userInfo->id, 'chat.is_read' => 0, 'contact.from_uid' => $req->userInfo->id])->count();
		return api('00', $data);
	}


	/**
	 * @api
	 * @name    已阅新聊天消息
	 * @url     /api/chat/read
	 * @method  POST
	 * @desc
	 * @param   id          string  [必填]  用户ID
	 * @param   type        string  [选填]  类型 0用户-用户 1用户-群组 2群组-用户 默认0
	 */
	public function read(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'id');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$type = isset($form['type']) ? $form['type'] : 0;
		// 将与该用户的聊天记录改为已读
		if ($data = Chat::where(['from_id' => $form['id'], 'to_id' => $req->userInfo->id, 'type' => $type, 'status' => 1, 'is_read' => 0])->update(['is_read' => 1])) {
			return api('00', $data);
		}
		return error('500');
	}


}
