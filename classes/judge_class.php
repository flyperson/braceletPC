<?php
/*
 * 判断用户当天是否有氧运动
 * */
 
class JudgeAerobicExercise{
	protected $database;//数据库句柄
	public $AEtime = 0;//有氧运动时长
	public $isAE;//是否有氧运动
	protected $userid;//要判断的用户
	public $standardHR;//用户达标的心率
	public $usefuldata = array();//有用的心率数据
	public $usefuldata_date = array();//有用的心率数据时间
	protected $_10_MINUTES = 600;
	public $sql;
	protected $userage;
	protected $selfdate;
	
	public function __construct($db,$uid,$age,$date){
		$this->database = $db;
		$this->userid = $uid;
		$this->userage = $age;
		$this->selfdate = $date;
	}
	
	/*
	 * 计算用户的达标心率
	 * */
	protected function CalculateHR(){
		$this->standardHR = round((207-0.7*intval($this->userage))*0.5);
	}
	
	/*
	 * 查询用户当天所有心率，并剔除不达标的心率数据
	 * */
	protected function Deluselessdata(){
		$daytimestamp_start = strtotime($this->selfdate);
		$daytimestamp_end = strtotime($this->selfdate." 23:59:59");
		$sql = "SELECT `record_date` FROM `userheartrate` WHERE `user_id`='".$this->userid."' AND  `record_date` BETWEEN '".$daytimestamp_start."' AND '".$daytimestamp_end."' AND heart_rate>='".$this->standardHR."' ORDER BY `record_date`";
		$this->sql = $sql;
		$result = $this->database->query($sql);
		if($result->num_rows>0){
			$i = 0;
			while($row = $result->fetch_assoc()){
				$this->usefuldata[$i] = $row["record_date"];
				$this->usefuldata_date[$i] = date("Y-m-d H:i:s",$row["record_date"]);
				$i++;
			}
		}else{
			$this->isAE = FALSE;
			$this->AEtime = 0;
		}
	}
	
	
	/*
	 * 计算有氧运动的时长
	 * */
	protected function CountTime(){
		$len = count($this->usefuldata);
		if($len > 2){
			for($i=0;$i<$len;$i++){
				if(isset($this->usefuldata[$i]) && isset($this->usefuldata[$i+1])){
					$difference_time = intval($this->usefuldata[$i+1]) - intval($this->usefuldata[$i]);
					if($difference_time <= "800" ){
						$this->AEtime = $this->AEtime + 10;
					}
				}
			}
			$sql = "SELECT `webhandleoxygen` FROM `usersetcount` WHERE `user_id`='".$this->userid."' AND `record_date`='".$this->selfdate."' ";
			$result = $this->database->query($sql);
			if($result->num_rows>0){
				$webhandle = 1;
				while($row = $result->fetch_row()){
					$webhandle = $row[0];
				}
				if($webhandle == "0"){//未被web修改过
					if($this->AEtime >= 30){
						$this->isAE = "是";
						$sql = "UPDATE `usersetcount` SET oxygentime='".$this->AEtime."',isoxygen='是' WHERE user_id='".$this->userid."' AND record_date='".$this->selfdate."'";
						$this->database->query($sql);
					}else{
						$this->isAE = "否";
						$sql = "UPDATE `usersetcount` SET oxygentime='".$this->AEtime."',isoxygen='否' WHERE user_id='".$this->userid."' AND record_date='".$this->selfdate."'";
						$this->database->query($sql);
					}
				}
			}
		}else{
			$sql = "SELECT `webhandleoxygen` FROM `usersetcount` WHERE `user_id`='".$this->userid."' AND `record_date`='".$this->selfdate."' ";
			$result = $this->database->query($sql);
			if($result->num_rows>0){
				$webhandle = 1;
				while($row = $result->fetch_row()){
					$webhandle = $row[0];
				}
				if($webhandle == "0"){//未被web修改过
					$this->isAE = "否";
					$sql = "UPDATE `usersetcount` SET isoxygen='否' WHERE user_id='".$this->userid."' AND record_date='".$this->selfdate."'";
					$this->database->query($sql);
				}
			}
		}
	}
	
	/*
	 * 使用类
	 * */
	public function UsingClass(){
		$this->CalculateHR();//计算判断心率
		$this->Deluselessdata();
		$this->CountTime();
	}
} 

  
?>