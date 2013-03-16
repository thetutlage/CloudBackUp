<?php

// Security
if(!defined("WRAPPER")){ echo "You cannot load a page directly!"; exit; }
if($_SESSION['loggedin']){
	header("Location: index.php?page=home");
	exit();
}

$error = "";
if(isset($_POST['btnlogin'])){
	if($config['username'] == $_POST['txtusername'] && $config['password'] == $_POST['txtpassword']){
		$_SESSION['loggedin'] = true;
		header("Location: index.php?page=home");
		exit();
	}else{
		$error = "Your username or password are wrong.";
		$username = $_POST['txtusername'];
	}
}else{
	$username = "";
}

?>
<h2>Login</h2>
<?php if(!empty($error)){ ?>
	<div class="warningbox"><?php echo $error ?></div>
<?php } ?>
<form id="frmlogin" name="frmlogin" method="post" action="">
<table width="100%" border="0" cellspacing="2" cellpadding="0">
  <tr>
    <td width="21%"><label for="txtusername">Username</label></td>
    <td width="39%"><input name="txtusername" type="text" id="txtusername" value="<?php echo $username ?>" size="20" /></td>
  </tr>
  <tr>
    <td><label for="txtpassword">Password</label></td>
    <td><input name="txtpassword" type="password" id="txtpassword" size="20" /></td>
    </tr>
</table>
<p>
  <input type="submit" class="button" name="btnlogin" id="btnlogin" value="Login" />
</p>
</form>
