<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();

    class exceptionBase extends Exception {
	
	private $httpError = null;
	
	public function __construct ($str, $httpError=null) {
	    
	    parent::__construct($str);
	    
	    if (!empty($httpError)) $this->httpError = $httpError;
	    
	}
	
	public function getHttpError() {
	    
	    return $this->httpError;
	    
	}
	
    }
    
?>