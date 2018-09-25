<?php
// 指定允许其他域名访问  
header('Access-Control-Allow-Origin:*');
// 响应类型  
header('Access-Control-Allow-Methods:*');  
// 响应头设置  
header('Access-Control-Allow-Headers:x-requested-with,content-type');
require("conn.php");
require("function_php.php");

/*
 * 文件（头像）保存路径 : http://127.0.0.1:8081/braceletPC/usericon/
 * */
$usericonpath = "http://127.0.0.1:8081/braceletPC/usericon/";//本地测试
//$usericonpath = "http://119.145.255.210:8080/braceletPC/usericon/";//远程


/*
 * 文章移动端查看路径
 * */
$seearticleurl = "http://127.0.0.1:8081/braceletPC/seearticle.html";//本地测试
//$seearticleurl = "http://119.145.255.210:8080/braceletPC/seearticle.html";//远程

/*
 * Signup ：注册账号
 * Login ：登录
 * UpdateSetCount ：更新步数
 * UpdatePraise : 更新赞数
 * GetRankData ： 获取排名信息
 * UpdateHeartrate : 更新心率数据
 * GetHeartrate : 获取心率数据
 * */
//print_r($_FILES);
print_r($_SERVER);
//print_r($_GET);
//$file_name = time().".png";
//
//if(@file_put_contents("usericon/".$file_name,@file_get_contents('php://input'))){
//	echo $usericonpath.$file_name;
//}
//
if(!isset($_SERVER["PHP_AUTH_USER"])){ 
	header("WWW-Authenticate:Basic realm=身份验证功能"); 
	header("HTTP/1.0 401 Unauthorized"); 
	echo "身份验证失败，您无权共享网络资源!"; 
	exit(); 
}else{
	echo $_SERVER["PHP_AUTH_USER"];
	echo $_SERVER["PHP_AUTH_PW"];
} 
exit();

$requestmethod = array("POST");

$restatus = 0;//返回的状态：true 或者 false
$remessage = "";
$redata = "";
$sql = "";

if(in_array($_SERVER["REQUEST_METHOD"], $requestmethod)){
	
//	@$send_json = $_REQUEST["send_json"];
	
	$send_json = @file_get_contents('php://input');
	
	$senddatatype = strtolower(gettype($send_json));
	if($senddatatype == "string"){
		$send_json = json_decode($send_json,TRUE);
	}
	$senddatatype = strtolower(gettype($send_json));
	
	if($senddatatype == "array" || $senddatatype == "object"){	
		switch($send_json["cmark"]){
			case "Signup" :
				if(!empty($send_json["data"]["userphone"]) && !empty($send_json["data"]["username"])){
					//检测用户手机号是否已存在
					$sql = "SELECT userphone FROM user WHERE userphone='".$send_json["data"]["userphone"]."'";
					$result = $conn->query($sql);
					if(!$result->num_rows > 0){
						$usericon = "";//头像地址
						if(count($_FILES)>0){
							$uploadpath = "usericon";
							foreach($_FILES as $ky =>$fileinfo){
								$uploadfilereturn = uploadFile($fileinfo,$uploadpath);					
								break;
							}
							if($uploadfilereturn["status"]){
								$tmpname_arr = explode("/", $uploadfilereturn["destination"]);
								$usericon = end($tmpname_arr);
								
							}else{
								$remessage .= $uploadfilereturn["msg"].",";
							}
						}
						$regisrtationdate = time();//注册日期的时间戳
						$sql = "INSERT INTO user(userphone,username,userpassword,regisrtationdate,usericon) VALUES(";
						$sql .= "'".$send_json["data"]["userphone"]."','".$send_json["data"]["username"]."','".$send_json["data"]["userpassword"]."','".$regisrtationdate."','".$usericon."')";
						if($conn->query($sql)){
							$userid = $conn->insert_id;		
							$restatus = 1;
							$usericon = empty($usericon) ? "" : $usericonpath.$usericon;
							$redata = '[{"userid":"'.$userid.'","userphone":"'.$send_json["data"]["userphone"].'","username":"'.$send_json["data"]["username"].'","regisrtationdate":"'.date("Y-m-d",$regisrtationdate).'","usericon":"'.$usericon.'"}]';
							$remessage .= "保存成功";
						}else{
							$remessage .= "保存失败";
						}
					}else{
						$remessage .= "手机号已注册";
					}	
				}else{
					$remessage .= "手机号或密码为空";
				}
				break;
				
			case "Login" :
				if(!empty($send_json["data"]["userphone"])){
					$sql = "SELECT id FROM user WHERE userphone='".$send_json["data"]["userphone"]."' LIMIT 1 ";
					$result = $conn->query($sql);
					if($result->num_rows > 0){
						$sql = "SELECT id,userphone,username,usericon,regisrtationdate,usergroup FROM user WHERE userphone='".$send_json["data"]["userphone"]."' AND userpassword='".$send_json["data"]["userpassword"]."' LIMIT 1 ";
						$result2 = $conn->query($sql);
						if($result2->num_rows > 0){
							$restatus = 1;
							while($row = $result2->fetch_row()){
								$row[3] = empty($row[3]) ? "" : $usericonpath.$row[3];
								$redata = '[{"userid":"'.$row[0].'","userphone":"'.$row[1].'","username":"'.$row[2].'","usericon":"'.$row[3].'","regisrtationdate":"'.date("Y-m-d",$row[4]).'","usergroup":"'.$row[5].'"}]';
							}
							$remessage .= "登录成功";
						}else{
							$remessage .= "密码号错误";
						}
					}else{
						$remessage .= "手机号错误";
					}
				}else{
					$remessage .= "手机号为空";
				}
				break;
			
			case "UpdateSetCount" :
//				{"cmark": "UpdateSetCount","data": {"userid":"7","setcount":"5621"}}
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `id` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						$nowdate = date("Y-m-d");
						$sql = "SELECT `id` FROM `usersetcount` WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
						$result2 = $conn->query($sql);
						if($result2->num_rows>0){//判断是更新还是插入
							//UPDATE
							$sql = "UPDATE `usersetcount` SET `step_count`='".$send_json["data"]["setcount"]."' WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
							if($conn->query($sql)){
								$restatus = 1;
								$remessage .= "保存成功";
							}else{
								$remessage .= "服务器错误";
							}
						}else{
							//INSERT INTO
							$sql = "INSERT INTO `usersetcount`(`user_id`,`step_count`,`record_date`) VALUES('".$send_json["data"]["userid"]."','".$send_json["data"]["setcount"]."','".$nowdate."')";
							if($conn->query($sql)){
								$restatus = 1;
								$remessage .= "保存成功";
							}else{
								$remessage .= "服务器错误";
							}
						}
					}else{
						$remessage .= "用户不存在无法更新数据";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
			
			case "UpdatePraise" :
//				{"cmark": "UpdatePraise","data": {"userid":"7","praisenumber":"35"}}
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `id` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						$nowdate = date("Y-m-d");
						$sql = "SELECT `id` FROM `usersetcount` WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
						$result2 = $conn->query($sql);
						if($result2->num_rows>0){//判断是更新还是插入
							//UPDATE
							$sql = "UPDATE `usersetcount` SET `praise_number`='".$send_json["data"]["praisenumber"]."' WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
							if($conn->query($sql)){
								$restatus = 1;
								$remessage .= "保存成功";
							}else{
								$remessage .= "服务器错误";
							}
						}else{
							$remessage .= "要更新的数据不存在";
						}
						
					}else{
						$remessage .= "用户不存在无法更新数据";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
			
			case "GetRankData" :
//				{"cmark": "GetRankData","data": {"userid":"7"}}
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `id`,`username`,`usericon`,`usergroup` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						$oneself_arr = array("step_count" => 0,"ranking_person" => 0,"ranking_group" => 0,"praise_number" => 0);//自己数据 数组
						$oneself_str = "";//自己数据json
						$personal = "";//个人排行
						$group = "";//分组排行榜
						$personal_arr = "";//个人排行数组
						$group_arr = "";//分组排行榜数组
						
						//获取自己数据 数组
						while($row = $result->fetch_row()){
							$oneself_arr["user_id"] = $row[0];
							$oneself_arr["username"] = $row[1];
							$oneself_arr["usericon"] = empty($row[2]) ? "" : $usericonpath.$row[2];
							$oneself_arr["usergroup"] = $row[3];
						}
						
						//获取个人排行榜信息
						$sql = "SELECT `user_id`,`usericon`,`username`,`step_count`,`praise_number`,`usergroup` FROM userrunnumber ORDER BY step_count DESC";
						$result3 = $conn->query($sql);
						if($result3->num_rows>0){
							$i = 1;
							while($row3 = $result3->fetch_row()){
								if($oneself_arr["user_id"] == $row3[0]){//判断是否为用户
									$oneself_arr["ranking_person"] = $i;
									$oneself_arr["step_count"] = $row3[3];
									$oneself_arr["praise_number"] = $row3[4];
								}
								$personal .= ','.'{"ranking":"'.$i.'","user_id":"'.$row3[0].'","usericon":"'.$row3[1].'","username":"'.$row3[2].'","step_count":"'.$row3[3].'","praise_number":"'.$row3[4].'","usergroup":"'.$row3[5].'"}';
								
								$personal_arr[$row3[0]]["ranking_person"] = $i;
								$personal_arr[$row3[0]]["usericon"] =  empty($row3[1]) ? "" : $usericonpath.$row3[1];
								$personal_arr[$row3[0]]["username"] = $row3[2];
								$personal_arr[$row3[0]]["step_count"] = $row3[3];
								$personal_arr[$row3[0]]["praise_number"] = $row3[4];
								$personal_arr[$row3[0]]["usergroup"] = $row3[5];
								
								$i++;
							}
							$personal = substr($personal, 1);
						}
						//获取分组排行榜信息
						$sql = "SELECT `user_id`,`usericon`,`username`,`step_count`,`praise_number`,`usergroup` FROM userrunnumber WHERE `usergroup`='".$oneself_arr["usergroup"]."' ORDER BY step_count DESC";
						$result4 = $conn->query($sql);
						if($result4->num_rows>0){
							$i = 1;
							while($row4 = $result4->fetch_row()){
								if($oneself_arr["user_id"] == $row4[0]){
									$oneself_arr["ranking_group"] = $i;
								}
								$group .= ','.'{"ranking":"'.$i.'","user_id":"'.$row4[0].'","usericon":"'.$row4[1].'","username":"'.$row4[2].'","step_count":"'.$row4[3].'","praise_number":"'.$row4[4].'","usergroup":"'.$row4[5].'"}'; 
								
								
								$group_arr[$row4[0]]["ranking_group"] = $i;
								
								$i++;
							}
							$group = substr($group, 1);
						}
						//自己数据json
						$oneself_str = '{"ranking_person":"'.$oneself_arr["ranking_person"].'","ranking_group":"'.$oneself_arr["ranking_group"].'","user_id":"'.$oneself_arr["user_id"].'","username":"'.$oneself_arr["username"].'","usericon":"'.$oneself_arr["usericon"].'","usergroup":"'.$oneself_arr["usergroup"].'","step_count":"'.$oneself_arr["step_count"].'","praise_number":"'.$oneself_arr["praise_number"].'"}';
						
						if(!empty($personal_arr)){
							//组合数据返回
							$allrankingdata = "";
							foreach($personal_arr as $ky => $infodata){
								$allrankingdata[$ky] = $infodata;
								$allrankingdata[$ky]["ranking_group"] = array_key_exists($ky, $group_arr)? $group_arr[$ky]["ranking_group"] : "0";
							}
							
							$oneself_json = "";
							$all_json = "";
							if(array_key_exists($oneself_arr["user_id"], $allrankingdata)){
								foreach($allrankingdata as $ky2 => $infodata){
									if($ky2 == $oneself_arr["user_id"]){
										$oneself_json = '{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$ky2.'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'"}';
									}else{
										$all_json .= ','.'{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$ky2.'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'"}';
									}
								}
							}else{
								$oneself_json = '{"ranking_person":"0","ranking_group":"0","user_id":"'.$oneself_arr["user_id"].'","username":"'.$oneself_arr["username"].'","usericon":"'.$oneself_arr["usericon"].'","usergroup":"'.$oneself_arr["usergroup"].'","step_count":"'.$oneself_arr["step_count"].'","praise_number":"'.$oneself_arr["praise_number"].'"}';
								foreach($allrankingdata as $ky2 => $infodata){
									if($ky2 == $oneself_arr["user_id"]){
										$oneself_json = '{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$ky2.'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'"}';
									}else{
										$all_json .= ','.'{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$ky2.'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'"}';
									}
								}
							}
							
							$ranking_data = $oneself_json.$all_json;
						}else{
							$oneself_json = '{"ranking_person":"0","ranking_group":"0","user_id":"'.$oneself_arr["user_id"].'","username":"'.$oneself_arr["username"].'","usericon":"'.$oneself_arr["usericon"].'","usergroup":"'.$oneself_arr["usergroup"].'","step_count":"'.$oneself_arr["step_count"].'","praise_number":"'.$oneself_arr["praise_number"].'"}';
							$ranking_data = $oneself_json;
						}
						
						//处理结果及数据返回
						$restatus = 1;
						$remessage .= "获取成功";
//						$redata = '{"oneself":'.$oneself_str.',"personranking":['.$personal.'],"groupranking":['.$group.']}';
						$redata = '['.$ranking_data.']';
						
					}else{
						$remessage .= "用户不存在无法更新数据";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
			
			case "UpdateHeartrate" :
//				{"cmark": "UpdateHeartrate","data": {"userid":"1","heartrate":"80"}}
				$send_json["data"]["heartrate"] = empty($send_json["data"]["heartrate"])? 0 : $send_json["data"]["heartrate"];
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `id`,`username`,`usericon`,`usergroup` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						$nowtimestamp = time();
						$sql = "INSERT INTO `userheartrate`(`user_id`,`heart_rate`,`record_date`) VALUES(";
						$sql .= "'".$send_json["data"]["userid"]."','".$send_json["data"]["heartrate"]."','".$nowtimestamp."')";
						if($conn->query($sql)){
							//处理结果及数据返回
							$restatus = 1;
							$redata = '{"userid":"'.$send_json["data"]["userid"].'","heartrate":"'.$send_json["data"]["heartrate"].'","recorddate":"'.date("Y-m-d H:i:s",$nowtimestamp).'"}';
							$remessage .= "保存成功";
						}else{
							$remessage .= "服务器错误";
						}
					}else{
						$remessage .= "用户不存在无法更新数据";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
				
			case "GetHeartrate" :
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `id`,`username`,`usericon`,`usergroup` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						$daytimestamp_start = strtotime(date("Y-m-d"));
						$daytimestamp_end = strtotime(date("Y-m-d")."23:59:59");
						$sql = "SELECT `heart_rate`,`record_date` FROM `userheartrate` WHERE `user_id`='".$send_json["data"]["userid"]."' AND  `record_date` BETWEEN '".$daytimestamp_start."' AND '".$daytimestamp_end."' ORDER BY `record_date`";
						$result2 = $conn->query($sql);
						if($result2->num_rows>0){
							$tempdata = "";
							
							while($row2 = $result2->fetch_row()){
								$tempdata .= ','.'{"heart_rate":"'.$row2[0].'","record_date":"'.date("Y-m-d H:i:s",$row2[1]).'"}';
							}
							$tempdata = substr($tempdata, 1);
							$restatus = 1;
							$remessage .= "获取成功";
							$redata = '['.$tempdata.']';
						}else{
							$remessage .= "暂无数据";
						} 
					}else{
						$remessage .= "用户不存在无法更新数据";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				
				break;
			case "GetArticleData" :
				$sql = "SELECT `id`,`title`,`creatdate` FROM `article` ";
				$result = $conn->query($sql);
				if($result->num_rows>0){
					$tempdata = "";
					while($row = $result->fetch_row()){
						$seeurl = "";
						$seeurl = $seearticleurl."?id=".$row[0];
						$tempdata = ','.'{"title":"'.$row[1].'","seeurl":"'.$seeurl.'"}';
					}
					$tempdata = substr($tempdata, 1);
					$restatus = 1;
					$remessage .= "获取成功";
					$redata = '['.$tempdata.']';
				}else{
					$remessage .= "暂无数据";
				}
				break;	
			default :
				$remessage .= "所请求的命令'cmark'没有定义";
		}	
	}else{
		$remessage = "发送过来的数据格式错误";
	}
}else{
	$remessage = "请求方法不正确";
}


if(empty($redata)){
	$redata = '""';
}
//$json_str = '{"status":"'.$restatus.'","data":'.$redata.',"msg":"'.$remessage.'","sql":"'.$sql.'"}';
@$json_str = '{"status":"'.$restatus.'","data":'.$redata.',"msg":"'.$remessage.'"}';
echo $json_str;

$conn->close();

?>