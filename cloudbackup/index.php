<?php include("lib/Bootstrap.php"); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $title ?></title>
<link rel="stylesheet" href="css/reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
<script src="js/jquery.js" type="text/javascript" language="javascript"></script>
<script src="js/default.js" type="text/javascript" language="javascript"></script>
<meta content="Ben" name="author" />
<meta name="robots" content="noindex, nofollow" />
<meta content="English" name="language" />
</head>
<body>
<div id="header">
    <div id="header_pad"></div><!-- end header_pad -->
    <div class="page-width">
        <div id="title-bar">
            <a href="index.php" class="head"> Cloud Backup </a>
            <ul>
                <?php if(!empty($_SESSION['loggedin'])){ echo '<li>Welcome <a href="#">'.$config['username'].'</a></li>
                <li class="spacer"><img class="shadows spacer" src="images/x.gif"></li>
                <li><a href="index.php?page=logout" title="Logout"> Logout </a></li>
                '; }?>
            </ul>
        </div><!-- end title-bar -->

<?php if(!empty($_SESSION['loggedin'])){ 
?>
        <div id="navigation">
            <ul>
               <li><a href="?page=filebackup" title="File Backup"> File Backups</a></li>
               <li><a href="index.php?page=deploy" title="Upload Files">Upload Files</a></li>
               <li><a href="index.php?page=database" title="Db Backup"> Db Backup</a></li>
               <li><a href="index.php?page=import" title="Db Import"> Db Import</a></li>
               <li><a href="index.php?page=config" title="Settings"> Settings </a></li>
            </ul>
        </div><!-- end navigation -->
<?php } ?>
    </div><!-- end page-width -->
    
</div><!-- end header  -->
<div id="wrapper">
   <div class="page-width">
      <?php if($current_page != "home" && $current_page != "login"){ ?>
      <a class="bluebutton" href="index.php">
      <span class="btn-text">« Back </span>
      <span class="btn-icon"></span>
      </a>
       <?php } ?>

      <div id="maincontent">
         <?php include($current_page_path); ?>
      </div>
   </div>
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-2072779-17");
pageTracker._trackPageview();
} catch(err) {}</script>

</body>
</html> 