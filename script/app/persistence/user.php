<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile('library/DAO/entity');
    
    final class user extends entity {
	
	public function checkPw ($pw) {
	    
	    return (md5($pw) == $this->pw);
	    
	}
	
    }
    
?>
