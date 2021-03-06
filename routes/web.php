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
	Route::any('/upload', 'CommonController@upload');              // 上传头像

	Route::any('/user/register', 'UserController@register');       // 注册
	Route::any('/user/login', 'UserController@login');             // 登录
	Route::any('/user/forgetPassword', 'UserController@forgetPassword'); // 忘记密码
	Route::any('/user/avatar', 'UserController@avatar');           // 获取头像


	/**
	 * 需要登录状态的路由写这里
	 */
	Route::group(['middleware' => ['ApiAuth']], function () {

		Route::any('/user/info', 'UserController@info');          // 用户信息
		Route::any('/user/editUserInfo', 'UserController@editUserInfo');  // 编辑用户信息
		Route::any('/user/editPassword', 'UserController@editPassword');  // 修改密码
		Route::any('/user/logout', 'UserController@logout');              // 退出登录
		Route::any('/user/search', 'UserController@search');              // 搜索用户

		Route::any('/contact/list', 'ContactController@list');            // 通讯录列表
		Route::any('/contact/addList', 'ContactController@addList');      // 添加通讯录好友请求列表
		Route::any('/contact/getNewAddContactNum', 'ContactController@getNewAddContactNum');            // 新添加通讯录好友请求数量
		Route::any('/contact/add', 'ContactController@add');              // 添加通讯录好友
		Route::any('/contact/readAddContact', 'ContactController@readAddContact');              // 已阅新添加通讯录好友请求消息
		Route::any('/contact/editAddContact', 'ContactController@editAddContact');              // 修改添加通讯录好友状态
		Route::any('/contact/del', 'ContactController@del');              // 删除通讯录好友

		Route::any('/chat/bindUid', 'ChatController@bindUid');        // 绑定用户ID和客户端ID
		Route::any('/chat/list', 'ChatController@list');              // 聊天列表
		Route::any('/chat/send', 'ChatController@send');              // 发送消息
		Route::any('/chat/getNewChatNum', 'ChatController@getNewChatNum');        // 获取最新未读消息数
		Route::any('/chat/read', 'ChatController@read');              // 已阅新聊天消息
		Route::any('/chat/record', 'ChatController@record');            // 聊天记录
		Route::any('/chat/del', 'ChatController@del');                  // 删除聊天记录

		Route::any('/moment/list', 'MomentController@list');              // 记忆列表
    Route::any('/moment/plaza', 'MomentController@plaza');              // 广场记忆列表
    Route::any('/moment/friend', 'MomentController@friend');            // 好友记忆列表
		Route::any('/moment/info', 'MomentController@info');              // 记忆详情
		Route::any('/moment/add', 'MomentController@add');              // 新增记忆
		Route::any('/moment/del', 'MomentController@del');              // 删除记忆

    Route::any('/like/add', 'LikeController@add');              // 点赞记忆
    Route::any('/like/del', 'LikeController@del');              // 取消点赞

    Route::any('/comment/list', 'CommentController@list');            // 评论列表
    Route::any('/comment/add', 'CommentController@add');              // 新增评论
    Route::any('/comment/del', 'CommentController@del');              // 删除评论
	});
});


Route::group(['prefix' => 'wx', 'namespace' => 'WX'], function () {
//	Route::any('/', 'CommonController@validate');           // 微信认证
	Route::any('/', 'CommonController@index');           // 接收微信公众号发送的消息
	Route::any('/getAccessToken', 'CommonController@getAccessToken');           // 获取access_token
	Route::any('/menu/create', 'MenuController@create');           // 自定义公众号菜单
});

