<?php
//header("Content-Type:application/x-xls");
//header("Content-Type:application/vnd.ms-excel");
header("Content-Type:application/octet-stream");
//字符串编码互换utf-8转gbk或gbk转utf-8
function change_encode($str){
	$ret_str = $str;
	$encode = mb_detect_encoding($str);//获取字符串的编码格式
	if($encode == "UTF-8"){
		$ret_str = iconv("UTF-8", "GBK", $str);
	}
	if($encode == "GBK"){
		$ret_str = iconv("GBK","UTF-8",  $str);
	}
	return $ret_str;
}

if(isset($_GET["filepath"])){
	$filepath = $_GET['filepath'];//下载的文件路径
	
	//确认下载的文件的名称是否有需要重命名，如不需要则为原文件名
	$name_arr = explode("/", $filepath);//将文件路径分割成数组
	$filename =isset($_GET["filename"])?$_GET["filename"]:$name_arr[count($name_arr)-1];//下载的文件名
	$filepath = change_encode($filepath);//将中文字段进行转码
	
	if(file_exists($filepath)){//判断文件是否存在
		header('content-disposition:attachment;filename='.$filename);
		header('content-length:'.filesize($filepath));
		readfile($filepath);
	}else{
		echo '<script type="text/javascript">alert("文件已被删除或移动了！");window.close();</script>';
	}
}else{
	echo '<script type="text/javascript">alert("下载失败！");window.close();</script>';
}

?>