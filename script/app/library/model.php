<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    abstract class model {
	
	private $model = null;
	
        final public function __construct() {
	    
	    $model = get_class($this);
	    
	    $pattern = '/^[a-zA-Z0-9_]+Model\Z/is';
        if (!preg_match($pattern,$model)) throw new exceptionBase("Unexpected model class name '$model'",500);
	    $this->model = $model;
	    
	}
	
    }
    
?>