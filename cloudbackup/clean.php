<?php 

include("lib/Bootstrap.php"); 
$File->SetPath($Server->GetBackupPath());
$listing = $File->Listing(array(), array(), array(), false);
$deleted = array();
foreach($listing as $item){
	if($item['type'] == "dir"){
		$File->SetPath($item['fullpath']);
		$File->Delete();
		$deleted[] = $item['fullpath'];
	}else{
		$parts = explode(".", $item['fullpath']);
		if(end($parts) == "small"){
			$File->SetPath($item['fullpath']);
			$File->Delete();
		}
	}
}

echo json_encode($deleted);

?>