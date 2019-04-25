<?php
/**
 * ContactController
 * User: Viker
 * Date: 2019/4/20 11:28
 */


namespace App\Http\Controllers\Api;


use App\Models\AddContact;
use App\Models\Contact;
use App\Models\User;
use App\Validators\CommonValidator;
use GatewayClient\Gateway;
use Illuminate\Http\Request;

class ContactController {

	/**
	 * @api
	 * @name    通讯录列表
	 * @url     /api/contact/list
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

		$user = User::find($req->userInfo->id);
		$data = $user->contact()->orderBy('username', 'asc')->get();
//		$data = $paginate->items();
//		$addition = getAddition($paginate);

		return api('00', $data);
	}


	/**
	 * @api
	 * @name    添加好友请求列表
	 * @url     /api/contact/addList
	 * @method  POST
	 * @desc
	 * @param   page     string  [选填]  当前页数 不传默认1
	 * @param   pageNum  string  [选填]  每页显示数量 不传默认15
	 */
	public function addList(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'list');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$user = User::find($req->userInfo->id);
		$paginate = $user->addContact()->orderBy('add_contact.created_at', 'desc')->paginate($req->pageNum);
		$data = $paginate->items();
		$addition = getAddition($paginate);

		return api('00', $data, $addition);
	}

	/**
	 * @api
	 * @name    新的添加好友请求消息数量
	 * @url     /api/contact/getNewAddContactNum
	 * @method  POST
	 * @desc
	 */
	public function getNewAddContactNum(Request $req) {
		$user = User::find($req->userInfo->id);
		$data = $user->addContact()->where('is_read', 0)->count();

		return api('00', $data);
	}


	/**
	 * @api
	 * @name    添加通讯录好友
	 * @url     /api/contact/add
	 * @method  POST
	 * @desc
	 * @param   id          string  [必填]  要添加的用户ID
	 * @param   content     string  [选填]  附加内容
	 */
		public function add(Request $req) {
			$form = $req->all();
			// 验证
			$valid = CommonValidator::handle($form, 'id');
			if (true !== $valid) {
				return error('01', $valid->first());
			}
			// 查找用户
			$user = User::find($form['id']);
			if (empty($user)) {
				return error('01', '该用户不存在');
			}
			// 查询是否已经是好友了
			if (Contact::where(['from_uid' => $req->userInfo->id, 'to_uid' => $form['id']])->count() > 0) {
				return error('01', '对方已经是你的好友了哦');
			}

			// 查询是否已发送过添加请求了
			$addContact = AddContact::where(['from_uid' => $req->userInfo->id, 'to_uid' => $form['id']])->first();
			if (empty($addContact)) {
				$addContact = new AddContact();
				$addContact->from_uid = $req->userInfo->id;
				$addContact->to_uid = $form['id'];
			}
			$addContact->is_read = 0;
			empty($form['content']) ?: $addContact->content = $form['content'];

			// 发送一条推送给对方用户
			$array = array_merge($req->userInfo->toArray(), $addContact->toArray());
			$data = ['type' => 'addContact', 'content' => $array];
			Gateway::sendToUid($addContact->to_uid, json_encode($data));

			if ($addContact->save()) {
				return api('00');
			}
			return error('500');
		}


	/**
	 * @api
	 * @name    已阅新添加通讯录好友请求消息
	 * @url     /api/contact/readAddContact
	 * @method  POST
	 * @desc
	 */
	public function readAddContact(Request $req) {
		if (AddContact::where(['to_uid' => $req->userInfo->id, 'is_read' => 0])->update(['is_read' => 1])) {
			return api('00');
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    修改添加通讯录好友状态
	 * @url     /api/contact/editAddContact
	 * @method  POST
	 * @desc
	 * @param   id          string  [必填]  请求添加通讯录好友的用户ID
	 * @param   status      string  [必填]  状态：1同意  2拒绝
	 */
	public function editAddContact(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'edit');
		if (true !== $valid) {
			return error('01', $valid->first());
		}
		// 查找是否有该消息记录
		$addContact = AddContact::where(['from_uid' => $form['id'], 'to_uid' => $req->userInfo->id, 'status' => 0])->first();
		if (empty($addContact)) {
			return error('01', '未找到该添加好友请求');
		}

		// 更新状态
		$addContact->status = $form['status'];
		if ($addContact->update()) {
			// 如果是同意则要新增通讯录好友记录
			if ($form['status'] == 1) {
				$contact = new Contact();
				$contact->from_uid = $addContact->from_uid;
				$contact->to_uid = $addContact->to_uid;
				$contact->save();
				// 如果对方也没有在自己的通讯录好友里则也要新增好友记录
				if (Contact::where(['from_uid' => $addContact->to_uid, 'to_uid' => $addContact->from_uid])->count() < 1) {
					$contact1 = new Contact();
					$contact1->from_uid = $addContact->to_uid;
					$contact1->to_uid = $addContact->from_uid;
					$contact1->save();
				}

				//发送一条消息给对方 告知对方已同意
				$data = ['type' => 'agreeAddContact', 'data' => ['id' => $addContact->from_uid]];
				Gateway::sendToUid($addContact->from_uid, json_encode($data));
			}
			return api('00');
		}
		return error('500');

	}


	/**
	 * @api
	 * @name    删除通讯录好友
	 * @url     /api/contact/del
	 * @method  POST
	 * @desc
	 * @param   id          string  [必填]  要删除的用户ID
	 */
	public function del(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'id');
		if (true !== $valid) {
			return error('01', $valid->first());
		}
		// 验证是否是好友
		$contact = Contact::where(['from_uid' => $req->userInfo->id, 'to_uid' => $form['id']])->first();
		if (empty($contact)) {
			return error('01', '对方不是你的好友哦');
		}

		// 删除好友
		if ($contact->delete()) {
			return api('00');
		}
		return error('500');
	}


}