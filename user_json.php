<?php
header("Charset:UTF-8");
header("Content-Type:application/json");

require_once "conn.php";


$flag = isset($_REQUEST["flag"])? $_REQUEST["flag"] : "";

switch($flag){
	case "GetUserMessage" : 
		$sql = "SELECT `id`,`userphone`,`username`,`regisrtationdate`,`usergroup`,`userage`,`mac` FROM `user`";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmpdata = "";
			while($row = $result->fetch_row()){
				$regisrtationdate = date("Y-m-d",$row[3]);
//				$tmpdata .= ','.'{"userphone":"'.$row[1].'","username":"'.$row[2].'","regisrtationdate":"'.date("Y-m-d",$row[3]).'","usergroup":"'.$row[4].'"}'; 
				$tmpdata .= ','.'{"userid":"'.$row[0].'","userphone":"<a data-toggle=\"modal\" href=\"#SelfMessage\" name=\"'.$row[0].'\" onclick=\"CheckMessage(this)\" >'.$row[1].'</a>","username":"'.$row[2].'","regisrtationdate":"'.$regisrtationdate.'","usergroup":"'.$row[4].'","userage":"'.$row[5].'","usermac":"'.$row[6].'","handle":"<button class=\"btn btn-primary\" data-toggle=\"modal\" href=\"#JudgeOxygen\" name=\"'.$row[0].'\" onclick=\"JudgeOxygen(this)\">有氧判断</button>"}'; 
			}
			$tmpdata = substr($tmpdata, 1);
			$redata = '['.$tmpdata.']';
			$state = 1;
			$message = "获取成功";
		}else{
			$message = "获取失败";
		}
		break;
		
	case "GetOneselfData" :
		$selfID = $_GET["selfID"];
		
		if(!empty($selfID)){
			$sql = "SELECT `username`,`userphone`,`userpassword`,`usergroup`,`mac` FROM `user` WHERE `id`='".$selfID."' LIMIT 1";
			$result = $conn->query($sql);
			if($result->num_rows>0){
				while($row=$result->fetch_row()){
//					$redata = '{"username":"'.$row[0].'","userphone":"'.$row[1].'","userpassword":"'.$row[2].'","usergroup":"'.$row[3].'"}';
					$redata = '["'.$row[0].'","'.$row[1].'","'.$row[2].'","'.$row[3].'","'.$row[4].'","'.$selfID.'"]';
				}
				$state = 1;
				$message = "获取成功";
			}
		}else{
			$message = "获取失败";
		}
		
		break;
	
	case "UpdateUserData":
		$data = $_POST["data"];
		if(!empty($data[2])){
			$sql = "UPDATE `user` SET `username`='".$data[0]."',`usergroup`='".$data[1]."' WHERE `id`='".$data[2]."'";
			if($conn->query($sql)){
				$state = 1;
				$message = "保存成功";
			}else{
				$message = "保存失败";
			}
		}else{
			$message = "用户标志不存在";
		}
		break;
	
	case "RemoveBracelet":
		$userid = $_POST["userid"];
		if(!empty($userid)){
			$sql = "UPDATE `user` SET `mac`='' WHERE `id`='".$userid."'";
			if($conn->query($sql)){
				$state = 1;
				$message = "解除成功";
			}else{
				$message = "服务器错误";
			}
		}else{
			$message = "用户标志不存在";
		}
		break;
		
	default :
		$message = "没有相应的flag";
}


$state = isset($state)?$state:FALSE;
$message = isset($message)?$message:"服务器错误";
$redata = isset($redata)?$redata:'[]';

$json = '{"state":"'.$state.'","message":"'.$message.'","data":'.$redata.'}';
echo $json;

?>