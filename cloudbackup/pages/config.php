<?php

// Security
if(!defined("WRAPPER")){ echo "You cannot load a page directly!"; exit; }

$charsets = array("armscii8", "ascii", "big5", "binary", "cp1250", "cp1251", "cp1256", "cp1257", "cp850", "cp852", "cp866", "cp932", "dec8", "eucjpms", "euckr", "gb2312", "gbk", "geostd8", "greek", "hebrew", "hp8", "keybcs2", "koi8u", "latin1", "latin2", "latin5", "latin7", "macce", "macroman", "sjis", "swe7", "tis620", "ucs2", "ujis", "utf8");
$saved = false;
if(isset($_POST['btnsave'])){


	$host 			= htmlspecialchars($_POST['txthost']);
	$username 		= htmlspecialchars($_POST['txtusername']);
	$password 		= htmlspecialchars($_POST['txtpassword']);
	$name 			= htmlspecialchars($_POST['txtname']);
	$port 			= htmlspecialchars($_POST['txtport']);
	$charset 		= htmlspecialchars($_POST['sltcharset']);
	$dirs 			= htmlspecialchars($_POST['txtdirs']);
	$files 			= htmlspecialchars($_POST['txtfiles']);
	$timezone 		= htmlspecialchars($_POST['txttimezone']);
	$memory 		= htmlspecialchars($_POST['txtmemory']);
	$maxbackups		= htmlspecialchars($_POST['txtmaxbackups']);
  $loginusername = htmlspecialchars($_POST['loginusername']);
  $loginpassword = htmlspecialchars($_POST['loginpassword']);

  if(empty($loginusername) || empty($loginpassword))
  {
    $saved = true;
    die;
  }

$config_content = '<?php $config[\'backuppath\'] = "/backup/";
$config[\'db.host\'] = "' . $host . '";
$config[\'db.username\'] = "' . $username . '";
$config[\'db.password\'] = "' . $password . '";
$config[\'db.name\'] = "' . $name . '";
$config[\'db.port\'] = "' . $port . '";
$config[\'db.charset\'] = "' . $charset . '";
$config[\'exclude.dir\'] = "' . $dirs . '";
$config[\'exclude.file\'] = "' . $files . '";
$config[\'autoarchive\'] = true;
$config[\'timezone\'] = "' . $timezone . '";
$config[\'username\'] = "' . $loginusername . '";
$config[\'password\'] = "' . $loginpassword . '";
$config[\'memory\'] = "' . $memory . '";
$config[\'maxbackups\'] = "' . $maxbackups . '";';


	file_put_contents($Server->GetPath() . "config.php", $config_content);
	$saved = true;
	
}else{
	$host 			= $config['db.host'];
	$username 		= $config['db.username'];
	$password 		= $config['db.password'];
	$name 			= $config['db.name'];
	$port 			= $config['db.port'];
	$charset 		= $config['db.charset'];
	$dirs 			= $config['exclude.dir'];
	$files 			= $config['exclude.file'];
	$timezone 		= $config['timezone'];
	$memory 		= $config['memory'];
	$maxbackups		= $config['maxbackups'];
  $loginusername = $config['username'];
  $loginpassword = $config['password'];
}

?>
<form id="frmconfig" name="frmconfig" method="post" action="">
<?php if($saved){ ?>
<div class="successbox">Configuration is saved succesfully.</div>
<?php } ?>

<h2> Login Information</h2>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="47%"><label for="txthost">Username</label></td>
    <td width="53%"><input name="loginusername" type="text" id="loginusername" value="<?php echo $loginusername ?>" size="40" /></td>
  </tr>
  <tr>
    <td width="47%"><label for="txthost">Password</label></td>
    <td width="53%"><input name="loginpassword" type="password" id="loginpassword" value="<?php echo $loginpassword ?>" size="40" />
    <a href="#" class="button" id="show_password"> Show password </a></td>
  </tr>
</table>

<h2>Database configuration</h2>
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="47%"><label for="txthost">Host</label></td>
    <td width="53%"><input name="txthost" type="text" id="txthost" value="<?php echo $host ?>" size="40" /></td>
  </tr>
  <tr>
    <td><label for="txtusername">Username</label></td>
    <td><input name="txtusername" type="text" id="txtusername" value="<?php echo $username ?>" size="20" /></td>
  </tr>
  <tr>
    <td><label for="txtpassword">Password</label></td>
    <td><input name="txtpassword" type="text" id="txtpassword" value="<?php echo $password ?>" size="20" /></td>

  </tr>
  <tr>
    <td><label for="txtname">Default database</label></td>
    <td><input name="txtname" type="text" id="txtname" value="<?php echo $name ?>" size="20" /></td>
  </tr>
  <tr>
    <td><label for="txtport">Port</label></td>
    <td><input name="txtport" type="text" id="txtport" size="6" value="<?php echo $port ?>" /></td>
  </tr>
  <tr>
    <td><label for="sltcharset">Charset</label></td>
    <td>
      <select name="sltcharset" id="sltcharset">
      <?php foreach($charsets as $charset_text){ ?>
        <option value="<?php echo $charset_text ?>" <?php if($charset_text == $charset) echo 'selected="selected"' ?>><?php echo $charset_text ?></option>
        <?php } ?>
     </select>    
    </td>
  </tr>
</table>

<h2>Exclude directories from backup</h2>
<p>Enter comma seperated directory names </p>
<p>
  <textarea name="txtdirs" id="txtdirs" cols="50" rows="5"><?php echo $dirs ?></textarea>
</p>
<h2>Exclude files from backup</h2>
<p>Enter comma seperated file names </p>
<p>
  <textarea name="txtfiles" id="txtfiles" cols="50" rows="5"><?php echo $files ?></textarea>
</p>
<p><input name="txttimezone" type="hidden" id="txttimezone" value="<?php echo $timezone ?>" size="60" /></p>
<h2>Memory limit</h2>
<p> 32 works with most of the websites. Increase only if you get corrupted backup files or website size is too large.</p>
<p><input name="txtmemory" type="text" id="txtmemory" value="<?php echo $memory ?>" size="10" />
</p>

<h2>Maximum backups files</h2>
<p> Maximum no of files you want to backup at one point of time. </p>
<p><input name="txtmaxbackups" type="text" id="txtmaxbackups" value="<?php echo $maxbackups ?>" size="10" />
</p>
<p>
  <input type="submit" class="button" name="btnsave" id="btnsave" value="Save configuration" />
</p>

<p>&nbsp; </p>
<p>&nbsp;</p>
</form>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/default.js"></script>
