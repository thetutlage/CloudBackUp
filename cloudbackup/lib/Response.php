<?php 

define("STATE_ERROR", "500");
define("STATE_SUCCES", "200");
define("STATE_BUSY", "100");
define("STATE_CALLBACK", "101");

/**
 * Response object
 */
class Response{
	
	/**
	 * Raw response XML data
	 *
	 * @var string
	 */
	private $_raw;
	
	/**
	 * Response XML wrapper
	 *
	 * @var string
	 */
	private $_baseformat = "<response time=\"%s\" domain=\"%s\">\n%s</response>";
	
	/**
	 * Add the state of this response
	 *
	 * @access public
	 * @param define $state
	 * @return void
	 */
	public function AddState($state){
		$this->_raw .= sprintf("\t<state>%s</state>\n", $state);
	}
	
	/**
	 * Add a message to this response
	 *
	 * @access public
	 * @param string $string
	 * @return void
	 */
	public function AddMessage($string){
		$this->_raw .= sprintf("\t<message>%s</message>\n", $string);
	}
	
	/**
	 * Add data to this response
	 *
	 * @access public
	 * @param string $data
	 * @return void
	 */
	public function AddData($data){
		if(is_array($data)){
			$this->_raw .= "\t<data type=\"array\">";
			foreach($data as $key => $value){
				 if(is_numeric($key)){
				 	$this->_raw .= '<line index="' . $key . '">' . $value . '</line>';
				 }else{
				 	$this->_raw .= '<' . $key . '>' . $value . '</' . $key . '>';
				 }
			}
			$this->_raw .= "</data>\n";
			
		}else{
			$this->_raw .= sprintf("\t<data type=\"string\">%s</data>\n", $data);
		}
	}
	
	/**
	 * Output the XML data
	 *
	 * @access public
	 * @return void
	 */
	public function Parse(){
		header ("content-type: text/xml");
		echo sprintf($this->_baseformat, time(), $_SERVER['HTTP_HOST'], $this->_raw);
		exit();
	}
	
}