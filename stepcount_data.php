<?php
header("Charset:UTF-8");
//header("Content-Type:application/json");

require_once "conn.php";
require_once "classes/judge_class.php";


$flag = isset($_REQUEST["flag"])? $_REQUEST["flag"] : "" ;

//$flag = "GetTotalStepcountMessage";

$state = 0;
$message = "";
$redata = "";


switch($flag){
	case "GetStepcountMessage" : 
		$sql = "SELECT userphone,username,usergroup,step_count,praise_number,score,calorie,record_date,isoxygen,user_id,webhandleoxygen FROM allstepcount ORDER BY record_date";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmpdata = "";
			while($row = $result->fetch_row()){
				if($row[10] == '0'){
					$tmpdata .= ','.'{"userphone":"'.$row[0].'","username":"'.$row[1].'","usergroup":"'.$row[2].'","step_count":"'.$row[3].'","praise_number":"'.$row[4].'","score":"'.$row[5].'","calorie":"'.$row[6].'","record_date":"'.$row[7].'","isoxygen":"'.$row[8].'","handle":"<button class=\"btn btn-primary\" data-toggle=\"modal\" href=\"#JudgeOxygen\" selfdate=\"'.$row[7].'\" webhandle=\"0\" name=\"'.$row[9].'\" onclick=\"JudgeOxygen(this)\">有氧判断</button>"}';
				}else{
					$tmpdata .= ','.'{"userphone":"'.$row[0].'","username":"'.$row[1].'","usergroup":"'.$row[2].'","step_count":"'.$row[3].'","praise_number":"'.$row[4].'","score":"'.$row[5].'","calorie":"'.$row[6].'","record_date":"'.$row[7].'","isoxygen":"'.$row[8].'","handle":"<button class=\"btn btn-defaul\" data-toggle=\"modal\" href=\"#JudgeOxygen\" selfdate=\"'.$row[7].'\" webhandle=\"1\" name=\"'.$row[9].'\" onclick=\"JudgeOxygen(this)\">有氧判断</button>"}';
				}
			}
			$tmpdata = substr($tmpdata, 1);
			$redata = '['.$tmpdata.']';
			$state = 1;
			$message = "获取成功";
		}else{
			$message = "获取失败";
		}
		break;
	
	case "GetTotalStepcountMessage" : 
		$sql = "SELECT userphone,username,usergroup,SUM(step_count) AS step_count,SUM(score) AS score,SUM(calorie) AS calorie,record_date FROM allstepcount GROUP BY userphone ORDER BY record_date DESC;";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmpdata = "";
			while($row = $result->fetch_row()){
				$tmpdata .= ','.'{"userphone":"'.$row[0].'","username":"'.$row[1].'","usergroup":"'.$row[2].'","step_count":"'.$row[3].'","score":"'.$row[4].'","calorie":"'.$row[5].'","record_date":"'.$row[6].'"}';
			}
			$tmpdata = substr($tmpdata, 1);
			$redata = '['.$tmpdata.']';
			$state = 1;
			$message = "获取成功";
		}else{
			$message = "获取失败";
		}
		break;
		
	case "GetGroupStepcountMessage" : 
		$sql = "SELECT usergroup,SUM(step_count) AS step_count,SUM(score) AS score,SUM(calorie) AS calorie,record_date,SUM(`score`)/COUNT(`usergroup`) AS average,COUNT(`usergroup`) AS groupnum FROM allstepcount GROUP BY usergroup,record_date  ORDER BY record_date;";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmpdata = "";
			while($row = $result->fetch_row()){
				$tmpdata .= ','.'{"usergroup":"'.$row[0].'","step_count":"'.$row[1].'","score":"'.$row[2].'","calorie":"'.$row[3].'","record_date":"'.$row[4].'","average":"'.round($row[5],2).'","groupnum":"'.$row[6].'"}';
			}
			$tmpdata = substr($tmpdata, 1);
			$redata = '['.$tmpdata.']';
			$state = 1;
			$message = "获取成功";
		}else{
			$message = "获取失败";
		}
		break;
		
	case "GetGroupTotalStepcountMessage" : 
		$sql = "SELECT usergroup,SUM(step_count) AS step_count,SUM(score) AS score,SUM(calorie) AS calorie,MAX(record_date) AS record_date FROM allstepcount GROUP BY usergroup  ORDER BY record_date DESC;";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmpdata = "";
			while($row = $result->fetch_row()){
				$tmpdata .= ','.'{"usergroup":"'.$row[0].'","step_count":"'.$row[1].'","score":"'.$row[2].'","calorie":"'.$row[3].'","record_date":"'.$row[4].'"}';
			}
			$tmpdata = substr($tmpdata, 1);
			$redata = '['.$tmpdata.']';
			$state = 1;
			$message = "获取成功";
		}else{
			$message = "获取失败";
		}
		break;
	
	case "GetJudgeOxygenData":
		$self_id = $_GET["self_id"];
		
		$ret_arr = array(
			"state" => TRUE,
			"message" => "获取成功",
			"data" => array(
				"userphone" => "",
				"userage" => "",
				"userstandardheartrate" => "",
				"isoxygen" => "",
				"heartratedata" => ""
			)
		);
		
		//获取用户的账号，年龄，达标心率
		$sql = "SELECT `userphone`,`userage`  FROM `user` WHERE id='".$self_id."'";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			while($row = $result->fetch_row()){
				$ret_arr["data"]["userphone"] = $row[0];
				$ret_arr["data"]["userage"] = $row[1];
				$ret_arr["data"]["userstandardheartrate"] = round((207-0.7*intval($row[1]))*0.5);
			}
		}
		//获取用户的是否有氧
		$sql = "SELECT `isoxygen` FROM `usersetcount` WHERE `user_id`='".$self_id."' AND `record_date`='".date("Y-m-d")."'";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			while($row = $result->fetch_row()){
				$ret_arr["data"]["isoxygen"] = $row[0];
			}
		}
		//获取用户的已达标的心率数据
		$nowdate = date("Y-m-d");
		$daytimestamp_start = strtotime($nowdate);
		$daytimestamp_end = strtotime($nowdate." 23:59:59");
		$sql = "SELECT `heart_rate`,`record_date` FROM `userheartrate` WHERE `user_id`='".$self_id."' AND  `record_date` BETWEEN '".$daytimestamp_start."' AND '".$daytimestamp_end."' AND `heart_rate`>='".$ret_arr["data"]["userstandardheartrate"]."' ORDER BY `record_date`";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$i = 0;
			while($row = $result->fetch_row()){
				$ret_arr["data"]["heartratedata"][$i]["heartrate"] = $row[0];
				$ret_arr["data"]["heartratedata"][$i]["time"] = date("Y-m-d H:i:s",$row[1]);
				$i++;
			}
		}		
		$state = $ret_arr["state"];
		$message = $ret_arr["message"];
		$redata = json_encode($ret_arr["data"]);
		
		break;
	
	case "GetJudgeOxygenData_SelfDate":
		$self_id = $_GET["self_id"];
		$self_date = $_GET["self_date"];
		
		//进行一次服务器判断
		$sql = "SELECT `userage` FROM `user` WHERE `id`='".$self_id."'";
		$result = $conn->query($sql);
		if($result->num_rows>0){//判断用户是否存在
			//获取用户的年龄
			$userage = 0;
			while($row = $result->fetch_row()){
				$userage = $row[0];
			}
			if($userage != 0){
				//获取有氧判断
				$judgeoxgen = new JudgeAerobicExercise($conn,$self_id,$userage,$self_date);
				$judgeoxgen->UsingClass();
			}
		}
		
		//获取相关信息
		$ret_arr = array(
			"state" => TRUE,
			"message" => "获取成功",
			"data" => array(
				"userphone" => "",
				"userage" => "",
				"userstandardheartrate" => "",
				"isoxygen" => "",
				"heartratedata" => ""
			)
		);
		
		//获取用户的账号，年龄，达标心率
		$sql = "SELECT `userphone`,`userage`  FROM `user` WHERE id='".$self_id."'";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			while($row = $result->fetch_row()){
				$ret_arr["data"]["userphone"] = $row[0];
				$ret_arr["data"]["userage"] = $row[1];
				$ret_arr["data"]["userstandardheartrate"] = round((207-0.7*intval($row[1]))*0.5);
			}
		}
		//获取用户的是否有氧
		$sql = "SELECT `isoxygen` FROM `usersetcount` WHERE `user_id`='".$self_id."' AND `record_date`='".$self_date."'";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			while($row = $result->fetch_row()){
				$ret_arr["data"]["isoxygen"] = $row[0];
			}
		}
		//获取用户的已达标的心率数据
		$daytimestamp_start = strtotime($self_date);
		$daytimestamp_end = strtotime($self_date." 23:59:59");
		$sql = "SELECT `heart_rate`,`record_date` FROM `userheartrate` WHERE `user_id`='".$self_id."' AND  `record_date` BETWEEN '".$daytimestamp_start."' AND '".$daytimestamp_end."' AND `heart_rate`>='".$ret_arr["data"]["userstandardheartrate"]."' ORDER BY `record_date`";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$i = 0;
			while($row = $result->fetch_row()){
				$ret_arr["data"]["heartratedata"][$i]["heartrate"] = $row[0];
				$ret_arr["data"]["heartratedata"][$i]["time"] = date("Y-m-d H:i:s",$row[1]);
				$i++;
			}
		}		
		$state = $ret_arr["state"];
		$message = $ret_arr["message"];
		$redata = json_encode($ret_arr["data"]);
		
		break;
		
	case "SaveIsOxygen":
		$self_id = $_GET["self_id"];
		$isoxygen = $_GET["isoxygen"];
		$updatedate = isset($_GET["selfdate"]) ? $_GET["selfdate"] : date("Y-m-d");
		
		$sql = "SELECT `webhandleoxygen` FROM `usersetcount` WHERE `user_id`='".$self_id."' AND `record_date`='".$updatedate."' ";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$webhandle = 1;
			while($row = $result->fetch_row()){
				$webhandle = $row[0];
			}
			if($webhandle == "0"){
				$sql = "UPDATE `usersetcount` SET `isoxygen`='".$isoxygen."',`webhandleoxygen`='1' WHERE `user_id`='".$self_id."' AND `record_date`='".$updatedate."'";
				if($conn->query($sql)){
					$state = TRUE;
					$message = "保存成功";
					
				}else{
					$state = FALSE;
					$message = "服务器错误";
				}
			}else{
				$state = FALSE;
				$message = "已修改过一次，请联系开发人员";
			}
		}else{
			$state = FALSE;
			$message = "要更改的信息不存在，请联系开发人员";
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