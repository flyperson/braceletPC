<?php
class CreateToken{
	protected $id= 'LTAIkgFeqf2CMNFt';
    protected $key= 'YMlWWZeihh2fJOH4stXHbSXWPnd1WC';
	protected $database;
	protected $response = array(
		"accessid"=>"LTAIkgFeqf2CMNFt",
		"host"=>"http://127.0.0.1:8081/braceltePC/port.php",
		"policy"=>"",
		"signature"=>"",
		"expire"=>""
	);
	
	public function __construct($db){
		$this->database = $db;
	}
	
	protected function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }
	
	/*
	 * 生成签名
	 * */
	protected function CreatePolicy(){
		print_r($this->response);
		
		$now = time();
		$this->response["expire"] = $now + 300;//五分钟内有效
		
		$expiration = $this->gmt_iso8601($this->response["expire"]);
		$arr = array('expiration'=>$expiration);
		$this->response["policy"] = json_encode($arr);
		$this->response["policy"] = base64_encode($this->response["policy"]);
		
		$this->signature = base64_encode(hash_hmac('sha1', $base64_policy, $this->key, true));
		*/
	}
	
	/*
	 * */
	public function Usingclass(){
		$this->CreatePolicy();
	}
}

require("../conn.php");
$test = new CreateToken($conn);
$test->Usingclass();
?>