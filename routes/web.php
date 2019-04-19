<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
	return view('welcome');
});


Route::group(['namespace' => 'Api', 'prefix' => 'api', 'middleware' => ["CORS", "ApiBase"]], function () {
	/**
	 * 不需要登录状态的路由写这里
	 */

	Route::any('/sms', 'SmsController@sendSms');                   // 短信发送接口
	Route::any('/config', 'CommonController@config');              // 配置信息

	Route::any('/user/register', 'UserController@register');       // 注册
	Route::any('/user/login', 'UserController@login');             // 登录
	Route::any('/user/forgetPassword', 'UserController@forgetPassword'); // 忘记密码

	Route::any('/comment/list', 'CommentController@list');            // 评论列表



	/**
	 * 需要登录状态的路由写这里
	 */
	Route::group(['middleware' => ['ApiAuth']], function () {

		Route::any('/user/userInfo', 'UserController@userInfo');          // 用户信息
		Route::any('/user/editUserInfo', 'UserController@editUserInfo');  // 编辑用户信息
		Route::any('/user/editPassword', 'UserController@editPassword');  // 修改密码
		Route::any('/user/logout', 'UserController@logout');              // 退出登录

	});
});

