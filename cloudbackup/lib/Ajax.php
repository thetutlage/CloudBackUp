<?php

session_start();
error_reporting(0);

// If no loggedin
if(!$_SESSION['loggedin']){
	echo "You're not allowed to execute actions.";
	exit();
}

// Valid functions
$valid_functions = array("filebackup", "download", "delete", "rollback", "databasebackup", "databaserollback");

$function = $_POST['function'];

// Block if function doesn't exist
if(!in_array($function, $valid_functions)) error("Invalid function");

// Create main Server ABS instance
include("Server.php");
include("Error.php");
include("Encryption.php");
include("Response.php");
include("Request.php");
include("File.php");
if(file_exists("config.php")){
	include("config.php");
}else{
	include("../config.php");
}

// Set memory limit
ini_set('memory_limit', $config['memory'] . 'M');

// Json include
if(!function_exists("json_encode")){
	include("Json/Encoder.php");
}
if(!function_exists("json_decode")){
	include("Json/Decoder.php");
}

// Create explorer instance
$File = new Explorer();

// Create server object and load config
$Server = new Server($config);

// Remove old plczip temp files
$File->SetPath($Server->GetPath() . "lib/");
$listing = $File->Listing(array(), array(), array(), false);
foreach($listing as $item){
	if(isset($item['extension']) && $item['extension'] == "tmp"){
		$File->SetPath($item['fullpath']);
		$File->Delete();
	}
}

// Set timezone
date_default_timezone_set($config['timezone']);

switch($function){
	case "filebackup":
		$result = $Server->CommandFilebackup();
		if($result == "exists"){
			error("You can't create more backups then 1 per minute.");
		}
		$data['size'] = $Server->GetByteFormat(filesize($Server->GetBackupPath() . $result . ".zip"));
		$data['name'] = $result . ".zip";
		$data['md5'] = md5($result . ".zip");
		$data['date'] = $Server->GetDatePart($result);
		$data['time'] = $Server->GetTimePart($result);
		$data['message'] = "New backup created successfully.";
		success($data);
		break;
	case "download":
		$file = $_POST['file'];
		$file = str_replace(array("..", "/", "\\"), "", $file);
		$parts = explode(".", $file);
		$extension = end($parts);
		$filepath = $Server->GetBackupPath() . $file;
		if(file_exists($filepath) && is_file($filepath)){
			if($extension == "zip"){
				header('Content-type: application/zip');
				header('Content-Disposition: attachment; filename="test.zip"');
				readfile("C:/xampp/htdocs/Themeforest/Abs/Script/Abs/backup/image_20100211_2210.zip");
				exit();
			}
		}
		error("You're not allowed to download this file or the file doens't exist anymore.");
		break;
	case "delete":
		$file = $_POST['file'];
		$file = str_replace(array("..", "/", "\\"), "", $file);
		$parts = explode(".", $file);
		$extension = end($parts);
		$filepath = $Server->GetBackupPath() . $file;
		if(file_exists($filepath) && is_file($filepath)){
			$File->SetPath($filepath);
			if($File->Delete()){
				$data['message'] = "File is deleted succesfull";
				$data['file'] = $file;
				$data['md5'] = md5($file);
				success($data);
			}else{
				error("Can't delete this file.");
			}
		}
		error("You're not allowed to delete this file or the file doens't exist anymore.");
		break;
	case "rollback":
		$file = cleanFile($_POST['file']);
		
		// Delete old content
		/*
		$File->SetPath($Server->GetWebPath());
		$result = $File->Listing(array(), array(), array("Abs"));
		foreach($result as $item){
			$File->SetPath($item['fullpath']);
			$File->Delete();
		}*/
		
		if($Server->CommandRollback(array("backup" => $file))){
			$data['message'] = "Successfully rolled back to the backup of " . $Server->GetDatePart($file) . " on " . $Server->GetTimePart($file) . ".";
			$data['file'] = $file;
			$data['md5'] = md5($file);
			success($data);
		}else{
			error("Can't rollback to this backup.");
		}
		break;
	case "databasebackup":
		$database = $_POST['database'];
		$result = $Server->CommandMysqlbackup(array("database" => $database));
		if($result){
			$data['message'] = "Database backup is created successfully.";
			$data['file'] = $result;
			$data['database'] = $database;
			$data['size'] = $Server->GetByteFormat(filesize($Server->GetBackupPath() . $result));
			$data['md5'] = md5($result . ".sql");
			$data['name'] = $Server->GetDatabaseName($result);
			$data['date'] = $Server->GetDatePart($result);
			$data['time'] = $Server->GetTimePart($result);
			success($data);
		}else{
			error("Can't create database backup.");
		}
		break;
	case "databaserollback":
		$file = cleanFile($_POST['file']);
		if($Server->CommandMysqlRollback(array("file" => $file))){
			$data['message'] = "Database rolledback successfully.";
			$data['file'] = $file;
			$data['md5'] = md5($_POST['file']);
			success($data);
		}else{
			error("Could not find selected file.");
		}
}


function success($data){
	$response['code'] = 200;
	$response['data'] = $data;
	echo json_encode($response);
	exit();
}

function error($message, $data=""){
	$response['code'] = 400;
	$response['message'] = $message;
	if($data != ""){
		$response['data'] = $data;
	}
	echo json_encode($response);
	exit();
}

function cleanFilePath($file){
	global $Server;
	$file = str_replace(array("..", "/", "\\"), "", $file);
	$parts = explode(".", $file);
	$extension = end($parts);
	$filepath = $Server->GetBackupPath() . $file;
	return $filepath;
}

function cleanFile($file){
	$file = str_replace(array("..", "/", "\\"), "", $file);
	return $file;
}
