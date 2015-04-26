<?php
	require_once('PHPExcel/PHPExcel.php');
	define('ENCRYPTION_KEY', 'YOUR_ENCRYPTION_KEY');
	define("host", "mysql:host=localhost;dbname=your_db");
	define("user_name", "your_name");
	define("user_pwd", "your_pwd");
	define("key", "YOUR_KEY");
	
	function excel_writer($row_data_excel)
	{
		$objPHPEXCEL = new PHPExcel();
		$row_len = count($row_data_excel);
		$row_count = 0;
		$myWorkSheet = new PHPExcel_WorkSheet($objPHPEXCEL, "基本資料");
		$objPHPEXCEL -> addSheet($myWorkSheet, 0);
		$objPHPEXCEL -> getSheet(0) -> setCellValueByColumnAndRow(0, 1, "姓名");
		$objPHPEXCEL -> getSheet(0) -> setCellValueByColumnAndRow(1, 1, "身分證字號");
		$objPHPEXCEL -> getSheet(0) -> setCellValueByColumnAndRow(2, 1, "出生年月日");
		
		$objPHPEXCEL -> getSheet(0) -> getColumnDimension('B')->setWidth(30);
		$objPHPEXCEL -> getSheet(0) -> getColumnDimension('C')->setWidth(30);
		
		$column = 2;
		while($row_count<$row_len)
		{
			$objPHPEXCEL -> getSheet(0) -> setCellValueByColumnAndRow(0, $column, $row_data_excel[$row_count]["name"]);
			$objPHPEXCEL -> getSheet(0) -> setCellValueByColumnAndRow(1, $column, $row_data_excel[$row_count]["ID"]);
			$objPHPEXCEL -> getSheet(0) -> setCellValueByColumnAndRow(2, $column, $row_data_excel[$row_count]["date"]);
			$row_count++;
			$column++;
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPEXCEL, 'Excel5');
		$objWriter -> save("/pathto/基本資料.xls");
		$objPHPEXCEL -> disconnectWorksheets();
		unset($objPHPEXCEL);
		return "/pathto/基本資料.xls";
	}
	
	// Decrypt Function
	function mc_decrypt($decrypt, $key)
	{
		$decrypt = explode('|', $decrypt.'|');
		$decoded = base64_decode($decrypt[0]);
		$iv = base64_decode($decrypt[1]);
		if(strlen($iv)!==mcrypt_get_iv_size(MCRYPT_TripleDES, MCRYPT_MODE_CBC))
			return false;
		$key = pack('H*', $key);
		$decrypted = trim(mcrypt_decrypt(MCRYPT_TripleDES, $key, $decoded, MCRYPT_MODE_CBC, $iv));
		$mac = substr($decrypted, -64);
		$decrypted = substr($decrypted, 0, -64);
		$calcmac = hash_hmac('sha256', $decrypted, substr(bin2hex($key), -32));
		if($calcmac!==$mac)
			return false;
		$decrypted = unserialize($decrypted);
		return $decrypted;
	}
	
	$response = null;
	$in_key = null;
	$token = null;
	if(!empty($_POST["data"]))
		$data = $_POST["data"];
	
	$link_db = null;
	try
	{
		$link_db = new PDO(host, user_name, user_pwd);
	}
	catch(PDOEcxception $e)
	{
		$link_db = null;
	}
	
	if($link_db!=null)
	{
		$link_db -> query("SET NAMES utf8");
		$in_key = $data[0]["key"];
		$token = $data[0]["accessToken"];
		
		if($in_key!==key)
		{
			$response = "key-error";
		}
		else if($token==null)
		{
			$response = "token-error";
		}
		else
		{
			$sql = "SELECT name,ID,date FROM passport_data";
			$result = $link_db -> query($sql);
			$row_data_excel = array();
			$row_i = 0;
			
			while($res = $result -> fetch())
			{
				$row_data_excel[$row_i]["name"] = $res["name"];
				$row_data_excel[$row_i]["ID"] = mc_decrypt($res["ID"], ENCRYPTION_KEY);
				$row_data_excel[$row_i]["date"] = $res["date"];
				$row_i++;
			}
			$link_db = null;
			$response = excel_writer($row_data_excel);
		}
	}
	else
	{
		$response = "cannot link db";
	}
	
	echo json_encode($response);
?>