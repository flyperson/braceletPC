<?php
/**
 * 得到文件扩展名
 * @param string $filename
 * @return string
 */
function getExt($filename){
	return strtolower(pathinfo($filename,PATHINFO_EXTENSION));
}
//获取路径的相关信息
/*
 * @param string $path
 * @return array index[dirname,basename,filename,extension,lowerextension]
 * */
function get_pathinfo($my_path){
	$info_arr = pathinfo($my_path);
	$info_arr["lowerextension"] = strtolower($info_arr["extension"]);
	return $info_arr;
}

/*毫秒级的时间戳*/
function getMillisecond2() {
	list($t1, $t2) = explode(' ', microtime());
	return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

/*
 * 上传单个文件并返回路径
 * */
function uploadFile($fileInfo,$uploadPath){
	$ext_array = array("jpg","png","gif");
	
	$returndata = "";
	$returndata["msg"] = "";
	$returndata["status"] = FALSE;
	$returndata["destination"] = "";
	
	// 判断错误号
	if ($fileInfo ['error'] > 0) {
		switch ($fileInfo ['error']) {
			case 1 :
				$mes = '上传文件超过了PHP配置文件中upload_max_filesize选项的值';
				break;
			case 2 :
				$mes = '超过了表单MAX_FILE_SIZE限制的大小';
				break;
			case 3 :
				$mes = '文件部分被上传';
				break;
			case 4 :
				$mes = '没有选择上传文件';
				break;
			case 6 :
				$mes = '没有找到临时目录';
				break;
			case 7 :
			case 8 :
				$mes = '系统错误';
				break;
		}
		$returndata["msg"] = $mes;		
	}else{
		//检测文件后缀是否存在
		$ext = pathinfo ( $fileInfo ['name'], PATHINFO_EXTENSION );
		if(in_array($ext, $ext_array)){
			// 检测上传文件大小是否符合规范
			$maxSize = 20971520; // 20M
			if ($fileInfo ['size'] > $maxSize) {
				$returndata["msg"] =  "上传文件过大";
			}else{
				// 检测文件是否是通过HTTP POST方式上传上来
				if (! is_uploaded_file ( $fileInfo ['tmp_name'] )) {
					$returndata["msg"] =  '文件不是通过HTTP POST方式上传上来的';
				}else{
					//检测要存放的路径是否存在，如果不存在就自动创建
					if (! file_exists ( $uploadPath )) {
						mkdir ( $uploadPath, 0777, true );
						chmod ( $uploadPath, 0777 );
					}
					
					$uniName = md5 ( uniqid ( microtime ( true ), true ) ) . '.' . $ext;//加密名称
					$destination = $uploadPath.'/'.$uniName;
					if (! @move_uploaded_file ( $fileInfo ['tmp_name'], $destination )) {
						$returndata["msg"] = '文件保存成功';
					}else{
						$returndata["status"] = TRUE;
						$returndata["destination"] = $destination;
					}
				}
			}
		}else{
			$returndata["msg"] = '文件格式不正确';
		}
	}	
	return $returndata;
}


function Getranking($user_msg,$flag){
	//区分有名次跟名次为0
	$have_ranking = array();
	$none_ranking = array();
	$i = 0;
	$j = 0;
	foreach($user_msg as $ky => $infodata){
		if($infodata[$flag] == "0"){
			$none_ranking[$i] = $infodata;
			$i++;
		}else{
			$have_ranking[$j] = $infodata;
			$j++;
		}
	}
	
	if(!empty($have_ranking)){
		//根据个人排名名次排序
		$arr_len_have = count($have_ranking);
		for($i=0;$i<$arr_len_have-1;$i++){
			for($j=0;$j<$arr_len_have-$i-1;$j++){
				if($have_ranking[$j][$flag] > $have_ranking[$j+1][$flag]){
					$tmp = $have_ranking[$j];
					$have_ranking[$j] = $have_ranking[$j+1];
					$have_ranking[$j+1] = $tmp;
				}
			}
		}
		if(!empty($none_ranking)){
			//根据延续后面的名次
			$arr_len_none = count($none_ranking);
			for($i=0;$i<$arr_len_none;$i++){
				$arr_len_have++;
				$none_ranking[$i][$flag] = $arr_len_have;
			}
			//拼接在一起
			$all_data = array_merge($have_ranking,$none_ranking);
		}else{
			$all_data = $have_ranking;
		}
	}else{
		if(!empty($none_ranking)){
			$arr_len_have = count($have_ranking);
			//根据延续后面的名次
			$arr_len_none = count($none_ranking);
			for($i=0;$i<$arr_len_none;$i++){
				$arr_len_have++;
				$none_ranking[$i][$flag] = $arr_len_have;
			}
			//拼接在一起
			$all_data = $none_ranking;
		}
	}
	
	return $all_data;
}

/*
 * 根据步数获取分数
 * */
function GetScore($stepcount){
	$score = "";
	if($stepcount>=6000){
		$score = $stepcount*0.01;
		if($score > 100){
			$score = 100;
		}
	}else{
		$score = 0;
	}
	return $score;
}

/*
 * 根据整点数及序号生成对应的时间戳
 * */
function Hourtotimestamp($h,$num){
	$daytimestamp_start = strtotime(date("Y-m-d")." ".$h.":00:00");
	switch($num){
		case "0":
			return $daytimestamp_start;
			break;
		case "1":
			return $daytimestamp_start+600;
			break;
		case "2":
			return $daytimestamp_start+1200;
			break;
		case "3":
			return $daytimestamp_start+1800;
			break;
		case "4":
			return $daytimestamp_start+2400;
			break;
		case "5":
			return $daytimestamp_start+3000;
			break;
		default :
			return $daytimestamp_start;
			break;
	}
}
 
 
/*
 * 解析十六进制的心率数据
 * "heartdata":[{"heartrate":"010203040506",timestamp:"10"}]
 * */
function Settle_heartrate($heartdata_x){
	$retdata = "";
	$j = 0;
	foreach($heartdata_x as $index => $datainfo){
		$heart_arr = str_split($datainfo["heartrate"],2);
		$len = count($heart_arr);
		for($i=0;$i<$len;$i++){
			$heart_num = hexdec($heart_arr[$i]);
			if($heart_num > 0){
				$retdata[$j]["heartrate"] = $heart_num;
				$retdata[$j]["timestamp"] = Hourtotimestamp($datainfo["timestamp"],$i);
				$retdata[$j]["timedate"] = date("Y-m-d H:i:s",$retdata[$j]["timestamp"]);
				$j++;
			}
		}
	}
	return $retdata;
}
 

?>