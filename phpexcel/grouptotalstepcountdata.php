<?php
/*StepcountExport
 * 导出心率的数据
 * 
 * */
include 'Classes/PHPExcel.php';
require_once('Classes/PHPExcel/Writer/Excel2007.php'); 
include '../conn.php';

//接收数据
$startdate = isset($_GET["startdate"]) ? $_GET["startdate"] : "2018-09-01";
$enddate = isset($_GET["enddate"]) ? $_GET["enddate"] : date("Y-m-d");
if(strtotime($startdate) > strtotime($enddate)){
	$temp = $startdate;
	$startdate = $enddate;
	$enddate = $temp;
}

//创建Excel对象
$objPHPExcel = new PHPExcel(); 
//Set properties 设置文件属性  这部分随意
$objPHPExcel->getProperties()->setCreator("KingShen");  
$objPHPExcel->getProperties()->setLastModifiedBy("KingShen");  
$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test StepcountExport");  
$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");  
$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX,StepcountExport");  
$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");  
$objPHPExcel->getProperties()->setCategory("Test result file"); 
//Rename sheet 重命名工作表标签  
$objPHPExcel->getActiveSheet()->setTitle('sheet1');  
/*写进头部*/
$letter = array('A','B','C','D','E','F','G','H','I','J','K');
//Set column widths 设置列宽度 
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);  
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
//编写表字段
$objPHPExcel->getActiveSheet()->setCellValue('A1','分组');
$objPHPExcel->getActiveSheet()->setCellValue('B1','截止日期');
$objPHPExcel->getActiveSheet()->setCellValue('C1','总步数');
$objPHPExcel->getActiveSheet()->setCellValue('D1','总分数');
//居中
foreach($letter as $ky => $column){
	$objPHPExcel->getActiveSheet()->getStyle($column.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle($column.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中 	
}

//$sql_s = "SELECT usergroup,SUM(step_count) AS step_count,SUM(score) AS score,SUM(calorie) AS calorie,MAX(record_date) AS record_date FROM allstepcount GROUP BY usergroup  ORDER BY usergroup,record_date DESC;";	
$sql_s = "SELECT usergroup,SUM(step_count) AS step_count,SUM(score) AS score,SUM(calorie) AS calorie,MAX(record_date) AS record_date FROM allstepcount WHERE record_date BETWEEN '".$startdate."' AND '".$enddate."' GROUP BY usergroup  ORDER BY usergroup,record_date DESC;";
$result = $conn->query($sql_s);
$i=2;
if($result->num_rows>0){
	while($row = $result->fetch_assoc()){
		$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,$row['usergroup'],PHPExcel_Cell_DataType::TYPE_STRING);//显示字符串
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$row['record_date']);
		$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$i,$row['step_count']);//显示字符串
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$row['score']);
		
		//日期格式化
		$objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
		$i++;
	}
}else{
	exit("没有数据！");
}	
$conn->close();

//保存
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);  
//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
$objWriter->save("file_excel/Stepcountdata.xlsx");



//输出下载
sleep(1);
//$filename = "file_excel/Stepcountdata.xlsx";
$name = "分组总步数数据".$startdate."_".$enddate.".xlsx"; 
//if(file_exists($filename)){
//	header('content-disposition:attachment;filename='.$name);
//	header('content-length:'.filesize($filename));
//	readfile($filename);
//}else{
//	echo '<script type="text/javascript">alert("文件已被删除或移动了！");window.close();</script>';
//}

header("Location:../downloadonefile.php?filepath=phpexcel/file_excel/Stepcountdata.xlsx&filename=".$name)

?>