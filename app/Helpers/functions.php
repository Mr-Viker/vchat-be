<?php

/**
 * 公共方法
 */


// 通用接口返回方法
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

