<?php

// Security
if(!defined("WRAPPER")){ echo "You cannot load a page directly!"; exit; }

$error = "";
$success = false;
if(isset($_POST['upload'])){
	if(strtolower(substr($_FILES["file"]["name"],-3)) == "zip"){
		$newfilepath = $Server->GetBackupPath() . $_FILES["file"]["name"];
		move_uploaded_file($_FILES["file"]["tmp_name"], $newfilepath);
		if($Server->CommandRollback(array("backup" => $_FILES["file"]["name"]))){
			unlink($newfilepath);
			$success = true;
		}else{
			unlink($newfilepath);
			$error = "There was an unkown error while deploying the ZIP.";
		}
	}else{
		$error = "The file to deploy must be a ZIP file.";
	}
}

?>
<form action="" method="post" enctype="multipart/form-data" name="frmdeploy" id="frmdeploy">
<div class="warningbox">File Needs To Be A <strong>Zip</strong> File</div>
<div class="toolblock big">
  <input name="file" type="file" class="left" id="file" />
  <input type="submit" name="upload" id="upload" class="button" value="Upload" />
</div>
</form>
<div class="spacer"><!--SPACER--></div>
<?php if(!empty($error)){ ?>
<div class="warningbox"><?php echo $error ?></div>
<?php } ?>
<?php if($success){ ?>
<div class="successbox">Deployment of the ZIP is finished succesfully</div>
<?php } ?>
