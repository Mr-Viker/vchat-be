<?php


namespace App\Http\Controllers\Api;


use App\Models\Comment;
use App\Models\Moment;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class CommentController
{
  /**
   * @api
   * @name    评论列表
   * @url     /api/comment/list
   * @method  POST
   * @desc
   * @param   id       string  [必填]  记忆ID
   * @param   page     string  [选填]  当前页数 不传默认1
   * @param   pageNum  string  [选填]  每页显示数量 不传默认15
   */
  public function list(Request $req) {
    $form = $req->all();
    // 验证
    $valid = CommonValidator::handle($form, 'idList');
    if (true !== $valid) {
      return error('01', $valid->first());
    }

    $paginate = Comment::where('mid', $form['id'])->with(['from_user:id,username,avatar', 'to_user:id,username,avatar'])->orderBy('created_at', 'desc')->paginate($req->pageNum);
    $data = $paginate->items();
    $addition = getAddition($paginate);

    return api('00', $data, $addition);
  }


  /**
   * @api
   * @name    添加评论
   * @url     /api/comment/add
   * @method  POST
   * @desc
   * @param   id       string  [必填]  记忆ID
   * @param   content  string  [必填]  评论内容
   * @param   toUid    string  [选填]  被评论的用户ID, 不传表示是一级评论
   */
  public function add(Request $req) {
    $form = $req->all();
    // 验证
    $valid = CommonValidator::handle($form, 'comment');
    if (true !== $valid) {
      return error('01', $valid->first());
    }
    // 查看是否有该记忆
    if (Moment::where('id', $form['id'])->count() <= 0) {
      return error('01', '记忆不存在哦');
    }

    $comment = new Comment();
    $comment->mid = $form['id'];
    $comment->from_uid = $req->userInfo->id;
    isset($form['toUid']) ? $comment->to_uid = $form['toUid'] : '';
    $comment->content = $form['content'];

    if ($comment->save()) {
      $data = Comment::where('id', $comment->id)->with(['from_user:id,username,avatar', 'to_user:id,username,avatar'])->first();
      return api('00', $data);
    }
    return error('500');
  }


  /**
   * @api
   * @name    删除评论
   * @url     /api/comment/del
   * @method  POST
   * @desc
   * @param   id       string  [必填]  评论ID
   */
  public function del(Request $req) {
    $form = $req->all();
    // 验证
    $valid = CommonValidator::handle($form, 'id');
    if (true !== $valid) {
      return error('01', $valid->first());
    }
    // 查看是否有该评论
    $comment = Comment::where('id', $form['id'])->first();
    if (empty($comment)) {
      return error('01', '评论不存在哦');
    }

    if ($comment->delete()) {
      return api('00');
    }
    return error('500');
  }

}
