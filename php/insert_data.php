<?php
	// Define a 32-byte (64 character) hexadecimal encryption key
	// Note: The same encryption key used to encrypt the data must be used to decrypt the data
	define('ENCRYPTION_KEY', 'E972AE0548B036');
	define("host", "mysql:host=localhost;dbname=your_db");
	define("user_name", "your_name");
	define("user_pwd", "your_pwd");
	// Encrypt Function
	function mc_encrypt($encrypt, $key)
	{
		$encrypt = serialize($encrypt);
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_TripleDES, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM);
		$key = pack('H*', $key);
		$mac = hash_hmac('sha256', $encrypt, substr(bin2hex($key), -32));
		$passcrypt = mcrypt_encrypt(MCRYPT_TripleDES, $key, $encrypt.$mac, MCRYPT_MODE_CBC, $iv);
		$encoded = base64_encode($passcrypt).'|'.base64_encode($iv);
		return $encoded;
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
	
	$name = null;
	$id = null;
	$date = null;
	$data = null;
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
		if($data == null)
			$response = "post-error";
		else
		{
			$name = $data[0]["name"];
			$id = $data[0]["id"];
			$date = $data[0]["date"];
			
			$date_arr = explode("-", $date);
			if(count($date_arr)!=3)
			{
				$response = "post-error";
			}
			else if(strlen($date_arr[0])>4)
			{
				$response = "date-error";
			}
			else if(strlen($date_arr[1])>2 || strlen($date_arr[2])>2)
			{
				$response = "date-error";
			}
			else
			{
				$sql = "SELECT COUNT(*) FROM passport_data WHERE name = :name";
				$stmt = $link_db -> prepare($sql);
				$stmt -> execute(array(":name"=>$name));
				if((int)$stmt -> fetchColumn()==1)
				{
					$response = "has-inserted";
				}
				else
				{
					$sql = "INSERT INTO passport_data(name,ID,date) VALUES(:name,:ID,:date)";
					$stmt = $link_db -> prepare($sql);
					$stmt -> execute(array(":name"=>$name,":ID"=>mc_encrypt($id, ENCRYPTION_KEY),":date"=>$date));
					$response = "store-success";
				}
			}
		}
		$link_db = null;
	}
	else
		$response = "cannot link db";
	
	echo json_encode($response);
?>