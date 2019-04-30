<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Moment;
use App\Models\Sms;
use App\Models\User;
use App\Validators\CommonValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

	/**
	 * @api
	 * @name    注册
	 * @url     /api/user/register
	 * @method  POST
	 * @desc    先调用验证码接口
	 * @param username  string  [必填]  用户名
	 * @param phone     string  [必填]  手机号
	 * @param password  string  [必填]  密码
	 * @param smsCode   string  [必填]  手机验证码
	 */
	public function register(Request $req)
	{
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'register');
		if (true !== $valid) {
			return error('01', $valid->first());
		}
		// 验证手机号是否已注册
		if (User::where('phone', $form['phone'])->count() > 0) {
			return error('01', '手机号已注册');
		}
		// 短信验证码
		if (!Sms::check($form['phone'], $form['smsCode'], 0)) {
			return error('01', '短信验证码错误');
		}

		// 存入数据库
		$user = new User();
		$user->username = $form['username'];
		$user->vchat_id = getVChatID();
		$user->phone = $form['phone'];
		$user->password = Hash::make($form['password']);
		isset($form['avatar']) ? $user->avatar = $form['avatar'] : '';

		if ($user->save()) {
			// 新用户默认发布一条新记忆
			$moment = new Moment();
			$moment->uid = $user->id;
			$moment->content = '您出生了';
			$moment->save();
			return api('00', ['id' => $user->id]);
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    登录
	 * @url     /api/user/login
	 * @method  POST
	 * @desc    登录
	 * @param   phone     string  [必填]  手机号
	 * @param   password  string  [必填]  密码
	 */
	public function login(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'login');
		if (true !== $valid) {
			return error('01', $valid->first());
		}
		// 验证用户
		$user = User::where('phone', $form['phone'])->first();
		if (!$user) {
			return error('01', '用户不存在');
		}
		if (!$user->status) {
			return error('01', '账户已冻结');
		}

		try {
			$token = JWTAuth::attempt([
				"phone" => $form['phone'],
				"password" => $form['password'],
			]);
			if (!$token) {
				throw new \Exception('密码错误');
			}
		} catch (\Exception $e) {
			return error("500", $e->getMessage());
		}

		return api('00', ['token' => $token]);
	}


	/**
	 * @api
	 * @name    忘记密码
	 * @url     /api/user/forgetPassword
	 * @method  POST
	 * @desc    请先调用sms
	 * @param   password  string  [必填]  新的密码
	 * @param   phone     string  [必填]  手机号
	 * @param   smsCode   string  [必填]  手机验证码
	 */
	public function forgetPassword(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'forgetPassword');
		if (true !== $valid) {
			return error('01', $valid->first());
		}
		// 验证手机号是否已注册
		$user = User::where('phone', $form['phone'])->first();
		if (!$user) {
			return error('01', '手机号未注册');
		}
		// 验证短信验证码
		if (!Sms::check($form['phone'], $form['smsCode'], 1)) {
			return error('01', '短信验证码错误');
		}

		// 更新密码
		$user->password = Hash::make($form['password']);
		if ($user->save()) {
			return api('00');
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    用户信息
	 * @url     /api/user/info
	 * @method  POST
	 * @desc    用户信息
	 * @param   id          string  [选填]  用户ID，不传则表示获取自己的用户信息
	 */
	public function info(Request $req) {
		$form = $req->all();
		// 如果没传ID则获取自己的用户信息
		if (empty($form['id'])) {
			$user = $req->userInfo;
		} else {
			// 获取其他用户的信息
			$user = User::find($form['id']);
			if (empty($user)) {
				return error('01', '未找到该用户');
			}
			// 是否是好友
			$user->is_contact = Contact::where(['from_uid' => $req->userInfo->id, 'to_uid' => $user->id])->count();
		}
		return api('00', $user);
	}


	/**
	 * @api
	 * @name    搜索用户
	 * @url     /api/user/search
	 * @method  POST
	 * @desc
	 * @param   search       string  [必填]  VChat唯一标识ID/手机号
	 * @param   page         string  [选填]  当前页数 不传默认1
	 * @param   pageNum      string  [选填]  每页显示数量 不传默认15
	 */
	public function search(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'search');
		if (true !== $valid) {
			return error('01', $valid->first());
		}
		// 查找用户
		$paginate = User::where('phone', 'like', "{$form['search']}%")->orWhere('vchat_id', 'like', "{$form['search']}%")->paginate($req->pageNum);
		$data = $paginate->items();
		$addition = getAddition($paginate);

		return api('00', $data, $addition);
	}


	/**
	 * @api
	 * @name    编辑用户
	 * @url     /api/user/editUserInfo
	 * @method  POST
	 * @desc    编辑用户
	 * @param   avatar          object  [选填]  头像
	 * @param   username        string  [选填]  昵称
	 * @param   sex             string  [选填]  性别 0男 1女
	 * @param   signature       string  [选填]  个性签名
	 * @param   area            string  [选填]  地区
	 * @param   momentBgi       string  [选填]  朋友圈背景图
	 */
	public function editUserInfo(Request $req) {
		$user = User::find($req->userInfo->id);
		$avatar = $req->file('avatar');
		$momentBgi = $req->file('momentBgi');
		$form = $req->all();
		$hasChange = false;

		// 头像
		if (!empty($avatar)) {
			$fileName = saveFile($avatar);
			if (!$fileName) {
				return error('500', '上传头像失败');
			}
			$user->avatar = $fileName;
			if ($user->save()) {
				return api('00');
			}
		}

		// 朋友圈背景图
		if (!empty($momentBgi)) {
			$file = saveFile($momentBgi);
			if (!$file) {
				return error('500', '上传朋友圈背景图失败');
			}
			$user->moment_bgi = $file;
			if ($user->save()) {
				return api('00');
			}
		}

		// 用户名
		if (!empty($form['username'])) {
			$user->username = $form['username'];
			if ($user->save()) {
				$hasChange = true;
			} else {
				$hasChange = false;
			}
		}

		// 性别
		if (isset($form['sex'])) {
			if (!is_numeric($form['sex'])) {
				return error('01', '性别格式错误');
			}
			$user->sex = $form['sex'];
			if ($user->save()) {
				$hasChange = true;
			} else {
				$hasChange = false;
			}
		}

		// 地区
		if (!empty($form['area'])) {
			$user->area = $form['area'];
			if ($user->save()) {
				$hasChange = true;
			} else {
				$hasChange = false;
			}
		}

		// 个性签名
		if (isset($form['signature'])) {
			if (strlen($form['signature']) > 50) {
				return error('01', '个性签名不能超过50个字');
			}
			$user->signature = $form['signature'];
			if ($user->save()) {
				$hasChange = true;
			} else {
				$hasChange = false;
			}
		}

		if ($hasChange) {
			return api('00');
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    修改密码
	 * @url     /api/user/editPassword
	 * @method  POST
	 * @desc
	 * @param   oldPassword  string  [必填]  旧密码
	 * @param   password  string  [必填]  新密码
	 */
	public function editPassword(Request $req) {
		$form = $req->all();
		// 验证
		$valid = CommonValidator::handle($form, 'editPassword');
		if (true !== $valid) {
			return error('01', $valid->first());
		}
		// 验证用户旧密码是否正确
		$user = User::find($req->userInfo->id);
		if (!Hash::check($form['oldPassword'], $user->password)) {
			return error('01', '原密码错误');
		}
		// 更新
		$user->password = Hash::make($form['password']);
		if ($user->save()) {
			return api('00');
		}
		return error('500');
	}


	/**
	 * @api
	 * @name    退出登录
	 * @url     /api/user/logout
	 * @method  POST
	 * @desc    ( 只能在登录状态下调用 )
	 */
	public function logout() {
		try {
			$old_token = JWTAuth::getToken();
			JWTAuth::setBlacklistEnabled(true);
			JWTAuth::invalidate($old_token);
		} catch (\Exception $e) {
			return api("500", $e->getMessage());
		}
		return api("00");
	}


  /**
   * @api
   * @name    获取头像
   * @url     /api/user/avatar
   * @method  POST
   * @desc
   * @param   phone      string  [必填]  手机号
   */
  public function avatar(Request $req) {
    $form = $req->all();
    // 验证
    $valid = CommonValidator::handle($form, 'phone');
    if (true !== $valid) {
      return error('01', $valid->first());
    }
    // 查找用户
    $data = User::select('avatar')->where('phone', $form['phone'])->first();
    return api('00', $data);
  }


}
