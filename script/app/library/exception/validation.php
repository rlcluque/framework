<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();

    class exceptionValidation extends Exception {
	
	public function dieProcess() {
	    
	    $message = new Message(parent::getMessage());
	    $message->add('type','validationError');
	    header("HTTP/1.0 200 OK");
	    die($message->sendMessage());
	    
	}
	
    }
    
?>