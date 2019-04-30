<?php


namespace App\Http\Controllers\Api;


use App\Models\Like;
use App\Models\Moment;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class LikeController
{
  /**
   * @api
   * @name    点赞记忆
   * @url     /api/like/add
   * @method  POST
   * @desc
   * @param   id       string  [选填]  记忆ID
   */
  public function add(Request $req) {
    $form = $req->all();
    // 验证
    $valid = CommonValidator::handle($form, 'id');
    if (true !== $valid) {
      return error('01', $valid->first());
    }
    // 查看是否有该记忆
    if (Moment::where('id', $form['id'])->count() <= 0) {
      return error('01', '记忆不存在哦');
    }
    // 验证是否已经点赞过了
    if (Like::where(['mid' => $form['id'], 'uid' => $req->userInfo->id])->count() > 0) {
      return error('01', '不能重复点赞哦');
    }

    $like = new Like();
    $like->mid = $form['id'];
    $like->uid = $req->userInfo->id;
    if ($like->save()) {
      return api('00');
    }
    return error('500');
  }


  /**
   * @api
   * @name    取消点赞记忆
   * @url     /api/like/del
   * @method  POST
   * @desc
   * @param   id       string  [必填]  记忆ID
   */
  public function del(Request $req) {
    $form = $req->all();
    // 验证
    $valid = CommonValidator::handle($form, 'id');
    if (true !== $valid) {
      return error('01', $valid->first());
    }
    // 查看是否有该记忆
    if (Moment::where('id', $form['id'])->count() <= 0) {
      return error('01', '记忆不存在哦');
    }
    // 验证是否已经点赞过了
    $like = Like::where(['mid' => $form['id'], 'uid' => $req->userInfo->id])->first();
    if (empty($like)) {
      return error('01', '你还未点赞哦');
    }

    if ($like->delete()) {
      return api('00');
    }
    return error('500');
  }

}
