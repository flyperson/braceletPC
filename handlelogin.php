<?php
header("Content-Type:application/json");

require_once "conn.php";

$flag = isset($_REQUEST["flag"])?$_REQUEST["flag"]:"";

switch($flag){
	case "HandleLogin":
		$un = isset($_GET["un"]) ? $_GET["un"] : "";
		$psw = isset($_GET["psw"]) ? $_GET["psw"] : "";
		$sql = "SELECT id FROM `logindata` WHERE `loginname`='".$un."' AND `loginpws`='".$psw."'";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			//保存到session中;
			session_start();
			$_SESSION["un"] = $un;
			
			$state = TRUE;
			$message = "登录成功";
		}else{
			$message = "账号或密码错误";
		}
		break;
	
	case "JudgeLogin":	
		session_start();
		if(isset($_SESSION["un"])){
			$state = TRUE;
			$message = "用户已登录";
		}else{
			$message = "未登录";
		}
		
		break;
		
	default:
		$message = "没有对应的flag";
		break;
}



$state = isset($state)?$state:FALSE;
$message = isset($message)?$message:"服务器错误";
$retdata = isset($retdata)?$retdata:'""';

$json = '{"state":"'.$state.'","message":"'.$message.'","data":'.$retdata.'}';
echo $json;
?>