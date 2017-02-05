<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    abstract class mediaBase {
	
	private $name = null;
	private $html = null;
	
        protected function __construct($html) {
	    
	    $media = get_class($this);
	    
	    $pattern = '/^[a-zA-Z0-9_]\Z/is';
            if (!preg_match($pattern,$media)) throw new exceptionBase("Unexpected media class name '$media'",500);
	    $this->name = $media;
	    
	    $html = str_replace('___base_url_media___',$this->getMediaUrl,$html);
	    
	    $this->html = $html;
	    
	}
	
	public function __toString() {
	    
	    echo $this->html;
	    
	}
	
	private function getMediaUrl() {
	    
	    return Bayou::registry('base_url') . Bayou::registry('base_dir') . Bayou::registry('media_dir') . $this->name;
	    
	}
	
    }
    
?>