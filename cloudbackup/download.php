<?php

// Include error reporting
include("lib/Error.php");

// Main Server instance
include("lib/Server.php");
include("config.php");
$Server = new Server($config);

$file = $_GET['file'];

$file = str_replace(array("..", "/", "\\"), "", $file);
$parts = explode(".", $file);
$extension = end($parts);
$filepath = $Server->GetBackupPath() . $file;
if(!file_exists($filepath) || !is_file($filepath)) exit();

// Compress file if it is a sql
if($extension == "sql"){
	
	/*$zip = new ZipArchive();
	$opened = $zip->open( $Server->GetBackupPath() . $file . ".small" , ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
	$zip->addFromString( $file, file_get_contents($Server->GetBackupPath() . $file));
	$zip->close();*/
	
	@unlink($Server->GetBackupPath() . $file . ".small");
	$zip = new PclZip($Server->GetBackupPath() . $file . ".small");
	if (substr($Server->GetBackupPath(), 1,1) == ':') {
		$remove = substr($Server->GetBackupPath(), 2);
	}
  	$zip->add($Server->GetBackupPath() . $file, PCLZIP_OPT_REMOVE_ALL_PATH);
	$read_file = $Server->GetBackupPath() . $file . ".small";	
	
}else{
	$read_file = $Server->GetBackupPath() . $file;	
}

header('Content-type: application/zip');
header('Content-Disposition: attachment; filename="' . str_replace(".sql", ".zip", $file) . '"');
echo file_get_contents($read_file);