<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sms;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;

class SmsController extends Controller {

  /**
   * @api
   * @name    发送验证码
   * @url     /api/sms
   * @method  POST
   * @desc    验证码发送
   * @param   phone     string  [必填]  手机号
   * @param   type      string  [必填]  类型  0注册 1忘记密码
   */
  public function sendSms(Request $req) {
    $form = $req->all();

    // 验证
    $valid = CommonValidator::handle($form, 'sms');
    if (true !== $valid) {
      return error('01', $valid->first());
    }

    $code = rand(100000, 999999);
    $res = Sms::send($form['phone'], $code);

    // 如果成功则保存
    if (isset($res->result) && $res->result->err_code == '0') {
      $sms= new Sms();
      $sms->phone = $form['phone'];
      $sms->code = $code;
      $sms->type = $form['type'];
      $sms->status = 0;
      $sms->result = json_encode($res->result);
      if ($sms->save()) {
        return api('00');
      } else {
        return error('500');
      }
    }
    return error('300', $res->sub_msg);
  }
  
}