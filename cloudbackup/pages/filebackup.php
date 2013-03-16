<?php

// Security
if(!defined("WRAPPER")){ echo "You cannot load a page directly!"; exit; }

$File->SetPath($Server->GetBackupPath());
$files = $File->Listing(array("sql", "small"), array(".htaccess", ".DS_Store"), array("__MACOSX"), false);

?>
<div class="toolblock big">
	<!--img src="images/archive.png" alt="Archive" /-->
    <h3>Start creating backup of all the files</h3>
      <a href="#" onclick="doFileBackup();return false;" class="bluebutton" id="runButton">
      <span class="btn-text"> Start </span>
      <span class="btn-icon"></span>
      </a>
    <img src="images/ajaxbig.gif" class="loader" alt="Loading" id="loadImage" style="display:none" />
</div>
<div class="spacer"><!--SPACER--></div>
<div class="successbox" id="success" style="display:none">New backup is created</div>
<div class="warningbox" id="warning" style="display:none">New backup is created</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="base" id="resultTable">
<thead>
  <tr>
  	<th width="5%">Type</th>
    <th width="25%">Date</th>
    <th width="25%">Time</th>
    <th width="15%">Size</th>
    <th width="10%">Download</th>
    <th width="10%">Delete</th>
    <th width="10%">Update</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach($files as $file){  if($file['type'] == "dir") continue;?>
  <tr class="row_<?php echo md5($file['filename']) ?>">
  	<td>Zip</td>
    <td><?php echo $Server->GetDatePart($file['filename']) ?></td>
    <td><?php echo $Server->GetTimePart($file['filename']) ?></td>
    <td><?php echo $Server->GetByteFormat($file['size']) ?></td>
    <td align="right">
    	<a href="download.php?file=<?php echo $file['filename']; ?>" class="button">Download</a>
    </td>
  	<td align="right">
    	<a href="#" id="<?php echo md5($file['filename']); ?>_delete_link" onclick="doDelete('<?php echo $file['filename']; ?>', '<?php echo md5($file['filename']); ?>');return false;" class="button">Delete</a>
        <img id="<?php echo md5($file['filename']); ?>_delete_load" class="loader" width="16" height="16" style="display: none;" alt="loader" src="images/ajax.gif"/>
    </td>
    <td align="right">
    	<a href="#" id="<?php echo md5($file['filename']); ?>_rollback_link" onclick="doRollback('<?php echo $file['filename']; ?>', '<?php echo md5($file['filename']); ?>');return false;" class="button">Update</a>
    	 <img id="<?php echo md5($file['filename']); ?>_rollback_load" class="loader" width="16" height="16" style="display: none;" alt="loader" src="images/ajax.gif"/>
    </td>
  </td>
  </tr>
  <?php } ?>
  <tr class="norows" <?php if(count($files) != 0) echo 'style="display:none"'; ?>><td colspan="7" class="warningbox">There are no file backups found on the server.</td></tr>
  </tbody>
</table>
