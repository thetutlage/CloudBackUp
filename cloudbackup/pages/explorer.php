<?php

$File->SetPath($Server->GetWebPath());
$files = $File->Listing(array(), array(), array(), false);

?>
<div class="toolblock big">
	<img src="images/folderbig.png" alt="Archive" />
    <span>current path location</span>
  <h3>/test/gaan/php</h3>
    <a href="#" onclick="doFileBackup();return false;" class="button" id="runButton">Download</a>
    <img src="images/ajaxbig.gif" class="loader" alt="Loading" id="loadImage" style="display:none" />
</div>
<div class="spacer"><!--SPACER--></div>
<div class="successbox" id="success" style="display:none">New backup is created</div>
<div class="warningbox" id="warning" style="display:none">New backup is created</div>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="base" id="resultTable">
<thead>
  <tr>
  	<th width="5%"></th>
    <th width="35%">Name</th>
    <th width="30%">Size</th>
    <th width="10%">Download</th>
    <th width="10%">Edit</th>
    <th width="10%">Delete</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach($files as $file){ ?>
  <tr class="row_<?php echo md5($file['filename']) ?>">
  	<?php if($file['type'] == "file"){ ?>
        <td><img src="images/file.png" width="16" height="16" alt="file" /></td>
        <td><a class="image" href="<?php echo $file['fullpath']; ?>"><?php echo $file['filename']; ?></a></td>
        <td><?php echo $Server->GetByteFormat($file['size']) ?></td>
        <td align="right"><a href="download.php?file=<?php echo $file['filename']; ?>" class="button">Download</a></td>
        <td align="right"><a href="#" onclick="doDelete('<?php echo $file['filename']; ?>');return false;" class="button">Edit</a></td>
        <td align="right"><a href="#" onclick="doDelete('<?php echo $file['filename']; ?>');return false;" class="button">Delete</a></td>
    <?php }else{ ?>
    	<td><img src="images/folder.png" width="16" height="16" alt="directory" /></td>
        <td><?php echo $file['name']; ?></td>
        <td>-</td>
        <td align="right"><a href="download.php?file=<?php echo $file['filename']; ?>" class="button">Download</a></td>
        <td align="right"><a href="#" onclick="doDelete('<?php echo $file['filename']; ?>');return false;" class="button">Edit</a></td>
        <td align="right"><a href="#" onclick="doDelete('<?php echo $file['filename']; ?>');return false;" class="button">Delete</a></td>
    <?php } ?>
  </tr>
  <?php } ?>
  </tbody>
</table>
