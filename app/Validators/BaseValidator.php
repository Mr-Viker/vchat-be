<?php
/**
 * 验证器基类
 */
namespace App\Validators;

use App\Models\User;
use Illuminate\Http\Request;
use Validator;

abstract class BaseValidator {
  /**
   * 验证规则
   *  $rules = [
   *    'phone' => ['required', 'regex: /^\d+$/'],
   *    'password' => ['required'],
   *  ]
   */
  protected $rules = [];
  /**
   * 错误消息
   *  $msgs = [
   *    'phone.required' => '手机号不能为空'
   *  ]
   */
  protected $msgs = [];
  /**
   * 验证场景
   *  $scenes = [
   *    'login' => ['phone', 'password']
   *  ]
   */
  protected $scenes = [];


  /**
   * 处理验证函数
   * @param  [type] $data    需要验证的数据
   * @param  [type] $sceneId 场景ID
   * @return [type]          错误消息数组或者true
   */
  protected function handle($data, $sceneId, $req = '') {
    // empty($req) ?: $this->initCustomRules($req);
    $matchRules = $this->getRules($sceneId);
    $validator = Validator::make($data, $matchRules, $this->msgs);
    if ($validator->fails()) {
      return $validator->errors();
    }
    return true;
  }


  // 初始化自定义规则
  protected function initCustomRules(Request $req) {
    // 修改密码时验证旧密码和数据库的密码是否相同
    Validator::extend('sameOld', function($attr, $value, $params) use ($req) {
      $user = User::find($req->userInfo->id);
      if (!$user) { return false; }
      if (\Hash::check($value, $user->password)) {
        return true;
      }
      return false;
    });
  }


  // 根据传入的场景名来获取所需的rule组成的数组
  protected function getRules($sceneId) {
    $sceneValue = $this->scenes[$sceneId];
    $matchRules = [];
    foreach ($sceneValue as $v) {
      $matchRules[$v] = $this->rules[$v];
    }
    return $matchRules;
  }


  // 伪静态
  public static function __callStatic($method, $args) {
    if (method_exists(static::class, $method)) {
      return (new static())->$method(...$args);
    }
  }

}