<?php
require_once "conn.php";

$settimestamp = strtotime("2018-08-31 23:59:59");
$sql = "DELETE FROM userheartrate WHERE record_date<='".$settimestamp."'";

if($conn->query($sql)){
	echo "清除成功";
}else{
	echo "服务器错误";
}
$sql = "DELETE FROM usersetcount WHERE record_date<='2018-08-31'";
if($conn->query($sql)){
	echo "清除成功";
}else{
	echo "服务器错误";
}
?>