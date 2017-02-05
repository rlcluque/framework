<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    final class Validator {
	
	/*
	public function __call($name,$arguments) {
	    
	    $validators = get_class_methods($this);
	    
	    if (in_array($name,$validators)) return call_user_func_array(array($this,$name),$arguments);
	    else throw new exceptionBase("Unknown validator '$name'",500);
	    
	}
	*/
	
	public function is_text ($var) {
	    
	    return $this->word($var,0);
	    
	}
	
	public function is_word ($var) {
	    
	    return $this->word($var,2);
	    
	}
	
	public function is_natural($var) {
	    
	    if (!is_int($var)) return false;
	    if ($var < 1) return false;
	    
	    return true;
	    
	}
	
	public function is_integer ($var) {
	    
		return (is_int($var));
	    
	}
	
	public function is_bit ($var) {
	    
		return ($var === 1 || $var === 0);
	    
	}
	
	public function is_decimal ($var) {
	    
	    return (is_float($var));
	    
	}
	
	public function is_mail($var,$checkdns=true) {
	    
	    $atIndex = strrpos($email, "@");
	    if (is_bool($atIndex) && !$atIndex) return false;
	    
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		
		if ($localLen < 1 || $localLen > 64) return false;
		if ($domainLen < 1 || $domainLen > 255) return false;
		if ($local[0] == '.' || $local[$localLen-1] == '.') return false;
		if (preg_match('/\\.\\./', $local)) return false;
		if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) return false;
		if (preg_match('/\\.\\./', $domain)) return false;
		if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
			if (!preg_match('/^"(\\\\"|[^"])+"$/',str_replace("\\\\","",$local))) return false;
		}
		
		if ($checkdns) if (checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")) return false;
	    
	    return true;
	    
	}
	
	public function is_url($url) {
	    
	    if (!is_string($url)) return false;
	    
	    return (bool)preg_match('/^[A-Za-z0-9-_ñ]+\Z/', $url);
	    
	}
	
	private function word($string,$minLength=3,$maxLength=10000) {
	    
	    if (!is_string($string)) return false;
	    
	    $length = strlen($string);
	    return (($length >= $minLength) && ($length <= $maxLength));
	    
	}
	
    }
    
?>