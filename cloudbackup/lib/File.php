<?php

/**
 * File and directory handling script
 * 
 * @version 1.0
 */
class Explorer{
	
	/********************* PROPERTY ********************/
	
	/**
	 * The current cursor location in the file system
	 * 
	 * @var string
	 */
	private $_path = "";
	
	/********************* CONSTRUCT *********************/
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct(){}
	
	
	/********************* PRIVATE *********************/
	
		/**
	 * Get extension from a string
	 *
	 * @access private
	 * @param string $file
	 * @return string
	 */
	private function getExtension($file){
		$parts = explode(".", $file);
		return end($parts);
	}
	
	/**
	 * Sort an arry based on the strings length
	 *
	 * @access private
	 * @param string $val_1
	 * @param string $val_2
	 * @return int
	 */
	private function lengthSort($val_1, $val_2){

		// initialize the return value to zero 
		$retVal = 0;
		
		// compare lengths 
		$firstVal = strlen($val_1); 
		$secondVal = strlen($val_2);
		
		if($firstVal > $secondVal) 
		{ 
			$retVal = 1; 
		} 
		else if($firstVal < $secondVal) 
		{ 
			$retVal = -1; 
		} 
		return $retVal; 

	}
	
	/********************* PUBLIC **********************/
	
	/**
	 * Set the explorer path
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public function SetPath($path=""){
		if($path != ""){ 
			$this->_path = str_replace("\\", "/", $path);
			$this->_path = (substr($this->_path, -1) == "/") ? substr($this->_path, 0, -1) : $this->_path;
		}
	}

	/**
	 * Read file content
	 * 
	 * @access public
	 * @param string $filename
	 * @return string
	 */
	public function Read(){
		$handle = fopen($this->_path, "r");
		$contents = fread($handle, filesize($this->_path));
		fclose($handle);
		return $contents;
	}
	
	/**
	 * Write content to file
	 * 
	 * @access public
	 * @param string $filename
	 * @param string $data
	 * @return bool
	 */
	public function Write($data){
		if($handle = fopen($this->_path,"w")){
			fwrite($handle, $data); 
			fclose($handle); 
			return true;
		}
		return false;
	}
	
	/**
	 * Create file
	 * 
	 * @access public
	 * @param bool $is_dir
	 * @param bool $create_if_exists If true existing file is overwrited
	 * @return bool
	 */
	public function Create($is_dir=false, $create_if_exists=false){
		if(file_exists($this->_path) && !$create_if_exists) return false;
		if(!$is_dir){
			$parts = explode("/", $this->_path);
			$path = "";
			foreach ($parts as $part){
				if($part == end($parts)) break;
				$path .= $part . "/";
				@mkdir($path, "0700");
			}
			if($handle = fopen($this->_path, 'w')){
				fclose($handle);
			}
		}else{
			$parts = explode("/", $this->_path);
			$path = "";
			foreach ($parts as $part){
				$path .= $part . "/";
				@mkdir($path, "0700");
			}
		}
		return file_exists($this->_path);
	}
	
	/**
	 * Delete a file or directory
	 * 
	 * @access public
	 * @return bool
	 */
	public function Delete(){
		if(is_dir($this->_path) && $this->_path != ""){
			$result = $this->Listing();
			
			// Bring maps to back
			// This is need otherwise some maps
			// can't be deleted
			$sort_result = array();
			foreach($result as $item){
				if($item['type'] == "file"){
					array_unshift($sort_result, $item);
				}else{
					$sort_result[] = $item;
				}
			}

			// Start deleting
			while(file_exists($this->_path)){
				if(is_array($sort_result)){
					foreach($sort_result as $item){
						if($item['type'] == "file"){
							@unlink($item['fullpath']);
						}else{
							@rmdir($item['fullpath']);
						}
					}
				}
				@rmdir($this->_path);
			}
			return !file_exists($this->_path);
		}else{
			@unlink($this->_path);
			return !file_exists($this->_path);
		}
	}
	
	/**
	 * Copy directory's or files
	 *
	 * @access public
	 * @param string $destination
	 * @return bool
	 */
	public function Copy($destination){
		if($destination == "") throw new Exception("Destination is not specified.");
			
		$destination = str_replace("\\", "/", $destination);
		$destination = (substr($destination, -1) == "/") ? substr($destination, 0, -1) : $destination;
		if(is_dir($this->_path)){
			
			// Create paths recursively
			$result = $this->Listing();
			$paths = array();
			$files = array();
			foreach ($result as $item){
				if($item["type"] == "dir"){
					$paths[] = str_replace($this->_path, "", $item['fullpath']);
				}else{
					$file = str_replace($this->_path, "", $item['fullpath']);
					$files[] = (substr($file, 0, 1) == "/") ? $file : "/" . $file;
				}
			}
			uasort($paths, array($this, "lengthSort"));
			
			// Create directory structure
			foreach ($paths as $path){
				$path = (substr($path, 0, 1) == "/") ? $path : "/" . $path;
				$new_directory = $destination . $path;
				@mkdir($destination);
				if(!file_exists($new_directory)){
					@mkdir($new_directory, "0700");
				}
			}
			
			// Copy files
			foreach ($files as $file){
				@copy($this->_path . $file, $destination . $file);
			}
			return file_exists($destination);
		}else{
			@copy($this->_path, $destination);
			return file_exists($destination);
		}

	}
	
	/**
	 * Move directory or file
	 * 
	 * @access public
	 * @param string $destination
	 * @access void
	 */
	public function Move($destination){
		$this->Copy($destination);
		$this->Delete();
		return (file_exists($destination) && !file_exists($this->_path));
	}
	
	/**
	 * List directory content
	 * 
	 * @access public
	 * @param array $exclude
	 * @param bool $recursive
	 * @return array
	 */
	public function Listing($exclude_extension=array(), $exclude_file=array(), $exclude_dir=array(), $recursive=true, &$list=array(), $dir=""){

		// Lowercase exclude arrays
		$exclude_extension = array_map("strtolower", $exclude_extension);
		$exclude_file = array_map("strtolower", $exclude_file);
		$exclude_dir = array_map("strtolower", $exclude_dir);
		
		$dir = ($dir == "") ? $this->_path : $dir;
		if(substr($dir, -1) != "/") $dir .= "/";

		// Open the folder 
		$dir_handle = @opendir($dir) or die("Unable to open $dir"); 

		// Loop through the files 
		while ($file = readdir($dir_handle)) { 
			
			// Strip dir pointers and extension exclude
			$extension = $this->getExtension($file);
			if($file == "." || $file == ".." || in_array($extension, $exclude_extension)) continue; 
			
			if(is_dir($dir . $file)){
				if(!in_array(strtolower($file), $exclude_dir)){
					$info				= "";
					$info["type"]		= "dir";
					$info["name"]		= $file;
					$info["path"]		= $dir;
					$info["fullpath"]	= $dir . $file;
					$list[] = $info;
				}
			}else{
				if(!in_array(strtolower($file), $exclude_file)){
					$info				= "";
					$info["extension"] = $extension;
					$info["type"]		= "file";
					$info["path"]		= $dir;
					$info["filename"]	= $file;
					$info["fullpath"]	= $dir . $file;
					$info["size"]		= filesize($dir . $file);
					$list[] = $info;
				}
			}
			
			if($recursive && is_dir($dir . $file) && !in_array(strtolower($file), $exclude_dir)){
				$this->Listing($exclude_extension, $exclude_file, $exclude_dir, $recursive, $list, $dir . $file);
			}
			
		} 
		
		// Close 
		closedir($dir_handle); 
		
		return $list;
		
	}
	
	/**
	 * Get extension of a file
	 *
	 * @access public
	 * @return string
	 */
	public function Extension(){
		if(!is_dir($this->_path)){
			return $this->getExtension($this->_path);
		}
		return false;
	}
	
}