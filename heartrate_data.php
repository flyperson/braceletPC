<?php
header("Charset:UTF-8");
header("Content-Type:application/json");

require_once "conn.php";


$flag = $_REQUEST["flag"];

$state = 0;
$message = "";
$redata = "";


switch($flag){
	case "GetHeartRateMessage" : 
		$sql = "SELECT userphone,username,usergroup,heart_rate,record_date FROM allheartrate";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmpdata = "";
			while($row = $result->fetch_row()){
				$tmpdata .= ','.'{"userphone":"'.$row[0].'","username":"'.$row[1].'","usergroup":"'.$row[2].'","heart_rate":"'.$row[3].'","record_date":"'.$row[4].'"}';
			}
			$tmpdata = substr($tmpdata, 1);
			$redata = '['.$tmpdata.']';
			$state = 1;
			$message = "获取成功";
		}else{
			$message = "获取失败";
		}
		break;
	
	default :
		$message = "没有相应的flag";
}


if(empty($redata)){
	$redata = '""';
}


$json = '{"state":"'.$state.'","message":"'.$message.'","data":'.$redata.'}';
echo $json;

?>