<?php
require_once "conn.php";

$flag = $_REQUEST["flag"];

$state = 0;
$msg = "";
$redata = "";

switch($flag){
	case "NewArtcle" :
		$art_title = $_POST["art_title"];
		$art_content_64 = $_POST["art_content_64"];
		$judge_update_insert = $_POST["judge_update_insert"];
		
		
		if(empty($judge_update_insert)){
			$sql = "INSERT INTO `article`(`title`,`content`,`creatdate`) VALUES(";
			$sql .= "'".$art_title."','".$art_content_64."','".time()."')";
		}else{
			if($judge_update_insert == "1" || $judge_update_insert == "2" || $judge_update_insert == "5"){//不允许修改标题
				$sql = "UPDATE `article` SET `content`='".$art_content_64."' WHERE `id`='".$judge_update_insert."'";
			}else{
				$sql = "UPDATE `article` SET `title`='".$art_title."',`content`='".$art_content_64."' WHERE `id`='".$judge_update_insert."'";
			}
		}
		
		if($conn->query($sql)){
			$state = 1;
			$msg = "保存成功";
		}else{
			$msg = "保存失败";
		}
		break;
	
	case "GetArticleMessage" :
		
		$sql = "SELECT `id`,`title`,`creatdate` FROM `article`";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmp = "";
			while($row = $result->fetch_row()){
				if($row[0] == "1" || $row[0] == "2" || $row[0] == "5"){
					$tmp .= ','.'{"title":"'.$row[1].'","createdate":"'.$row[2].'","handle":"<button class=\"btn btn-primary\" name=\"'.$row[0].'\" onclick=\"checkarticle(this)\">查看</button><button name=\"'.$row[0].'\" class=\"btn btn-default\" onclick=\"phonecheck(this)\">手机查看模式</button>"}';
				}else{
					$tmp .= ','.'{"title":"'.$row[1].'","createdate":"'.$row[2].'","handle":"<button class=\"btn btn-primary\" name=\"'.$row[0].'\" onclick=\"checkarticle(this)\">查看</button><button name=\"'.$row[0].'\" class=\"btn btn-danger\" onclick=\"deletearticle(this)\">删除</button><button name=\"'.$row[0].'\" class=\"btn btn-default\" onclick=\"phonecheck(this)\">手机查看模式</button>"}';
				}
			}
			$tmp = substr($tmp, 1);
			$redata = '['.$tmp.']';
			$state = 1;
			$msg .= "获取成功";
		}
		
		break;
		
	case "CheckArticle" : 
		$selfid = $_GET["selfid"];
		
		$sql = "SELECT `title`,`content` FROM `article` WHERE `id`='".$selfid."' LIMIT 1";
		$result = $conn->query($sql);
		if($result->num_rows>0){
			$tmp = "";
			while($row = $result->fetch_row()){
				$tmp .= ','.'{"title":"'.$row[0].'","content":"'.$row[1].'","selfid":"'.$selfid.'"}';
			}
			$tmp = substr($tmp, 1);
			$redata = '['.$tmp.']';
			$state = 1;
			$msg .= "获取成功";
		}
		
		break;
	
	case "DeleteArticle" :
		$selfid = $_GET["selfid"];
		
		$sql = "DELETE FROM `article` WHERE `id`='".$selfid."'";
		if($conn->query($sql)){
			$state = 1;
			$msg .= "删除成功";
		}else{
			$msg .= "服务器错误";
		}
		
		break;
	
	default :
		$msg = "没有相应的flag";
}


$conn->close();


if(empty($redata)){
	$redata = '""';
}

$json = '{"state":"'.$state.'","msg":"'.$msg.'","data":'.$redata.'}';
echo $json;


?>