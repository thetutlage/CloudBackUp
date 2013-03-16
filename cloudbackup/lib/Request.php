<?php 

// Include encryption functionality
include("Encryption.php");

/**
 * Request object
 */
class Request extends Base_Encryption {

	/**
	 * The current domain
	 *
	 * @var string
	 */
	private $_domain;

	/**
	 * The current time
	 *
	 * @var string
	 */
	private $_time;
	
	/**
	 * Action to execute on the server
	 *
	 * @var string
	 */
	private $_action;
	
	/**
	 * A list of settings to use
	 * in the command to execute
	 *
	 * @var array
	 */
	private $_settings = array();
	
	/**
	 * Cecksum of this request
	 *
	 * @var string
	 */
	private $_crc;
	
	/**
	 * PSK of the server and client
	 * This will be reset before the request
	 * is send to the server
	 *
	 * @var string
	 */
	private $_psk;
	
	/**
	 * Message that is show when this request is executed successful
	 * 
	 * @var string
	 */
	private $_on_success;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $psk
	 * @return void
	 */
	public function __construct($psk=""){
		if(!empty($psk)){
			
			// Add current domain to the request
			$url =  "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']; 
			$last_slash_pos = (strrpos($url, "/") + 1);
			$this->_domain = substr($url, 0, $last_slash_pos);

			$this->_time = time();
			$this->_crc = md5($psk . $this->_time);
			$this->_psk = $psk;
		}
		$this->_psk = $psk;
	}
	
	/**
	 * Create a string from this object
	 *
	 * @access public
	 * @return string
	 */
	public function __toString(){
		
		// Delete PSK
		$psk = $this->_psk;
		$this->_psk = "";
		
		return $this->Encrypt(serialize($this), $psk);
	}
	
	/**
	 * Add an action to this request
	 *
	 * @access public
	 * @param string $name
	 * @return void
	 */
	public function AddAction($name){
		$this->_action = $name;
	}
	
	/**
	 * Add settings to this request
	 *
	 * @access public
	 * @param array $array
	 * @return void
	 */
	public function AddSettings($array){

		// Read data if it is a file
		if(isset($array['file']) && file_exists($array['file'])){
			$array['filename'] = end(explode("/", $array['file']));
			$array['file'] = base64_encode(file_get_contents($array['file']));
		}
		
		$this->_settings = $array;
	}
	
	/**
	 * Set on success message
	 * 
	 * @access public
	 * @param string $message
	 * @return void
	 */
	public function SetOnSuccess($message){
		$this->_on_success = $message;
	}
	
	/**
	 * Get the CRC from this request
	 *
	 * @access public
	 * @return string
	 */
	public function GetCrc(){
		return $this->_crc;
	}
	
	/**
	 * Get the time from this request
	 *
	 * @access public
	 * @return string
	 */
	public function GetTime(){
		return $this->_time;
	}
	
	/**
	 * Get the action from this request
	 *
	 * @access public
	 * @return string
	 */
	public function GetAction(){
		return $this->_action;
	}
	
	/**
	 * Get the settings from this request
	 *
	 * @access public
	 * @return array
	 */
	public function GetSettings(){
		return $this->_settings;	
	}
	
	/**
	 * Get on success message
	 *
	 * @access public
	 * @return string
	 */ 
	public function GetOnSuccess(){
		return $this->_on_success;	
	}
	
	/**
	 * Get domain 
	 * 
	 * @access public
	 * @return string
	 */
	public function GetDomain(){
		return $this->_domain;
	}
	
}