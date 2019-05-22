<?php
/**
 *  通用验证器
 */
namespace App\Validators;

class CommonValidator extends BaseValidator {

  protected $rules = [
    // user
    'phone' => ['required', 'regex: /^1([38][0-9]|4[579]|5[0-3,5-9]|6[6]|7[0135678]|9[89])\d{8}$/'],
    'password' => ['required', 'min:6', 'max:16'],
    'oldPassword' => ['required', 'min:6', 'max:16'],
    'smsCode' => ['required', 'alpha_num'],
	  'username' => ['required', 'min:1', 'max:10'],
	  'search' => ['required'],

    'type' => ['required', 'numeric'],
    'id' => ['required', 'numeric'],
    'clientId' => ['required'],
	  'status' => ['required', 'numeric'],
	  'content' => ['required'],

    // pay
    'payType' => ['required', 'numeric'],
    'money' => ['required', 'numeric', 'min:0'],
    'goal' => ['required', 'boolean'],

    'page' => ['sometimes', 'numeric'],
    'pageNum' => ['sometimes', 'numeric'],

	  // wx
	  'signature' => ['required'],
	  'timestamp' => ['required'],
	  'nonce' => ['required'],
	  'echostr' => ['required'],

  ];

  protected $msgs =[
    // user
    'phone.required' => '手机号不能为空',
    'phone.regex' => '手机号格式错误',
    'password.required' => '密码不能为空',
    'password.min' => '密码长度必须在6位以上',
    'password.max' => '密码长度必须在16位以下',
    'oldPassword.required' => '原密码不能为空',
	  'oldPassword.min' => '原密码长度必须在6位以上',
	  'oldPassword.max' => '原密码长度必须在16位以下',
    'smsCode.required' => '验证码不能为空',
    'smsCode.alpha_num' => '验证码格式错误',
	  'username.required' => '用户名不能为空',
	  'username.min' => '用户名长度必须在6位以上',
	  'username.max' => '用户名长度必须在16位以下',
	  'search.required' => '查询条件不能为空',


    'type.required' => '类型不能为空',
    'type.numeric' => '类型错误',
    'id.required' => 'ID不能为空',
    'id.numeric' => 'ID格式错误',
	  'clientId.required' => 'clientId不能为空',
	  'status.required' => '状态不能为空',
	  'status.numeric' => '状态格式错误',
	  'content.required' => '内容不能为空',


    // pay
    'payType.required' => '类型不能为空',
    'payType.numeric' => '类型格式错误',
    'money.required' => '金额不能为空',
    'money.numeric' => '金额格式错误',
    'money.min' => '金额不能小于最小值',
    'goal.required' => '目的不能为空',
    'goal.boolean' => '目的格式错误',

    'page.numeric' => '页数格式错误',
    'pageNum.numeric' => '每页显示数量格式错误',

	  // wx
	  'signature.required' => '微信加密签名不能为空',
	  'timestamp.required' => '时间戳不能为空',
	  'nonce.required' => '随机数不能为空',
	  'echostr.required' => '随机字符串不能为空',

  ];

  protected $scenes = [
    // user
    'register' => ['phone', 'password', 'smsCode', 'username'],
    'login' => ['phone', 'password'],
    'forgetPassword' => ['phone', 'smsCode', 'password'],
    'editPassword' => ['oldPassword', 'password'],
	  'search' => ['search', 'page', 'pageNum'],
    'phone' => ['phone'],

    'sms' => ['phone', 'type'],

    'id' => ['id'],
    'clientId' => ['clientId'],
	  'edit' => ['id', 'status'],
	  'msg' => ['id', 'content'],
	  'comment' => ['id', 'content'],

	  // moment
	  'content' => ['content'],

	  // chat
	  'idList' => ['id', 'page', 'pageNum'],

	  //

    // pay
    'pay' => ['payType', 'money'],

    'list' => ['page', 'pageNum'],

	  // wx
	  'wx' => ['signature', 'timestamp', 'nonce', 'echostr'],
  ];

}
