<?php
header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');
header('Charset=utf-8');
require("function_php.php");

//由于服务器运行环境问题，不能够用require("conn.php")，会出现json无法解释的情况
//require("conn.php");
  $servername = "127.0.0.1:3306";
	$username = "root";
	$password = "123456";
	$dbname ="bracelet";	
	$conn = new mysqli($servername, $username, $password, $dbname);	
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}else{
//		echo "Connected successfully 成功！";
	}
	
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
 * 
 * UpdateHeartrate : 更新心率数据
 * GetHeartrate : 获取心率数据
 * */


$requestmethod = array("POST");

$restatus = 0;//返回的状态：true 或者 false
$remessage = "";
$redata = "";
$sql = "";

if(in_array($_SERVER["REQUEST_METHOD"], $requestmethod)){
	
//	@$send_json = $_REQUEST["send_json"];
	
	$send_json = @file_get_contents('php://input');
	
	if(!empty($_GET)){
		if(array_key_exists("cmark", $_GET) && array_key_exists("userid", $_GET)){
			$send_json = '{"cmark":"'.$_GET["cmark"].'","data":{"userid":"'.$_GET["userid"].'"}}';
		}
	}
	
	$senddatatype = strtolower(gettype($send_json));
	if($senddatatype == "string"){
		$send_json = json_decode($send_json,TRUE);
	}
	$senddatatype = strtolower(gettype($send_json));
	
	if($senddatatype == "array" || $senddatatype == "object"){	
		switch($send_json["cmark"]){
			case "Signup" :
			//{"cmark":"Signup","data":{"userphone":"12345678901","userpassword":"123456","username":"测试员一号","userage":"20"}}
				if(!empty($send_json["data"]["userphone"]) && !empty($send_json["data"]["userpassword"])){
					//检测用户手机号是否已存在
					$sql = "SELECT userphone FROM user WHERE userphone='".$send_json["data"]["userphone"]."'";
					$result = $conn->query($sql);
					if(!$result->num_rows > 0){
						$send_json["data"]["username"] = empty($send_json["data"]["username"])?"无":$send_json["data"]["username"];
						$send_json["data"]["userage"] = empty($send_json["data"]["userage"])?"0":$send_json["data"]["userage"];
						$usericon = "";//头像地址
						$regisrtationdate = time();//注册日期的时间戳
						$sql = "INSERT INTO `user`(`userphone`,`username`,`userpassword`,`regisrtationdate`,`usericon`,`userage`) VALUES(";
						$sql .= "'".$send_json["data"]["userphone"]."','".$send_json["data"]["username"]."','".$send_json["data"]["userpassword"]."','".$regisrtationdate."','".$usericon."','".$send_json["data"]["userage"]."')";
						if($conn->query($sql)){
							$userid = $conn->insert_id;		
							$restatus = 1;
							$usericon = empty($usericon) ? "" : $usericonpath.$usericon;
							$redata = '[{"userid":"'.$userid.'","userphone":"'.$send_json["data"]["userphone"].'","username":"'.$send_json["data"]["username"].'","regisrtationdate":"'.date("Y-m-d",$regisrtationdate).'","usericon":"'.$usericon.'","userage":"'.$send_json["data"]["userage"].'"}]';
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
					//
					$sql = "SELECT id FROM user WHERE userphone='".$send_json["data"]["userphone"]."' LIMIT 1 ";
					$result = $conn->query($sql);
					if($result->num_rows > 0){
						$sql = "SELECT id,userphone,username,usericon,regisrtationdate,usergroup,userage FROM user WHERE userphone='".$send_json["data"]["userphone"]."' AND userpassword='".$send_json["data"]["userpassword"]."' LIMIT 1 ";
						$result2 = $conn->query($sql);
						if($result2->num_rows > 0){
							$restatus = 1;
							while($row = $result2->fetch_row()){
								$row[3] = empty($row[3]) ? "" : $usericonpath.$row[3];
								$redata = '{"userid":"'.$row[0].'","userphone":"'.$row[1].'","username":"'.$row[2].'","usericon":"'.$row[3].'","regisrtationdate":"'.date("Y-m-d",$row[4]).'","usergroup":"'.$row[5].'","userage":"'.$row[6].'"}';
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
//				{"cmark": "UpdatePraise","data": {"userid":"7","clickeduserid":"4"}}
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `id` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断被点赞用户是否存在
						$sql = "SELECT `id` FROM `user` WHERE `id`='".$send_json["data"]["clickeduserid"]."'";
						$result3 = $conn->query($sql);
						if($result3->num_rows>0){//判断点赞用户是否存在
							$nowdate = date("Y-m-d");
							$sql = "SELECT `id`,`praise_number`,`praised_userid` FROM `usersetcount` WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
							$result2 = $conn->query($sql);
							if($result2->num_rows>0){//判断是更新还是插入
								while($row2 = $result2->fetch_assoc()){
									$praisenumber = $row2["praise_number"];
									$praiseduserid_arr = empty($row2["praised_userid"])?array():explode(",", $row2["praised_userid"]);
								}
								$praisenumber++;
								if(isset($send_json["data"]["clickeduserid"])){
									if(!in_array($send_json["data"]["clickeduserid"],$praiseduserid_arr)){
										$praiseduserid_arr[] = $send_json["data"]["clickeduserid"];
										$praiseduserid_str = implode(",", $praiseduserid_arr);
										//UPDATE
										$sql = "UPDATE `usersetcount` SET `praise_number`='".$praisenumber."',`praised_userid`='".$praiseduserid_str."' WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
										if($conn->query($sql)){
											$restatus = 1;
											$remessage .= "保存成功";
											$redata = '{"user_id":"'.$send_json["data"]["userid"].'","praise_number":"'.$praisenumber.'"}';
										}else{
											$remessage .= "服务器错误";
										}
									}else{
										$remessage .= "你已点过赞";
									}
									
								}else{
									$remessage .= "缺少登录用户的标志";
								}
								
							}else{
								if(isset($send_json["data"]["clickeduserid"])){
									//INSERT INTO
									$sql = "INSERT INTO `usersetcount`(`user_id`,`step_count`,`praise_number`,`praised_userid`,`record_date`) VALUES('".$send_json["data"]["userid"]."','0','1','".$send_json["data"]["clickeduserid"]."','".$nowdate."')";
									if($conn->query($sql)){
										$restatus = 1;
										$remessage .= "保存成功";
										$redata = '{"user_id":"'.$send_json["data"]["userid"].'","praise_number":"1"}';
									}else{
										$remessage .= "服务器错误";
									}
								}else{
									$remessage .= "缺少登录用户的标志";
								}
							}
						}else{
							$remessage .= "点赞操作的用户不存在无法更新数据";
						}
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
						$tempdata .= ','.'{"title":"'.$row[1].'","seeurl":"'.$seeurl.'"}';
					}
					$tempdata = substr($tempdata, 1);
					$restatus = 1;
					$remessage .= "获取成功";
					$redata = '['.$tempdata.']';
				}else{
					$remessage .= "暂无数据";
				}
				break;	
				
			
				
			case "GetRankingPerson" :
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `id` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						//获取所有用户信息
						$user_msg = "";
						$sql = "SELECT `id`,`username`,`usericon`,`usergroup` FROM `user`";
						$result = $conn->query($sql);
						if($result->num_rows>0){
							while($row = $result->fetch_assoc()){
								$user_msg[$row["id"]]["user_id"] = $row["id"];
								$user_msg[$row["id"]]["username"] = $row["username"];
								$user_msg[$row["id"]]["usericon"] = empty($row["usericon"])?"":$usericonpath.$row["usericon"];
								$user_msg[$row["id"]]["usergroup"] = $row["usergroup"];
								
								$user_msg[$row["id"]]["ranking_person"] = "0";
								$user_msg[$row["id"]]["ranking_group"] = "0";
								$user_msg[$row["id"]]["step_count"] = "0";
								$user_msg[$row["id"]]["praise_number"] = "0";
								$user_msg[$row["id"]]["ispraised"] = 'TRUE';
							}
						}
						if(!empty($user_msg)){
							//更新个人排名名次，步数，赞数
							$sql = "SELECT `user_id`,`step_count`,`praise_number`,`praised_userid` FROM userrunnumber ORDER BY step_count DESC";
							$result2 = $conn->query($sql);
							if($result2->num_rows>0){
								$i = 1;
								while($row2 = $result2->fetch_assoc()){
									if(array_key_exists($row2["user_id"], $user_msg)){
										$user_msg[$row2["user_id"]]["ranking_person"] = $i;
										$user_msg[$row2["user_id"]]["step_count"] = $row2["step_count"];
										$user_msg[$row2["user_id"]]["praise_number"] = $row2["praise_number"];
										
										$praiseduserid_arr = empty($row2["praised_userid"])?array():explode(",", $row2["praised_userid"]);
										if(in_array($send_json["data"]["userid"],$praiseduserid_arr)){
											$user_msg[$row2["user_id"]]["ispraised"] = 'FALSE';
										}
										
										$i++;
									}
								}
							}
							
							
							//更新分组排名名次
							$sql = "SELECT `user_id` FROM userrunnumber WHERE `usergroup`='".$user_msg[$send_json["data"]["userid"]]["usergroup"]."' ORDER BY step_count DESC";
							$result3 = $conn->query($sql);
							if($result3->num_rows>0){
								$i = 1;
								while($row3 = $result3->fetch_assoc()){
									if($send_json["data"]["userid"] == $row3["user_id"]){
										$user_msg[$send_json["data"]["userid"]]["ranking_group"] = $i;
									}
									$i++;
								}
							}
							
							$all_data_person = Getranking($user_msg,"ranking_person");
							
							//编写返回数据json
							$oneself_json = "";
							$all_json = "";
							foreach($all_data_person as $ky => $infodata){
								if($infodata["user_id"] == $send_json["data"]["userid"]){
									$oneself_json = '{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$infodata["user_id"].'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'","ispraised":"'.$infodata["ispraised"].'"}';
								}
								$all_json .= ','.'{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$infodata["user_id"].'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'","ispraised":"'.$infodata["ispraised"].'"}';
								
							}
							$ranking_data = $oneself_json.$all_json;
							
							//处理结果及数据返回
							$restatus = 1;
							$remessage .= "获取成功";
							$redata = '['.$ranking_data.']';
	//						exit();
							
						}else{
							$remessage .= "缺少用户数据无法获取";
						}
					}else{
						$remessage .= "用户不存在";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
				
			case "GetRankingGroup" :
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `usergroup` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						while($row = $row = $result->fetch_assoc()){
							$self_group = $row["usergroup"];
						}
						//获取登录用户信息
						$user_msg = "";
						$sql = "SELECT `id`,`username`,`usericon`,`usergroup` FROM `user` WHERE `usergroup`='".$self_group."' ";
						$result = $conn->query($sql);
						if($result->num_rows>0){
							while($row = $result->fetch_assoc()){
								$user_msg[$row["id"]]["user_id"] = $row["id"];
								$user_msg[$row["id"]]["username"] = $row["username"];
								$user_msg[$row["id"]]["usericon"] = empty($row["usericon"])?"":$usericonpath.$row["usericon"];
								$user_msg[$row["id"]]["usergroup"] = $row["usergroup"];
								
								$user_msg[$row["id"]]["ranking_person"] = "0";
								$user_msg[$row["id"]]["ranking_group"] = "0";
								$user_msg[$row["id"]]["step_count"] = "0";
								$user_msg[$row["id"]]["praise_number"] = "0";
								$user_msg[$row["id"]]["ispraised"] = 'TRUE';
							}
						}
						if(!empty($user_msg)){
							//更新分组排名名次，步数，赞数
							$sql = "SELECT `user_id`,`step_count`,`praise_number`,`praised_userid` FROM userrunnumber WHERE `usergroup`='".$user_msg[$send_json["data"]["userid"]]["usergroup"]."' ORDER BY step_count DESC";
							$result3 = $conn->query($sql);
							if($result3->num_rows>0){
								$i = 1;
								while($row3 = $result3->fetch_assoc()){
									if(array_key_exists($row3["user_id"], $user_msg)){
										$user_msg[$row3["user_id"]]["ranking_group"] = $i;
										$user_msg[$row3["user_id"]]["step_count"] = $row3["step_count"];
										$user_msg[$row3["user_id"]]["praise_number"] = $row3["praise_number"];
										
										$praiseduserid_arr = empty($row3["praised_userid"])?array():explode(",", $row3["praised_userid"]);
										if(in_array($send_json["data"]["userid"],$praiseduserid_arr)){
											$user_msg[$row3["user_id"]]["ispraised"] = 'FALSE';
										}
										
										$i++;
									}
								}
							}
							
							//更新登录用户的个人排名名次
							$sql = "SELECT `user_id` FROM userrunnumber ORDER BY step_count DESC";
							$result2 = $conn->query($sql);
							if($result2->num_rows>0){
								$i = 1;
								while($row2 = $result2->fetch_assoc()){
									if($row2["user_id"] == $send_json["data"]["userid"]){
										$user_msg[$row2["user_id"]]["ranking_person"] = $i;
									}
									$i++;
								}
							}
							
							
							
							$all_data_person = Getranking($user_msg,"ranking_group");
							
							//编写返回数据json
							$oneself_json = "";
							$all_json = "";
							foreach($all_data_person as $ky => $infodata){
								if($infodata["user_id"] == $send_json["data"]["userid"]){
									$oneself_json = '{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$infodata["user_id"].'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'","ispraised":"'.$infodata["ispraised"].'"}';
								}
								$all_json .= ','.'{"ranking_person":"'.$infodata["ranking_person"].'","ranking_group":"'.$infodata["ranking_group"].'","user_id":"'.$infodata["user_id"].'","username":"'.$infodata["username"].'","usericon":"'.$infodata["usericon"].'","usergroup":"'.$infodata["usergroup"].'","step_count":"'.$infodata["step_count"].'","praise_number":"'.$infodata["praise_number"].'","ispraised":"'.$infodata["ispraised"].'"}';
								
							}
							$ranking_data = $oneself_json.$all_json;
							
							//处理结果及数据返回
							$restatus = 1;
							$remessage .= "获取成功";
							$redata = '['.$ranking_data.']';
	//						exit();
							
						}else{
							$remessage .= "缺少用户数据无法获取";
						}
					}else{
						$remessage .= "用户不存在";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
				
			case "Upfile":	
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `usericon` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						while($row = $result->fetch_assoc()){
							if(!empty($row["usericon"])){
								$filepath = "usericon/".$row["usericon"];
								if(file_exists($filepath)){
									@unlink($filepath);
								}
							}
						}
						
						if(count($_FILES)>0){
							//用form-data上传方式处理
							$usericon = "";//头像地址
							
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
							if(!empty($usericon)){
								$sql = "UPDATE `user` SET `usericon`='".$usericon."' WHERE `id`='".$send_json["data"]["userid"]."'";
								if($conn->query($sql)){
									//处理结果及数据返回
									$restatus = 1;
									$remessage .= "保存成功";
									$redata = '{"user_id":"'.$send_json["data"]["userid"].'","usericon":"'.$usericonpath.$usericon.'"}';
								}else{
									$remessage .= "服务器错误";
								}
							}else{
								$remessage .= "图片保存失败";
							}
						}else{
							//body：binary上传处理方式
							$contentlength = isset($_SERVER["CONTENT_LENGTH"])?$_SERVER["CONTENT_LENGTH"]:"0";
							if($contentlength > 0){
								$inarray_image = array("image/jpeg","image/png","image/gif");
								$contenttype = isset($_SERVER["CONTENT_TYPE"])?strtolower($_SERVER["CONTENT_TYPE"]):"";
								if(in_array($contenttype, $inarray_image)){
									$ext = "";
									switch($contenttype){
										case "image/jpeg":
											$ext = "jpg";
											break;
										case "image/png":
											$ext = "png";
											break;
										case "image/gif":
											$ext = "gif";
											break;
										default :
											break;
									}
									if(!empty($ext)){
										//检测要存放的路径是否存在，如果不存在就自动创建
										$uploadPath = "usericon";
										if (! file_exists ( $uploadPath )) {
											mkdir ( $uploadPath, 0777, true );
											chmod ( $uploadPath, 0777 );
										}
										$uniName = md5 ( uniqid ( microtime ( true ), true ) ) . '.' . $ext;//加密名称
										if(@file_put_contents("usericon/".$uniName,@file_get_contents('php://input'))){
											$sql = "UPDATE `user` SET `usericon`='".$uniName."' WHERE `id`='".$send_json["data"]["userid"]."'";
											if($conn->query($sql)){
												//处理结果及数据返回
												$restatus = 1;
												$remessage .= "保存成功";
												$redata = '{"user_id":"'.$send_json["data"]["userid"].'","usericon":"'.$usericonpath.$uniName.'"}';
											}else{
												$remessage .= "服务器错误";
											}
										}else{
											$remessage .= "图片保存失败";
										}
									}else{
										$remessage .= "图片格式不正确";
									}
								}else{
									$remessage .= "CONTENT_TYPE格式不正确,你的CONTENT_TYPE为".$contenttype;
								}
							}else{
								$remessage .= "无文件上传";
							}
						}
					}else{
						$remessage .= "用户不存在";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
			
			case "UpdateSetcountAndHeart":
				//{"cmark":"UpdateSetcountAndHeart","data":{"userid":"1","setcount":"2000","heartrate":"79"}}
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `usericon` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						//更新步数
						$send_json["data"]["setcount"] = isset($send_json["data"]["setcount"])?$send_json["data"]["setcount"]:"0";
						$nowdate = date("Y-m-d");
						$sql = "SELECT `id` FROM `usersetcount` WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
						$result2 = $conn->query($sql);
						if($result2->num_rows>0){//判断是更新还是插入
							//UPDATE
							$sql = "UPDATE `usersetcount` SET `step_count`='".$send_json["data"]["setcount"]."' WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date`='".$nowdate."'";
							if($conn->query($sql)){
								$restatus = 1;
								$remessage .= "步数保存成功,";
							}else{
								$remessage .= "服务器错误";
							}
						}else{
							//INSERT INTO
							$sql = "INSERT INTO `usersetcount`(`user_id`,`step_count`,`record_date`) VALUES('".$send_json["data"]["userid"]."','".$send_json["data"]["setcount"]."','".$nowdate."')";
							if($conn->query($sql)){
								$restatus = 1;
								$remessage .= "步数保存成功,";
							}else{
								$remessage .= "服务器错误";
							}
						}
						//更新心率
						$send_json["data"]["heartrate"] = isset($send_json["data"]["heartrate"])?$send_json["data"]["heartrate"]:"0";
						$nowtimestamp = time();
						$sql = "INSERT INTO `userheartrate`(`user_id`,`heart_rate`,`record_date`) VALUES(";
						$sql .= "'".$send_json["data"]["userid"]."','".$send_json["data"]["heartrate"]."','".$nowtimestamp."')";
						if($conn->query($sql)){
							//处理结果及数据返回
							$restatus = 1;
//							$redata = '{"userid":"'.$send_json["data"]["userid"].'","heartrate":"'.$send_json["data"]["heartrate"].'","recorddate":"'.date("Y-m-d H:i:s",$nowtimestamp).'"}';
							$remessage .= "心率保存成功";
						}else{
							$remessage .= "服务器错误";
						}
						
					}else{
						$remessage .= "用户不存在";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
			
			case "GetSelfRunnumber":
//			{"cmark":"GetSelfRunnumber","data":{"userid":"1"}}
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `usericon` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						$nowdate = date("Y-m-d");
						$beforedate = date("Y-m-d",strtotime("-7day",strtotime($nowdate)));
						$sql = "SELECT `record_date`,`step_count` FROM `usersetcount` WHERE `user_id`='".$send_json["data"]["userid"]."' AND `record_date` BETWEEN '".$beforedate."' AND '".$nowdate."' ORDER BY `record_date` ";
						$result = $conn->query($sql);
						if($result->num_rows>0){
							$tmpdata = "";
							while($row = $result->fetch_row()){
								$tmpdata .= ','.'{"date":"'.$row[0].'","run_number":"'.$row[1].'"}';
							}
							$tmpdata = substr($tmpdata, 1);
							$redata = '['.$tmpdata.']';
							$restatus = 1;
							$remessage .= "获取成功";
						}else{
							$remessage .= "无数据";
						}
					}else{
						$remessage .= "用户不存在";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
				}
				break;
			
			case "UpdateAge":
				//{"cmark":"UpdateAge","data":{"userid":"1","userage":"20"}}
				if(!empty($send_json["data"]["userid"])){
					$sql = "SELECT `usericon` FROM `user` WHERE `id`='".$send_json["data"]["userid"]."'";
					$result = $conn->query($sql);
					if($result->num_rows>0){//判断用户是否存在
						$sql = "UPDATE `user` SET `userage`='".$send_json["data"]["userage"]."' WHERE `id`='".$send_json["data"]["userid"]."'";
						if($conn->query($sql)){
							$restatus = 1;
							$remessage .= "保存成功";
							$redata = '{"userid":"'.$send_json["data"]["userid"].'","userage":"'.$send_json["data"]["userage"].'"}';
							
						}else{
							$remessage .= "服务器错误";
						}
					}else{
						$remessage .= "用户不存在";
					}
				}else{
					$remessage .= "缺少用户标志无法更新数据";
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
$json_str = '{"status":"'.$restatus.'","data":'.$redata.',"msg":"'.$remessage.'"}';


echo $json_str;

$conn->close();

?>