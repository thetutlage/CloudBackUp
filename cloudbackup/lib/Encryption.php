<?php

if(!class_exists("Base_Encryption")){
class Base_Encryption {
	static public function Encrypt($str, $key){
	  	for($i=0; $i<strlen($str); $i++) {
	    	$char = substr($str, $i, 1);
	     	$keychar = substr($key, ($i % strlen($key))-1, 1);
	     	$char = chr(ord($char)+ord($keychar));
	     	$result.=$char;
	  	}
		return base64_encode($result);
	}
	
	
	static public function Decrypt($str, $key){
	  	$str = base64_decode($str);
	  	$result = '';
	  	for($i=0; $i<strlen($str); $i++) {
	    	$char = substr($str, $i, 1);
	    	$keychar = substr($key, ($i % strlen($key))-1, 1);
	    	$char = chr(ord($char)-ord($keychar));
	    	$result.=$char;
	  	}
		return $result;
	}
}
}