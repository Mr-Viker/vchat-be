<?php

/**
 * 公共方法
 */


// 通用接口返回方法
use App\Models\User;
use Illuminate\Support\Facades\Storage;

if (!function_exists('api')) {
  function api($code = '00', $data = [], $addition = [], $msg = '') { 
    $msgs = ['00' => '操作成功', '01' => '验证不通过', '300' => '第三方错误', '500' => '操作失败'];
    $msg = $msg ?: $msgs[$code];

    return response()->json(array_merge([
      'code' => $code,
      'data' => $data,
      'msg' => $msg,
    ], $addition));
  } 
}


// 通用错误接口返回方法
if (!function_exists('error')) { 
  function error($code = '500', $msg = '') { 
    return api($code, [], [], $msg);
  } 
}


// 随机生成订单号
if (!function_exists('getOrderNo')) { 
  function getOrderNo() { 
    return date('YmdHis') . rand(1000, 9999);
  } 
}


// 生成分页附加数据
if (!function_exists('getAddition')) { 
  function getAddition($paginate) { 
    return [
      'current_page' => $paginate->currentPage(),
      'last_page' => $paginate->lastPage(),
      'per_page' => $paginate->perPage(),
      'total' => $paginate->total(),
    ];
  } 
}


// 组装图片地址
if (!function_exists('to_media')) {
  function to_media($imgUrl) {
    if (stristr($imgUrl, 'http')) {
      return $imgUrl;
    } else {
      return url('upload') . '/' . $imgUrl;
    }
  }
}


// 随机生成VChat唯一ID
if (!function_exists('getVChatID')) {
	function getVChatID() {
		$from = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
		$str = substr(str_shuffle(implode('', $from)), 0, 20);
		return 'vcid_' . $str;
	}
}


// 存储图片
if (!function_exists('saveFile')) {
	function saveFile($file) {
		if ($file->isValid()) {
			$ext = $file->getClientOriginalExtension(); //文件拓展名
			$tempPath = $file->getRealPath(); //临时文件的绝对路径
			$fileName = date('YmdHis') . uniqid() . '.' . $ext; //新文件名
			$bool = Storage::disk('admin')->put($fileName, file_get_contents($tempPath)); //传成功返回bool值
			if (!$bool) {
				return false;
			}
			return $fileName;
		} else {
			return false;
		}
	}
}

// 存储多图
if (!function_exists('saveMultiFile')) {
	function saveMultiFile($files) {
		if (is_array($files)) {
			foreach ($files as $file) {
				$path[] = saveFile($file);
			}
		} else {
			$path = saveFile($files);
		}
		return $path;
	}
}

//格式化聊天列表 只需要获取和每个聊天对象的第一条消息即可
//去重并根据created_at排序
if (!function_exists('formatChatList')) {
  function formatChatList($data, $id)
  {
    $res = [];
    $result = [];

    foreach ($data as $v) {
    	$newChatNum = isset($res[$v['uid']]['new_chat_num']) ? $res[$v['uid']]['new_chat_num'] : 0;
      // 接收的未读消息数 如果is_accept == 1 && $v['is_read'] == 0 表示是自己接收的未读消息数
      if ($v['is_accept'] == 1 && $v['is_read'] == 0) {
        $newChatNum++;
      }
      // 如果$res没有该uid对应的聊天记录或者当前的聊天记录比$res['uid']对应的聊天记录更新 则将当前的聊天记录赋值给$res
      if (!isset($res[$v['uid']]) || strtotime($res[$v['uid']]['created_at']) < strtotime($v['created_at'])) {
        $res[$v['uid']] = $v;
      }
      $res[$v['uid']]['new_chat_num'] = $newChatNum;
    }

    $res = array_values($res);
    //根据created_at排序
    $sortKey = array_column($res, 'created_at');
    array_multisort($sortKey, SORT_DESC, $res);

    foreach ($res as $k => $v) {
      // 获取用户信息
      $user = User::select('username', 'avatar')->where('id', $v['uid'])->first()->toArray();
      $res[$k] = array_merge($v, $user);
      // 如果对方是自己的好友 才显示该聊天记录
	    if (\App\Models\Contact::where(['from_uid' => $id, 'to_uid' => $v['uid']])->count() > 0) {
		    $result[] = $res[$k];
	    }
    }
    return $result;
  }
}


// 格式化聊天记录列表
//if (!function_exists('formatChatList')) {
//  function formatChatList($data) {
//    $newData = array_column($data, Null, 'from_id'); // 以from_id为key的数组
//    $contents = []; // 以from_id为key的内容数组
//    $res = []; //最终结果数组
//
//    foreach ($data as $val) {
//      $contents[$val->from_id][] = ['id' => $val->id, 'msg' => $val->content, 'time' => $val->created_at];
//    }
//
//    foreach ($newData as $key => $item) {
//      unset($item->id);
//      unset($item->content);
//      unset($item->created_at);
//      $item->contents = $contents[$key];
//      $res[] = $item;
//    }
//
//    return $res;
//  }
//}


// 获取数据总计的js
if (!function_exists("get_total_js")) {
  function get_total_js() {
    return <<<EOT
    var myTotalData = {};
    $(function(){
      $(".myTotalData").each(function(){
        myTotalData[$(this).data("tag")] = 0;
      });

      $(".myTotalData").each(function(){
         var number =$(this).html();
         if(number){
            number=number.replace(/<[^>]+>/g,"");
            number = number.replace("+","");
            number = parseFloat(number);
            if(number){
              myTotalData[$(this).data("tag")] += parseFloat(number) ;
            }
         }
      });

      var tableObj = $(".content .box-body.table-responsive.no-padding table.table:first");
      tableObjTr = tableObj.find("tr").eq(1);
      if(tableObj.find('.myTotalDataTr').length <=0 && tableObjTr.length > 0){
        var myTotalHtml = "<tr class='myTotalDataTr' style='background-color: #e1eff9;'>";
        tableObjTr.children('td').each(function(i){
            myTotalHtml += "<td>";
            if( i == 0){
                myTotalHtml+= "<b>本页合计:</b>";
            }else{
                if($(this).find(".myTotalData").length > 0){
                    myTotalHtml+= "<b>"+ myTotalData[$(this).find(".myTotalData").data("tag")].toFixed(2) +"</b>";
                }
            }
            myTotalHtml += "</td>";
        });
        myTotalHtml += "</tr>";
        tableObj.append(myTotalHtml);
      }
    });
EOT;
  }
}

