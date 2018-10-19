<?php
/*StepcountExport
 * 导出心率的数据
 * 
 * */
include 'Classes/PHPExcel.php';
require_once('Classes/PHPExcel/Writer/Excel2007.php'); 
include '../conn.php';

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
//编写表字段
$objPHPExcel->getActiveSheet()->setCellValue('A1','序号');
$objPHPExcel->getActiveSheet()->setCellValue('B1','账号');
$objPHPExcel->getActiveSheet()->setCellValue('C1','名称');
$objPHPExcel->getActiveSheet()->setCellValue('D1','年龄');
$objPHPExcel->getActiveSheet()->setCellValue('E1','分组');
//居中
foreach($letter as $ky => $column){
	$objPHPExcel->getActiveSheet()->getStyle($column.'1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle($column.'1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//水平居中 	
}

$sql_s = "SELECT userphone,username,userage,usergroup FROM user ORDER BY usergroup;";	
$result = $conn->query($sql_s);
$i=2;
if($result->num_rows>0){
	$j = 1;
	while($row = $result->fetch_assoc()){
		$objPHPExcel->getActiveSheet()->setCellValueExplicit('A'.$i,$j,PHPExcel_Cell_DataType::TYPE_STRING);//显示字符串
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$row['userphone'],PHPExcel_Cell_DataType::TYPE_STRING);
		$objPHPExcel->getActiveSheet()->setCellValueExplicit('C'.$i,$row['username'],PHPExcel_Cell_DataType::TYPE_STRING);//显示字符串
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$row['userage']);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$row['usergroup'],PHPExcel_Cell_DataType::TYPE_STRING);
		
		//日期格式化
		$objPHPExcel->getActiveSheet()->getStyle('F'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
		$i++;
		$j++;
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
$name = "用户数据-导出时间".date("Y年m月d日").".xlsx"; 
//if(file_exists($filename)){
//	header('content-disposition:attachment;filename='.$name);
//	header('content-length:'.filesize($filename));
//	readfile($filename);
//}else{
//	echo '<script type="text/javascript">alert("文件已被删除或移动了！");window.close();</script>';
//}

header("Location:../downloadonefile.php?filepath=phpexcel/file_excel/Stepcountdata.xlsx&filename=".$name)

?>