<?php	
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

?>