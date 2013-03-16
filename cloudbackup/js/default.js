
function doFileBackup(){
	
	setElementState("#warning", false, true);
	setElementState("#success", false, true);
	setElementState("#runButton", false, false);
	setElementState("#loadImage", true, false);
	$.post("lib/Ajax.php", { 'function': "filebackup" },
	   function(response){
		   	if(response.code == "200"){
				setElementState("#runButton", true, false);
				setElementState("#loadImage", false, false);
				$("#success").html(response.data.message);
				setElementState("#success", true, true);
				$("#resultTable tbody .norows").hide();
				$("#resultTable tbody").append('<tr class="row_' + response.data.md5 + '"><td><img src="images/package.png" width="16" height="16" alt="database" /></td><td>' + response.data.date + '</td><td>' + response.data.time + '</td><td>' + response.data.size + '</td><td align="right"><a href="download.php?file=' + response.data.name + '" class="button">Download</a></td><td align="right"><a href="#"  id="' + response.data.md5 + '_delete_link" onclick="doDelete(\'' + response.data.name + '\',\'' + response.data.md5 + '\')" class="button">Delete</a><img id="' + response.data.md5 + '_delete_load" class="loader" width="16" height="16" style="display: none;" alt="loader" src="images/ajax.gif"/></td><td align="right"><a href="#" id="' + response.data.md5 + '_rollback_link" onclick="doRollback(\'' + response.data.name + '\',\'' + response.data.md5 + '\');return false;" class="button">Rollback</a><img id="' + response.data.md5 + '_rollback_load" class="loader" width="16" height="16" style="display: none;" alt="loader" src="images/ajax.gif"/></td></tr>');
			}else{
				$("#warning").html(response.message);
				setElementState("#warning", true, true);
				setElementState("#runButton", true, false);
				setElementState("#loadImage", false, false);
			}
	   }, "json");	
}

function doDownload(file){
	setElementState("#warning", false, true);
	setElementState("#success", false, true);
	$.post("lib/Ajax.php", { 'function': "download", file: file },
		function(response){
		   	if(response.code == "400"){
				$("#warning").html(response.message);
				setElementState("#warning", true, true);
			}
		}, "json");	
}

function doDelete(file, md5){
	setElementState("#warning", false, true);
	setElementState("#success", false, true);
	if(!confirm("Are you sure you want to delete " + file + "?")) return;
	setElementState("#" + md5 + "_delete_link", false, false);
	setElementState("#" + md5 + "_delete_load", true, false);
	$.post("lib/Ajax.php", { 'function': "delete", file: file },
		function(response){
		   	if(response.code == "200"){
				setElementState("#" + response.data.md5 + "_delete_load", false, false);
				setElementState("#" + response.data.md5 + "_delete_link", true, false);
				$("#success").html(response.data.message);
				setElementState("#success", true, true);
				//setElementState(".row_" + response.data.md5, false, false);
				window.location.reload();
				$(".row_" + response.data.md5).remove();
				if($("#resultTable tbody tr").length == 1){
					$("#resultTable .norows").show();
				}
			}else{
				$(".loader").hide();
				$(".button").show();
				$("#warning").html(response.message);
				setElementState("#warning", true, true);
			}
		}, "json");	
}

function doRollback(file, md5){
	setElementState("#warning", false, true);
	setElementState("#success", false, true);
	setElementState("#" + md5 + "_rollback_link", false, false);
	setElementState("#" + md5 + "_rollback_load", true, false);
	$.post("lib/Ajax.php", { 'function': "rollback", file: file },
		function(response){
		   	if(response.code == "200"){
				setElementState("#" + response.data.md5 + "_rollback_load", false, false);
				setElementState("#" + response.data.md5 + "_rollback_link", true, false);
				$("#success").html(response.data.message);
				setElementState("#success", true, true);
			}else{
				$(".loader").hide();
				$(".button").show();
				$("#warning").html(response.message);
				setElementState("#warning", true, true);
			}
		}, "json");	
}

function doDatabaseBackup(database){
	setElementState("warning", false, true);
	setElementState("success", false, true);
	setElementState("#" + database + "_runButton", false, false);
	setElementState("#" + database + "_loadImage", true, false);
	$.post("lib/Ajax.php", { 'function': "databasebackup", "database": database },
	   function(response){
		   	if(response.code == "200"){
				setElementState("#" + response.data.database + "_runButton", true, false);
				setElementState("#" + response.data.database + "_loadImage", false, false);
				$("#success2").html(response.data.message);
				setElementState("#success2", true, true);
				$("#resultTable tbody .norows").hide();
				$("#resultTable tbody").append('<tr class="row_' + response.data.file + '"><td><img src="images/database.png" width="16" height="16" alt="database" /></td><td>' + response.data.name + '</td><td>' + response.data.date + '</td><td>' + response.data.time + '</td><td>' + response.data.size + '</td><td align="right"><a href="download.php?file=' + response.data.file + '" class="button">Download</a></td><td align="right"><a href="#" onclick="doDelete(\'' + response.data.file + '\')" class="button">Delete</a></td><td align="right"><a href="#" id="' + response.data.md5 + '_rollback_link" onclick="doDatabaseRollback(\'' + response.data.file + '\',\'' + response.data.md5 + '\');return false;" class="button">Rollback</a><img id="' + response.data.md5 + '_rollback_load" class="loader" width="16" height="16" style="display: none;" alt="loader" src="images/ajax.gif"/></td></tr>');
				window.location.reload();
			}else{
				$("#warning2").html(response.message);
				setElementState("#warning2", true, true);
			}
	   }, "json");	
}

function doDatabaseRollback(file, md5){
	setElementState("#warning", false, true);
	setElementState("#success", false, true);
	setElementState("#" + md5 + "_rollback_link", false, false);
	setElementState("#" + md5 + "_rollback_load", true, false);
	$.post("lib/Ajax.php", { 'function': "databaserollback", file: file },
		function(response){
		   	if(response.code == "200"){
				setElementState("#" + response.data.md5 + "_rollback_load", false, false);
				setElementState("#" + response.data.md5 + "_rollback_link", true, false);
				$("#success").html(response.data.message);
				setElementState("#success", true, true);
			}else{
				$(".loader").hide();
				$(".button").show();
				$("#warning").html(response.message);
				setElementState("#warning", true, true);
			}
		}, "json");	
}

function setElementState(id, visible, fade){
	if(!fade){
		if(visible){
			$(id).show();
		}else{
			$(id).hide();
		}
	}else{
		if(visible){
			$(id).fadeIn("500");	
		}else{
			$(id).fadeOut("500");	
		}
	}
}

	function showPassword(element){
		if($(element).hasClass('done')){
			$(element).parent('td').find('input').prop('type','password');
			$(element).removeClass('done');
			$(element).html('Show Password');
		}
		else
		{
			$(element).closest('td').find('input').prop('type','text');
			$(element).addClass('done');
			$(element).html('Hide Password');
		}
	}
	
	$('#show_password').click(function(){
		showPassword($(this));
		return false;
	})