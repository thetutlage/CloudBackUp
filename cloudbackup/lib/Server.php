<?php 

include("pclzip.lib.php");

/**
 * Handling WebImage requests
 * 
 * @todo Public zip on a server
 */
class Server{
	
	/**
	 * Cache the date
	 *
	 * @var string
	 */
	private $_date;
	
	/**
	 * Temp directory path
	 *
	 * @var string
	 */
	private $_temppath;
	
	/**
	 * The path to the directory where the backups are
	 * placed
	 *
	 * @var string
	 */
	private $_backuppath;
	
	/**
	 * Path
	 * 
	 * @var string
	 */
	private $_path;
	
	/**
	 * Array of config data
	 *
	 * @var array
	 */
	private $_config;
	
	/**
	 * The directory of the live application
	 * or website
	 *
	 * @var string
	 */
	private $_webpath;
	
	/**
	 * Exclude dirs from backup
	 *
	 * @var array
	 */
	private $_excludedirs;
	
	/**
	 * Exclude files from backup
	 *
	 * @var array
	 */
	private $_excluldefiles;
	
	/**
	 * Wild exclude files from size/backup
	 * 
	 * @var array
	 */
	private $_exludewildfiles;
	
	/**
	 * The current request
	 * 
	 * @var Request
	 */
	private $_Request;
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @param array $config
	 * @return void
	 */
	public function __construct($config){
		
		// Set config to field
		$this->_config = $config;
		
		// Exclude dirs
		if(isset($this->_config['exclude.dir'])){
			$excludes = explode(",", $this->_config['exclude.dir']);
			foreach ($excludes as $exclude){
				$this->_excludedirs[] = trim("/" . $exclude);
			}
		}
		$this->_excludedirs[] = "/Abs";
		
		// Exclude files
		if(isset($this->_config['exclude.file'])){
			$excludes = explode(",", $this->_config['exclude.file']);
			foreach ($excludes as $exclude){
				if(strstr($exclude, "*")){
					$cexclude = str_replace("*", "", trim($exclude));
					$this->_excludewildfiles[] = $cexclude;
				}else{
					$this->_excludefiles[] = trim($exclude);
				}
			}
		}
		
		// Base path
		$path = str_replace("\\", "/", dirname(__FILE__));
		$path = $this->addTrailSlash($path);
		$parts = explode("/", $path);
		$last = (count($parts)-2);
		unset($parts[$last]);
		$path = implode("/", $parts);
		
		// Set webpath
		$parts = explode("/", $path);
		$last = (count($parts)-2);
		unset($parts[$last]);
		$this->_webpath = implode("/", $parts);
		
		// Construct backup path
		$backuppath = substr($config['backuppath'], 0, 1) == "/" ? substr($config['backuppath'], 1) : $config['backuppath'];
		$this->_backuppath = $path . $backuppath;
		
		if(!$this->isValidDir($this->_backuppath)){
			trigger_error("Problems when creating the backupdir");
		}
		
		// Set temp path
		$this->_temppath = $path . "temp/";
		
		// Set path
		$this->_path = $path;
		
		// Handle requests
		//$this->handleRequests();
		
	}	

	/**
	 * Backup file to a directory
	 *
	 * @access public
	 * @return string|bool
	 */
	public function FileBackup(){
		// Get the backupdir
		$image = $this->getImageName();
		$backupdir = $this->_backuppath . $image;
		if($this->isValidDir($backupdir)){
			$this->copyDir($this->_webpath, $backupdir);
			return $image;
		}
		return FALSE;
	}
	
	/**
	 * Return an array of backups
	 *
	 * @access public
	 * @return array
	 */
	public function ListBackups($zip=true, $sql=true){
		
		$list = array();
		$dirHandle = @opendir($this->_backuppath) or die("Unable to open $this->_backuppath");
		while ($file = readdir($dirHandle)) 
		{
			if($file != ".." && $file != "." && $file != ".htaccess")
			{	
		  		$list[] = $file;
			}
		}

		closedir($dirHandle);

		return $list;
	}
	
	/**
	 * Create a name/id for the next image
	 * 
	 * @access private
	 * @return string
	 */
	private function getImageName(){
		
		// Get current date
		if(!isset($this->_data)){
			$this->_date = date("Ymd") . "_" . date("Hi");
		}
		
		return "image_" . $this->_date;
		
	}
	
	/**
	 * Check if the dir exists and
	 * if not than create it
	 *
	 * @access private
	 * @param string $dir
	 * @return bool
	 */
	private function isValidDir($dir){
		if(!is_dir($dir)){
			mkdir($dir);
		}
		return TRUE;
	}
	
	/**
	 * Copy a directory from source to destination
	 *
	 * @access private
	 * @param string $source
	 * @param string $dest
	 * @param array $options
	 * @return bool
	 */
	private function copyDir($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755)) 
    { 
		
    	// If excluded dir return
    	$dirarray = explode("/", $dest);
    	$lastdir = "/" . end($dirarray);
    	if(in_array($lastdir, $this->_excludedirs)){
    		return;
    	}
    	
        $result=false; 
        
        if (is_file($source)){ 
            if ($dest[strlen($dest)-1]=='/') { 
                if (!file_exists($dest)) { 
                    cmfcDirectory::makeAll($dest,$options['folderPermission'],true); 
                } 
                $__dest=$dest."/".basename($source); 
            } else { 
                $__dest=$dest; 
            } 
            $result = @copy($source, $__dest); 
            if(file_exists($__dest)){
            	chmod($__dest,$options['filePermission']); 
            }
            
        } elseif(is_dir($source)) { 
            if ($dest[strlen($dest)-1]=='/') { 
                if ($source[strlen($source)-1]=='/') { 
                    //Copy only contents 
                } else { 
                    //Change parent itself and its contents 
                    $dest=$dest.basename($source); 
                    @mkdir($dest); 
                    if(file_exists($dest)){
                    	chmod($dest,$options['filePermission']); 
                    }
                } 
            } else { 
                if ($source[strlen($source)-1]=='/') { 
                    //Copy parent directory with new name and all its content 
					if(!file_exists($dest)){
                    	@mkdir($dest,$options['folderPermission']); 
					}
                    if(file_exists($dest)){
                    	chmod($dest,$options['filePermission']); 
                    }
                } else { 
                    //Copy parent directory with new name and all its content 
                    @mkdir($dest,$options['folderPermission']); 
                    if(file_exists($dest)){
                    	chmod($dest,$options['filePermission']); 
                    }
                } 
            } 

            $dirHandle= @opendir($source); 
            while($file=readdir($dirHandle)) 
            { 
                if($file!="." && $file!="..") 
                { 
                     if(!is_dir($source."/".$file)) { 
                        $__dest=$dest."/".$file; 
                    } else { 
                    	//if(in_array($file, $this->_excludedirs) return;
                        $__dest=$dest."/".$file; 
                    } 
                    //echo "$source/$file ||| $__dest<br />"; 
                    $result=$this->copyDir($source."/".$file, $__dest, $options); 
                } 
            } 
            closedir($dirHandle); 
            
        } else { 
            $result=false; 
        } 
        return $result; 
    } 
	
    /**
     * Handle requests
     * 
     * @access private
     * @return void
     */
	private function handleRequests(){

		// Reject GET method
		/*if($_SERVER['REQUEST_METHOD'] == "GET"){
			$Response = new Response();
			$Response->AddState(STATE_ERROR);
			$Response->AddMessage("The server doesn't allow GET method.");
			$Response->Parse();
		}*/
		
		$this->commandMysqlbackup(array("database" => "dmx_css"));

		if($Request = $this->isValidRequest($_POST)){
			$this->_Request = $Request;
			$commandname = "command" . ucfirst($Request->GetAction());
			if(method_exists($this, $commandname)){
				$this->$commandname($Request->GetSettings());
			}else{
				$Response = new Response();
				$Response->AddState(STATE_ERROR);
				$Response->AddMessage("Command " . $commandname . " is not found.");
				$Response->Parse();
			}
		}
	}
	
	/**
	 * Check if the CRC and PSK from the request is valid
	 *
	 * @access private
	 * @param array $post
	 * @return array
	 */
	private function isValidRequest($post){
		if(!empty($post['data'])){
			if($Request = @unserialize(Base_Encryption::Decrypt($post['data'], $this->_config['psk']))){
				if($Request->GetCrc() == md5($this->_config['psk'] . $Request->GetTime())){
					return $Request;
				}
			}
			
			$Response = new Response();
			$Response->AddState(STATE_ERROR);
			$Response->AddMessage("Wrong pre shared key or checksum.");
			$Response->Parse();
		}
		
		$Response = new Response();
		$Response->AddState(STATE_ERROR);
		$Response->AddMessage("Did you forget to send some data?");
		$Response->Parse();
	}
	
	/**
	 * Command
	 * Create a backup from the website/application files
	 *
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	public function CommandFilebackup($settings=""){

		if($name = $this->getImageName()){
			// Make a zip if autoarchive is on
			if($this->_config['autoarchive']){
				
				$File = new Explorer();
				$File->SetPath($this->_webpath);
				$exclude_dirs = explode(',',$this->_config['exclude.dir']);
				$exclude_dirs_clean = array();
				foreach($exclude_dirs as $dir){
					$exclude_dirs_clean[] = trim($dir);
				}
				$exclude_dirs_clean[] = "Abs";
				$listing = $File->Listing(array(), array(), $exclude_dirs_clean, false);
				$file_list = "";
				
				if (substr($this->_webpath, 1,1) == ':') {
					$remove = substr($this->_webpath, 2);
				}
				if(substr($this->_webpath, 0,1) == '/'){
					$remove = substr($this->_webpath, 1);
				}

				if(file_exists($this->_backuppath . $name . ".zip")){
					return "exists";
				}

				$zip = new PclZip($this->_backuppath . $name . ".zip");
				foreach($listing as $item){
					$v_list = $zip->add($item['fullpath'] , PCLZIP_OPT_REMOVE_PATH, $remove);
				}

				//$this->zipDir($this->_backuppath . $name, $this->_backuppath . $name . ".zip");
				//$this->deleteDirectory($this->_backuppath . $name);
			}
			
			// Check for max files
			$this->deleteMaxFiles();

			return $name;
		}else{
			return false;
		}

	}
	
	/**
	 * Command
	 * Return a list of the existing backups
	 *
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	private function commandListbackups($settings=""){
		if($list = $this->ListBackups()){
			$Response = new Response();
			$Response->AddState(STATE_SUCCES);
			$Response->AddData($list);
			$Response->Parse();
		}else{
			$Response = new Response();
			$Response->AddState(STATE_ERROR);
			$Response->AddMessage("Could not find any backups.");
			$Response->Parse();
		}
	}
	
	/**
	 * Command
	 * Get the name of the last backup
	 *
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	private function commandLastbackup($settings=""){
		if($list = $this->ListBackups()){
			$Response = new Response();
			$Response->AddState(STATE_SUCCES);
			
			$Response->AddData(array("file" => end($list)));
			$Response->Parse();
		}else{
			$Response = new Response();
			$Response->AddState(STATE_ERROR);
			$Response->AddMessage("Could not find any backups.");
			$Response->Parse();
		}
	}
	
	/**
	 * Command
	 * Rollback the file to a backup
	 *
	 * Settings
	 * backup = The name of the backup you want to rollback to
	 * 
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	public function CommandRollback($settings=""){
			
		// check if dir exists
		if(substr($settings['backup'],-3) == "zip"){
		
			 $zip = new PclZip($this->_backuppath . $settings['backup']);
			 if ($zip->extract(PCLZIP_OPT_PATH, $this->_webpath) == 0) {
				return false;
			 }else{
			 	return true;
			 }
		
		}else{
			return false;
		}
		
	}
	
	/**
	 * Command
	 * Upload a file/zip to the website
	 * 
	 * Settings
	 * filename = path to a file/zip you want to publish on the server
	 *
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	private function commandPublish($settings=""){
		
		//$this->startNoWait();

		// Create file
		file_put_contents($this->_webpath . $settings['filename'], base64_decode($settings['file']));
		
		if(class_exists("ZipArchive")){
			$Zip = new ZipArchive();
			if ($Zip->open($this->_webpath . $settings['filename']) === TRUE) {
			    $Zip->extractTo($this->_webpath);
			    $Zip->close();
			    unlink($this->_webpath . $settings['filename']);
			    
			    // Send callback to client to tell that the command is ready
				$this->doCallback("The file '" . $settings['filename'] . "' is published to the server.");
			    
			    $Response = new Response();
				$Response->AddState(STATE_SUCCES);
				$Response->AddMessage("Publish success");
				$Response->Parse();
			}else{
				$Response = new Response();
				$Response->AddState(STATE_ERROR);
				$Response->AddMessage("WebImage was uneable to open the zip archive.");
				$Response->Parse();
			}
		}else{
			$Response = new Response();
			$Response->AddState(STATE_ERROR);
			$Response->AddMessage("The ZipArchive class is not found on your server.");
			$Response->Parse();
		}
		
	}
	
	/**
	 * Command
	 * Download a backup as archive
	 *
	 * Settings
	 * backup = name of the backup you want to download
	 * 
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	private function commandDownload($settings=""){
		if(class_exists("ZipArchive")){
			if(!file_exists($this->_backuppath . $settings['backup'] . ".zip")){
				$this->zipDir($this->_backuppath . $settings['backup'], $this->_backuppath . $settings['backup'] . ".zip");
			}
			
			if(file_exists($this->_backuppath . $settings['backup'] . ".zip")){
				$Response = new Response();
				$Response->AddState(STATE_SUCCES);
				$Response->AddMessage("Zip backup created");
				$Response->AddData(base64_encode(file_get_contents($this->_backuppath . $settings['backup'] . ".zip")));
				
				// Delete the file from the server
				unlink($this->_backuppath . $settings['backup'] . ".zip");
				
				$Response->Parse();
			}else{
				$Response = new Response();
				$Response->AddState(STATE_ERROR);
				$Response->AddMessage("Problem while creating the backup archive.");
				$Response->Parse();
			}
		}else{
			$Response = new Response();
			$Response->AddState(STATE_ERROR);
			$Response->AddMessage("The ZipArchive class is not found on your server.");
			$Response->Parse();
		}

	}
	
	/**
	 * Command
	 * Get total size of the website
	 * 
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	private function commandTotalsize($settings=""){

				$Response = new Response();
				$Response->AddState(STATE_SUCCES);
				$Response->AddMessage("Total size counted");
				
				// Get dir size
				$bytes = $this->directorySize($this->_webpath);
				
				// Make formated size
				if($bytes / 1048576 > 1)
				{
					$format = round($bytes / 1048576, 1).' MB';
				}elseif($bytes / 1024 > 1)
				{
					$format = round($bytes / 1024, 1).' KB';
				}else{
					$format = round($bytes, 1).' bytes';
				}

				$Response->AddData(array("bytes" => $bytes, "format" => $format));

				
				$Response->Parse();

	}
	
	public function CommandMysqlRollback($settings=""){
		$file = $settings['file'];
		$filepath = $this->GetBackupPath() . $file;

		if(file_exists($filepath)){
			
			// Get database name
			$parts = explode("_", $file);
			$dbname = str_replace("-", "_", $parts[0]);

			if(isset($settings['dbname'])) $dbname = $settings['dbname'];
			
			
			// Connect to database
			$host = $this->_config['db.host'];
			$user = $this->_config['db.username'];
			$pass = $this->_config['db.password'];
			$name = $this->_config['db.name'];
			$port = $this->_config['db.port'];

			
			if($port == "") $port = 3306;
			$link = @mysql_connect($host.":".$port,$user,$pass);
			if(!$link){
				error("Could not connect to MySQL server. Check the database credentials in the config.");
			}
			if(function_exists("mysql_set_charset")){
				@mysql_set_charset($this->_config['db.charset'], $link);
			}
			if(!@mysql_select_db($dbname,$link)){
				error("There is no database found with name: " . $dbname . ".");
			}
			
				$content = file_get_contents($filepath);
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
				
			return true;
			
		}else{
			return false;
		}
	
	}
	
	/**
	 * Command
	 * Creat a mysql backup
	 * 
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	public function CommandMysqlbackup($settings=""){

		$host = $this->_config['db.host'];
		$user = $this->_config['db.username'];
		$pass = $this->_config['db.password'];
		$name = $settings['database'];
		$port = $this->_config['db.port'];
		$tables = "*";
		
		// Create link with database
		$link = mysql_connect($host.":".$port,$user,$pass);

		// If there is no link send settings error back
		if(!$link){
			return false;
		}

		if(!mysql_select_db($name,$link)){
			return false;
		}
		
		// Create array of the tables to backup
		if($tables == '*')
		{
			$tables = array();
			$result = mysql_query('SHOW TABLES');
			while($row = mysql_fetch_row($result))
			{
				$tables[] = $row[0];
			}
		}
		else
		{
			$tables = is_array($tables) ? $tables : explode(',',$tables);
		}
		
		// Add basic information
		$return = "-- Advanced Backup System SQL Dump\n";
		$return .= "--\n";
		$return .= '-- Host: ' . $host . ':' . $port . "\n";
		$return .= '-- Export created: ' . date("Y/m/d") . ' on ' . date("h:i") . "\n\n\n";
		
		// Loop through tables and make backup
		$constraints = "";
		foreach($tables as $table)
		{
			
			// Get some numbers
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);
			
			// Add table information
			$return .= "--\n";
			$return .= '-- Tabel structuur voor tabel `' . $table . '`' . "\n";
			$return .= "--\n";
			
			// Add a drop?
			if(!isset($settings['nodrop']) || !$settings['nodrop']){
				$return	.= 'DROP TABLE IF EXISTS '.$table.';';
				$return .= "\n\n";
			}
			
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$row2[1] = str_replace("CREATE TABLE ", "CREATE TABLE IF NOT EXISTS ", $row2[1]);
			
			$lines = explode("\n", $row2[1]);
			$create = "";
			$strip_last_comma = false;
			foreach($lines as $line){
				$clean_line = trim($line); 
				if(substr($clean_line,0, 10) == "CONSTRAINT"){
					$strip_last_comma = true;
					$constraint_line = substr(trim($clean_line), -1) == "," ? substr(trim($clean_line), 0, -1) . ";" : trim($clean_line) . ";";
					$constraints .= 'ALTER TABLE `' . $table . '` ADD ' . $constraint_line . "\n";
				}else{
					if($strip_last_comma){
						$clean = trim($create);
						if(substr($clean, -1) == ","){
							$create = substr($clean, 0, -1);
						}	
						$strip_last_comma = false;
					}
					$create .= $clean_line . "\n";	
				}	
			}
			
			$return .= $create.";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) 
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++) 
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = ereg_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}
		
		// Backup path
		$file = str_replace("_", "-", $name) . "_" . date("Ymd") . "_" . date("Hi") . ".sql";
		
		//save file
		$handle = fopen($this->_backuppath . $file,'w+');
		fwrite($handle,$return . "\n\n-- Constraints --\n" . $constraints);
		fclose($handle);
		
		// Check for max files
		$this->deleteMaxFiles();
		
		return $file;
		
	}
	
	/**
	 * Command
	 * Get names of databases
	 * 
	 * @access private
	 * @param array $settings
	 * @return void
	 */
	public function CommandMysqldatabases($settings=""){
		
		$host = $this->_config['db.host'];
		$user = $this->_config['db.username'];
		$pass = $this->_config['db.password'];
		$name = $this->_config['db.name'];
		$port = $this->_config['db.port'];
		
		// Create link with database
		$link = @mysql_connect($host.":".$port,$user,$pass);

		// If there is no link send settings error back
		if(!$link){
			return false;
		}

		if(!@mysql_select_db($name,$link)){
			return false;
		}
		
		$tables = array();
		$result = mysql_query('SHOW DATABASES');
		while($row = mysql_fetch_row($result))
		{
			$databases[] = $row[0];
		}
		
		return $databases;
		
	}
	
	/**
	 * Add a trailing slash to dir path
	 *
	 * @access private
	 * @param string $dir
	 * @return string
	 */
	private function addTrailSlash($dir){
		if(substr($dir,-1) != "/"){
			return $dir . "/";
		}
		return $dir;
	}
	
	/**
	 * Create an archive from a directory
	 *
	 * @access private
	 * @param string $sourcedirectory
	 * @param string $destinationfile
	 * @return void
	 */
	private function zipDir($sourcedirectory, $destinationfile){
		$handle = @opendir($sourcedirectory);
		$zip = new ZipArchive();
		$opened = $zip->open( $destinationfile, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
		if( $opened !== true ){
			die("cannot open {$destinationfile} for writing.");
		}
		$this->addFilesToZip($sourcedirectory, $handle, $zip);
		$zip->close();
	}
	
	/**
	 * Add files to an open zip archive
	 *
	 * @param string $directory
	 * @param Dir $dir_handle
	 * @param ZipArchive $archive
	 * @return void
	 */
	private function addFilesToZip($directory, $dir_handle, $archive){

	    //running the while loop
	    while (false !== ($file = readdir($dir_handle))) {
	        $dir =$directory.'/'.$file;
	        if(is_dir($dir) && $file != '.' && $file !='..' )
	        {
	            $handle = @opendir($dir) or die("undable to open file $file");
				$parts = explode("/", $dir);
				if(!in_array(end($parts), $this->_excludedirs)){
	            	$this->addFilesToZip($dir, $handle, $archive);
				}
	        }elseif($file != '.' && $file !='..')
	        {
	            $archive->addFromString( str_replace($this->_backuppath, "", $dir), file_get_contents($dir));
	        }
	    }
	    
	
	    //closing the directory
	    closedir($dir_handle);
		
	}
	
	private function startNoWait(){
		header('WebImage: longaction');
		header('Connection: close'); 
		set_time_limit(0); 
		//@ob_end_clean();
		ignore_user_abort(TRUE); 
		ob_start(); 
	}
	
	private function endNoWait(){
		$size = ob_get_length(); 
		header("Content-Length: $size"); 
		//ob_end_flush(); 
		//flush(); 
		
		// Do callback
		/*$Response = new Response();
		$Response->AddState(STATE_CALLBACK);
		$Response->AddMessage("Creating backup is ready.");
		$Response->Parse();*/
		
		session_write_close(); 
	}
	
	/**
	 * Function om een map recursief te verwijderen
	 * 
	 * @access 		private
	 * @param 		string $directory
	 * @return 		bool
	 */
	private function deleteDirectory($directory){
		
		// Create dirhandle
		if(is_dir($directory))
		{
			$dir_handle=opendir($directory); 
		}
		
		// Loop through dir
		while($file=@readdir($dir_handle)) 
		{ 
			if($file!="." && $file!="..") 
		    { 
		    	if(!is_dir($directory."/".$file))
		     	{
		     		// Delete file
		     		unlink ($directory."/".$file); 
		     	}
		     	else 
		     	{
		     		// Recursive delete
		     		$this->deleteDirectory($directory."/".$file);
		     	}
		    } 
		} 
		
		// Close dirhandle
		closedir($dir_handle); 
		
		// Delete directory
		rmdir($directory); 
		
		
		// Retrurn result
		return TRUE; 
	}
	
	private function doCallback($message){
		file_get_contents($this->_Request->GetDomain() . "Cb.php?time=" . $this->_Request->GetTime() . "&message=" . urlencode($message));
	}
	
	/**
	 * Count directory size 
	 * 
	 * @param string $directory
	 * @param bool $format
	 */
	private function directorySize($directory, $format=FALSE)
	{
		$size = 0;

		// if the path has a slash at the end we remove it here
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}
	
		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
		{
			// ... we return -1 and exit the function
			return -1;
		}
		// we open the directory
		if($handle = opendir($directory))
		{
			// and scan through the items inside
			while(($file = readdir($handle)) !== false)
			{
				
				// we build the new path
				$path = $directory.'/'.$file;
	
				// if the filepointer is not the current directory
				// or the parent directory
				if($file != '.' && $file != '..' && !in_array($file, $this->_excludefiles) && !$this->fileWildHit($file))
				{

					// if the new path is a file
					if(is_file($path))
					{
						// we add the filesize to the total size
						$size += filesize($path);
	
					// if the new path is a directory
					}elseif(is_dir($path) && !in_array("/".$file, $this->_excludedirs))
					{

						// we call this function with the new path
						$handlesize = $this->directorySize($path);
	
						// if the function returns more than zero
						if($handlesize >= 0)
						{
							// we add the result to the total size
							$size += $handlesize;
	
						// else we return -1 and exit the function
						}else{
							return -1;
						}
					}
				}
			}

			// close the directory
			closedir($handle);
		}
		
		// if the format is set to human readable
		if($format == TRUE)
		{
			// if the total size is bigger than 1 MB
			if($size / 1048576 > 1)
			{
				return round($size / 1048576, 1).' MB';
	
			// if the total size is bigger than 1 KB
			}elseif($size / 1024 > 1)
			{
				return round($size / 1024, 1).' KB';
	
			// else return the filesize in bytes
			}else{
				return round($size, 1).' bytes';
			}
		}else{
			// return the total filesize in bytes
			return $size;
		}
	}
	
	private function fileWildHit($file){
		foreach($this->_excludewildfiles as $find){
			if(strstr($file, $find)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Delete old files if number of backups files is bigger
	 * then maxbackups setting in config.php
	 *
	 * @return void
	 */
	private function deleteMaxFiles(){
		$File = new Explorer();
		$File->SetPath($this->_backuppath);
		$listing = $File->Listing(null, array(".htaccess"));
		if(count($listing) > $this->_config['maxbackups']){
			// Grab all files from the desired folder
			$files = glob( $this->_backuppath . "*.*" );
			
			// Sort files by modified time, latest to earliest
			// Use SORT_ASC in place of SORT_DESC for earliest to latest
			array_multisort(
				array_map( 'filemtime', $files ),
				SORT_NUMERIC,
				SORT_ASC,
				$files
			);
			
			// Remove wrong filetypes
			$clean_files = array();
			for($i=0 ; $i < count($files) ; $i++){
				if(substr($files[$i],0,1) != "."){
					$clean_files[] = $files[$i];
				}
			}

			$delete_n = count($clean_files) - $this->_config['maxbackups'];
			foreach($clean_files as $file){
				if($delete_n <= 0) break;
				unlink($file);
				$delete_n--;
			}

		}
	}
	
	public function GetWebPath(){
		return $this->_webpath;
	}
	
	public function GetPath(){
		return $this->_path;
	}
	
	public function GetBackupPath(){
		return $this->_backuppath;
	}
	
	public function GetTempPath(){
		return $this->_temppath;
	}
	
	public function GetDatePart($name){
		$parts = explode("_", $name);
		$name = $parts[1] . "_" . $parts[2];
		$name = str_replace(array("image_", ".zip"), "", $name);
		return substr($name, 0, 4) . "/" . substr($name, 4, 2) . "/" . substr($name, 6, 2);
	}
	
	public function GetTimePart($name){
		$parts = explode("_", $name);
		$name = $parts[1] . "_" . $parts[2];
		$name = str_replace(array("image_", ".zip"), "", $name);
		return substr($name, 9, 2) . ":" . substr($name, 11, 2);
	}
	
	public function GetByteFormat($bytes, $precision = 2){
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
	  
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
	  
		$bytes /= pow(1024, $pow);
	  
		return round($bytes, $precision) . ' ' . $units[$pow];
	}
	
	public function GetDatabaseName($name){
		$parts = explode("_", $name);
		return str_replace("-", "_", $parts[0]);
	}
	
}
