<?php
/**
 * MomentController
 * User: Viker
 * Date: 2019/4/30 15:12
 */


namespace App\Http\Controllers\Api;


use App\Models\Moment;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class MomentController {

	/**
	 * @api
	 * @name    记忆列表
	 * @url     /api/moment/list
	 * @method  POST
	 * @desc
	 * @param   id       string  [选填]  记忆的用户ID 不传默认自己
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

		$uid = isset($form['id']) ? $form['id'] : $req->userInfo->id;
		$paginate = Moment::where('uid', $uid)->orderBy('created_at', 'desc')->paginate($req->pageNum);
		$data = $paginate->items();
		$addition = getAddition($paginate);
		foreach ($data as &$item) {
			$item['imgs'] = json_decode($item['imgs'], true);
		}
		return api('00', $data, $addition);
	}


	/**
	 * @api
	 * @name    记忆详情
	 * @url     /api/moment/info
	 * @method  POST
	 * @desc
	 * @param   id       string  [选填]  记忆ID
	 */
	public function info(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'id');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$data = Moment::find($form['id']);
		if ($data) {
			return api('00', $data);
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    创建记忆
	 * @url     /api/moment/add
	 * @method  POST
	 * @desc
	 * @param   content     string  [必填]  记忆内容
	 * @param   imgs        string  [选填]  图片数组地址
	 * @param   address     string  [选填]  定位地址
	 */
	public function add(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'content');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$moment = new Moment();
		$moment->uid = $req->userInfo->id;
		$moment->content = $form['content'];
//		if (!empty($form['imgs'])) {
//			$moment->imgs = saveMultiFile($form['imgs']);
//		}
		isset($form['imgs']) ? $moment->imgs = json_encode($form['imgs']) : '';
		isset($form['address']) ? $moment->address = $form['address'] : '';
		if ($moment->save()) {
			return api('00', ['id' => $moment->id]);
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    删除记忆
	 * @url     /api/moment/del
	 * @method  POST
	 * @desc
	 * @param   id     string  [必填]  记忆ID
	 */
	public function del(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'id');
		if (true !== $valid) {
			return error('01', $valid->first());
		}

		$moment = Moment::find($form['id']);
		if (empty($moment)) {
			return error('01', '没有找到该记忆哦');
		}
		if ($moment->delete()) {
			return api('00');
		}
		return error('500');
	}

}