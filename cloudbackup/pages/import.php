<?php

// Security
if(!defined("WRAPPER")){ echo "You cannot load a page directly!"; exit; }

$error = "";
$success = false;

// Check if database is connectable
$db_valid = true;
$host = $config['db.host'];
$user = $config['db.username'];
$pass = $config['db.password'];
$name = $config['db.name'];
$port = $config['db.port'];
if($port == "") $port = 3306;
$link = @mysql_connect($host.":".$port,$user,$pass);
if(!$link){
	$db_valid = false;
}
if(function_exists("mysql_set_charset")){
	@mysql_set_charset($config['db.charset'], $link);
}
if(!@mysql_select_db($name,$link)){
	$db_valid = false;
}


$success = false;
if(isset($_POST['import'])){
	if(strtolower(substr($_FILES["file"]["name"],-3)) == "sql"){
		$newfilepath = $Server->GetTempPath() . $_FILES["file"]["name"];
		move_uploaded_file($_FILES["file"]["tmp_name"], $newfilepath);
		if(file_exists($newfilepath)){
			if($name != $_POST['sltdatabase'] && !@mysql_select_db($_POST['sltdatabase'],$link)){
				$error = "Could not select database.";
			}else{
				$content = file_get_contents($newfilepath);
				$lines	= explode("\n", $content);
				$clean_content = "";
				foreach($lines as $line){
					if(substr(trim($line),0, 2) != "--"){
						$clean_content .= $line;
					}
				}
				
				$queries = explode(";", $clean_content);
				$current_query = 0;
				$clean_queries = array();
				$keyword_hits = array("select", "update", "insert", "drop", "create", "show");
				foreach($queries as $query){
					$parts = explode(" ", strtolower(trim($query)));
					if(is_array($parts) && !in_array($parts[0], $keyword_hits)){
						$clean_queries[($current_query-1)] .= ";" . $query;
						//echo $query . "**<br /><br />";
					}else{
						$clean_queries[$current_query] = $query;
						$current_query++;
					}
					
				}
				
				$error_queries = "";
				foreach($clean_queries as $query){
					if($result = mysql_query($query)){
							
					}else{
						if(trim($query) != ""){
							$error_queries .= '<div class="warningbox">';
							$error_queries .= htmlspecialchars($query);
							$error_queries .= '<br /><br /><strong>MySQL Reported this as the problem</strong>:<br />' . mysql_error() . '';			
							$error_queries .= "</div>";
						}
					}
				}
				@unlink($newfilepath);
				$success = true;
				
			}
		}else{
			$error = "Could not upload the SQL file.";
		}
	}else{
		$error = "You can only import SQL files.";
	}
}

if($db_valid){
	$tables = array();
	$result = @mysql_query('SHOW DATABASES');
	while($row = @mysql_fetch_row($result))
	{
		$databases[] = $row[0];
	}
}


?>
<?php if(!$db_valid){ ?>
<div class="warningbox">Could not connect to your database server. Check if the credentials in the config file are correct.</div>
<?php }else{ ?>
<div class="warningbox">File Needs To Be A Sql File</div>
<form action="" method="post" enctype="multipart/form-data" name="frmdeploy" id="frmdeploy">
<div class="toolblock big">
  <input name="file" type="file" class="left" id="file" size="20" />
	<select name="sltdatabase" id="sltdatabase" class="left2">
      <?php foreach($databases as $database){ ?>
        <option value="<?php echo $database ?>" <?php if($database == $name) echo 'selected="selected"' ?>><?php echo $database ?></option>
        <?php } ?>
    </select> 
  <input type="submit" name="import" id="import" class="button" value="Import" />
</div>
</form>
<div class="spacer"><!--SPACER--></div>
<?php if(!empty($error)){ ?>
<div class="warningbox"><?php echo $error ?></div>
<?php } ?>
<?php if($success && empty($error_queries)){ ?>
<div class="successbox">Importing the SQL is successfully finished.</div>
<?php } ?>
<?php if($success && !empty($error_queries)){ ?>
<div class="successbox">Importing the SQL is finished but some queries failed.</div>
<p></p>
<h2>Failed queries</h2>
<?php echo $error_queries; ?>
<?php } ?>
<?php } ?>