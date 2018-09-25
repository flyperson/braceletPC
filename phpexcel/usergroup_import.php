<?php
/*
 * 用户表导入数据
 * */

require("Classes/PHPExcel.php");
require("Classes/PHPExcel/Reader/Excel2007.php");
require("Classes/PHPExcel/IOFactory.php");
require("../conn.php");

/*
 * 整理字符串：去掉回车，中间空格，去除一个字符串两端空格 
 * */
function Settle_string($str){
	$str = str_replace(array("\r\n", "\r", "\n", "\t"), "", $str);//去掉回车
	$str = str_replace(" ", "", $str);//去掉空格 
	$str = trim($str);//去除一个字符串两端空格
	return $str; 
}


$state = 0;
$message = "";
$redata = "";

//接收传过来的文件
if(count($_FILES) > 0){
//	$filename = "file_excel/ImportTemplate.xls";
	$filename = $_FILES["upfile"]["tmp_name"];
	
	$path_info = pathinfo($_FILES["upfile"]["name"]);
	$ext = $path_info["extension"];
	$filename = iconv("utf-8", "gbk", $filename);
	if(file_exists($filename)){
		if($ext == "xlsx" || $ext == "xls"){
			$reader = PHPExcel_IOFactory::createReader('Excel5');
		}else{
			$message .= "文件类型不对！";
		}	
	}else{
		$message .= "数据读取失败，文件不存在！";
	}
	
	
	$PHPExcel = $reader->load($filename); // 载入文件
	$sheet = $PHPExcel->getSheet(0); // 读取第一個工作表  
	$highestRow = $sheet->getHighestRow(); // 取得总行数  
	$highestColumm = $sheet->getHighestColumn(); // 取得总列数  
	$arr = array(1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L', 13 => 'M', 14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S', 20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z');
	
	//echo "行数：".$highestRow."//列数：".$highestColumm."<br/>";
	//
	///** 循环读取每个单元格的数据 */
	//echo "<table border='1'>";
	//for($row=1;$row<=$highestRow;$row++){//行循环
	//	echo "<tr>";
	//	for($column = 'A';$column <= 'D';$column++){//列循环
	//		echo "<td>".$sheet->getCell($column.$row)->getValue()."</td>";
	//	}
	//	echo "</tr>";
	//}
	//echo "</table>";
	
	
	
	$getdataforexcel = "";//装载Excel数据的数组
	
	for($row=2;$row<=$highestRow;$row++){//行循环
		for($column = 1;$column<=4;$column++){
			$getdataforexcel[$row-1][] = $sheet->getCell($arr[$column].$row)->getValue();
		}
	}
	
	//print_r($getdataforexcel);
	$error_dataforexcel = "";//无法保存到数据库的Excel的数据
	$error_num = 0;//失败数量
	
	//判断数组长度大于1
	if(count($getdataforexcel)>1){
		$state = 1;
		$message = "导入成功";
		//---------------------------------------------------将数据保存到数据库----------------------------------------------------------------------------------------------------
		foreach($getdataforexcel as $index_num =>$datainfo){
			//清除空格字符
			foreach($datainfo as $ky => $data){
				$datainfo[$ky] = Settle_string($datainfo[$ky]);
			}
			//检测手机号是否为空
			if(!empty($datainfo[1])){
				//检测用户手机号是否注册
				$sql = "SELECT `id` FROM `user` WHERE `userphone`='".$datainfo[1]."'";
				$result = $conn->query($sql);
				if($result->num_rows>0){
					$sql = "UPDATE `user` SET `usergroup`='".$datainfo[3]."' WHERE `userphone`='".$datainfo[1]."'";
					if(!$conn->query($sql)){
						$error_dataforexcel[$error_num] = $datainfo;
						$error_dataforexcel[$error_num]["error_message"] = "服务器错误";
						$error_num++;
					}
				}else{
					$error_dataforexcel[$error_num] = $datainfo;
					$error_dataforexcel[$error_num]["error_message"] = "手机号未注册";
					$error_num++;
				}
			}else{
				$error_dataforexcel[$error_num] = $datainfo;
				$error_dataforexcel[$error_num]["error_message"] = "手机号为空";
				$error_num++;
			}
		}
		
		$conn->close();
		//判断错误数组大于0
		if(count($error_dataforexcel)){
			$redata = '{"errorstate":"TRUE"}';
			//--------------------------------------------------------将导入失败数据写入Excel表----------------------------------------------------------------------------------------
			//创建Excel对象
			$objPHPExcel = new PHPExcel(); 
			//Set properties 设置文件属性  这部分随意
			$objPHPExcel->getProperties()->setCreator("KingShen");  
			$objPHPExcel->getProperties()->setLastModifiedBy("KingShen");  
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test 用户信息导入");  
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");  
			$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLS,用户信息导入失败");  
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");  
			$objPHPExcel->getProperties()->setCategory("Test result file"); 
			//Rename sheet 重命名工作表标签  
			$objPHPExcel->getActiveSheet()->setTitle('sheet1');  
			/*写进头部*/
			$letter = array('A','B','C','D','E');
			//Set column widths 设置列宽度 
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
			
			//编写表字段
			$objPHPExcel->getActiveSheet()->setCellValue('A1','序号');
			$objPHPExcel->getActiveSheet()->setCellValue('B1','账号（手机）');
			$objPHPExcel->getActiveSheet()->setCellValue('C1','姓名');
			$objPHPExcel->getActiveSheet()->setCellValue('D1','分组');
			$objPHPExcel->getActiveSheet()->setCellValue('E1','错误信息');
			
			//居中
			foreach($letter as $ky => $column){
				$objPHPExcel->getActiveSheet()->getStyle($column.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
				$objPHPExcel->getActiveSheet()->getStyle($column.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中 	
			}
			
			$i = 2;
			foreach($error_dataforexcel as $ky_my => $errordatainfo){
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$errordatainfo[0]);
				$objPHPExcel->getActiveSheet()->setCellValueExplicit('B'.$i,$errordatainfo[1],PHPExcel_Cell_DataType::TYPE_STRING);//显示字符串
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$errordatainfo[2]);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$errordatainfo[3]);
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$errordatainfo["error_message"]);
				
				$i++;
			}
			
			//保存
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);  
			//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
			$objWriter->save("file_excel/ImportErrordata.xlsx");
		}else{
			$redata = '{"errorstate":"FALSE"}';
		}
	}else{
		$message .= "Excel表格没有数据";
	} 
}else{
	$message .= "接收文件失败";
}

if(empty($redata)){
	$redata = '""';
}
$json = '{"state":"'.$state.'","message":"'.$message.'","data":'.$redata.'}';
echo $json;

?>