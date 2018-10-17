<?php
	require_once "conn.php";
	require_once "function_php.php";
	
	$str_json = '{"data":[{"user_id":"56","step_cpunt":"10509","record_date":"2018-10-02"},{"user_id":"60","step_cpunt":"10852","record_date":"2018-10-01"},{"user_id":"60","step_cpunt":"7482","record_date":"2018-10-04"},{"user_id":"194","step_cpunt":"10031","record_date":"2018-10-04"},{"user_id":"125","step_cpunt":"11844","record_date":"2018-09-27"},{"user_id":"125","step_cpunt":"11344","record_date":"2018-09-28"},{"user_id":"125","step_cpunt":"17425","record_date":"2018-09-29"},{"user_id":"54","step_cpunt":"20125","record_date":"2018-10-03"},{"user_id":"112","step_cpunt":"10032","record_date":"2018-09-28"},{"user_id":"182","step_cpunt":"10137","record_date":"2018-10-02"},{"user_id":"185","step_cpunt":"13526","record_date":"2018-09-30"},{"user_id":"238","step_cpunt":"10852","record_date":"2018-09-29"},{"user_id":"207","step_cpunt":"11526","record_date":"2018-09-29"},{"user_id":"207","step_cpunt":"8912","record_date":"2018-10-09"},{"user_id":"237","step_cpunt":"12125","record_date":"2018-09-28"},{"user_id":"277","step_cpunt":"11125","record_date":"2018-09-28"},{"user_id":"328","step_cpunt":"10028","record_date":"2018-09-27"},{"user_id":"72","step_cpunt":"11259","record_date":"2018-10-05"},{"user_id":"257","step_cpunt":"17236","record_date":"2018-10-05"},{"user_id":"216","step_cpunt":"12250","record_date":"2018-10-04"},{"user_id":"176","step_cpunt":"11337","record_date":"2018-09-28"},{"user_id":"176","step_cpunt":"11567","record_date":"2018-10-08"},{"user_id":"112","step_cpunt":"11253","record_date":"2018-10-09"},{"user_id":"254","step_cpunt":"12693","record_date":"2018-10-09"}]}';
	
	$arr = json_decode($str_json,TRUE);
	
	$errordata = array();
	
	foreach($arr["data"] as $index => $datainfo){
		$sql = "SELECT id FROM usersetcount WHERE user_id='".$datainfo["user_id"]."' AND record_date='".$datainfo["record_date"]."'";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$sql = "";
			while($row = $result->fetch_row()){
				$sql = "UPDATE usersetcount SET step_count='".$datainfo["step_cpunt"]."',score='".GetScore($datainfo["step_cpunt"])."' WHERE id='".$row[0]."'";
			}
			if(!empty($sql)){
				if(!$conn->query($sql)){
					$errordata[] = $datainfo;
				}
			}else{
				$errordata[] = $datainfo;
			}
		}else{
			$sql = "INSERT INTO usersetcount(user_id,step_count,record_date,score) VALUES(";
			$sql .= "'".$datainfo["user_id"]."','".$datainfo["step_cpunt"]."','".$datainfo["record_date"]."','".GetScore($datainfo["step_cpunt"])."')";
			if(!$conn->query($sql)){
				$errordata[] = $datainfo;
			}
		}
	}

	echo "处理完毕";
	if(count($errordata) > 0){
		echo "</br></br></br>失败数据：</br>";
		print_r($errordata);
	}
	
	
?>