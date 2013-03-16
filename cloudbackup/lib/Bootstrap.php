<?php

if(!isset($_SESSION)){
	session_start();
}
ob_start();

if(!isset($_SESSION['loggedin'])){
	$_SESSION['loggedin'] = false;
}

// Define Wrapper
define("WRAPPER", true); 

// If page is logout
if(isset($_GET['page']) && $_GET['page'] == "logout"){
	session_destroy();
	$_SESSION['loggedin'] = false;
}

// If no loggedin
if(!$_SESSION['loggedin'] && $_GET['page'] != "login"){
	header("Location: index.php?page=login");
	exit();
}

// Includes
include("lib/File.php");
include("lib/Server.php");
include("config.php");

// Json include
if(!function_exists("json_encode")){
	include("lib/Json/Encoder.php");
}
if(!function_exists("json_decode")){
	include("lib/Json/Decoder.php");
}

// Set some execution parameters
ignore_user_abort(TRUE); 
ini_set('memory_limit', '-1');
ini_set('post_max_size', '10M');
set_time_limit(0); 

// Handle page loading
$current_page = isset($_GET['page']) && file_exists("pages/" . str_replace(array("..", "/", "\\"), "", $_GET['page']) . ".php") ? $_GET['page'] : "home";

switch($current_page){
	case "home":
		$title = "Menu";
		break;
	case "filebackup":
		$title = "File Backup";
		break;
	case "deploy":
		$title = "Deploy";
		break;
	case "database":
		$title = "Database Backup";
		break;
	case "config":
		$title = "Configuration";
		break;
	case "import":
		$title = "Database Import";
		break;
	case "login":
		$title = "Login";
		break;
}

$current_page_path = "pages/" . $current_page . ".php";

// Main Server instance
$Server = new Server($config);

// Set timezone
date_default_timezone_set($config['timezone']);

// Main File instance
$File = new Explorer();