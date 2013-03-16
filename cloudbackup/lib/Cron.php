<?php

session_start();
$_SESSION['loggedin'] = true;

include("Bootstrap.php");
include("class.phpmailer.php");
include("Error.php");

// Config mailer

//now Attach all files submitted
/*foreach($attachments as $key => $value) { //loop the Attachments to be added ...
$mail->AddAttachment("uploads"."/".$value);
}*/

if(isset($_GET['password']) && $_GET['password'] != $config['password']) exit();

function fileBackup($email=""){
	global $Server;
	$name = $Server->CommandFilebackup();
	if($name == "exists") return false;
	if(!empty($email)){
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = "localhost";
		$mail->SMTPAuth = false;
		$mail->From = "noreply@sitebase.be";
		$mail->FromName = "Advanced Backup System";
		$mail->AddReplyTo("noreply@sitebase.be");
		$mail->WordWrap = 50;
		$mail->IsHTML(false);
		$mail->Subject = "Abs Server Backups";
		$mail->AddAttachment($Server->GetBackupPath() . $name . ".zip");
		$mail->AddAddress($email);
		$mail->Body = "Backups included:\n- " . $name . ".zip";
		$mail->Send();
	}
	return $name . ".zip";
}

function databaseBackup($names=array(), $email=""){
	global $Server;
	$backups = array();
	
	if(!empty($email)){
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = "localhost";
		$mail->SMTPAuth = false;
		$mail->From = "noreply@sitebase.be";
		$mail->FromName = "Advanced Backup System";
		$mail->AddReplyTo("noreply@sitebase.be");
		$mail->WordWrap = 50;
		$mail->IsHTML(false);
		$mail->Subject = "Abs Server Backups";
		$body = "Backups included:\n";
	}
	foreach($names as $name){
		$backup = $Server->CommandMysqlbackup(array("database" => $name));
		if(!empty($email)){
			$mail->AddAttachment($Server->GetBackupPath() . $backup);
			$body .= "- " . $backup . "\n";
		}
		$backups[] = $backup;
	}
	if(!empty($email)){
		$mail->AddAddress($email);
		$mail->Body = $body;
		$mail->Send();
	}
	return $backups;
}

function databaseRollback($names=array()){
	global $Server;
	$backups = array();
	foreach($names as $file => $dbname){
		$backups[] = $Server->CommandMysqlRollback(array("file" => $file, "dbname" => $dbname));
	}
	return $backups;
}

function fileRollback($name){
	global $Server;
	global $File;
	
	// Delete old content
	$File->SetPath($Server->GetWebPath());
	$result = $File->Listing(array(), array(), array("Abs"));
	foreach($result as $item){
		$File->SetPath($item['fullpath']);
		$File->Delete();
	}
	if($Server->CommandRollback(array("backup" => $name))){
		return true;
	}else{
		return false;
	}
}