<?php 

error_reporting(E_ALL);

function ErrorHandler($errno, $errstr, $errfile, $errline)
{

	if($errno < 3) return true;

	// Get current log file content
	if(file_exists(dirname(__FILE__) . "/error.log")){
		$content = file_get_contents(dirname(__FILE__) . "/error.log");
	}else{
		$fileHandle = fopen("error.log", 'w') or trigger_error("Can't create log file", E_USER_WARNING);
		fclose($fileHandle);
	}
	
	// Write new log line
	$content .= "[" . date("Y/m/d - h:i:s") . "] " . $errno . " - " . $errstr . " (" . $errfile . ":" . $errline . ")\n";
	
	// Write log to file
	try{
		file_put_contents( dirname(__FILE__) . "/error.log", $content);
	}catch(Exception $e){
		
	}
	
	return true;
}

set_error_handler("ErrorHandler");

?>