<?php
/**
 * ContactController
 * User: Viker
 * Date: 2019/4/20 11:28
 */


namespace App\Http\Controllers\Api;


use App\Models\AddContact;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class ContactController {

	/**
	 * @api
	 * @name    通讯录列表
	 * @url     /api/contact/list
	 * @method  POST
	 * @desc
	 * @param   page     string  [选填]  当前页数 不传默认1
	 * @param   pageNum  string  [选填]  每页显示数量 不传默认15
	 */
	public function list(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'list');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$user = User::find($req->userInfo->id);
		$paginate = $user->contact()->orderBy('username', 'asc')->paginate($req->pageNum);
		$data = $paginate->items();
		$addition = getAddition($paginate);

		return api('00', $data, $addition);
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
		// 查询是否已经是好友了
		if (Contact::where(['from_uid' => $req->userInfo->id, 'to_uid' => $form['id']])->count() > 0) {
			return error('01', '对方已经是你的好友了哦');
		}

		$addContact = new AddContact();
		$addContact->from_uid = $req->userInfo->id;
		$addContact->to_uid = $form['id'];
		empty($form['content']) ?: $addContact->content = $form['content'];

		// 发送一条推送给对方用户


		if ($addContact->save()) {
			return api('00');
		}
		return error('500');
	}

}