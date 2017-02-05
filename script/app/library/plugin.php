<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    abstract class plugin {
	
	private $name = null;
	private $html = null;
	
        protected function __construct($html) {
	    
	    $plugin = get_class($this);
	    
	    $pattern = '/^([a-zA-Z0-9_])+\Z/is';
            if (!preg_match($pattern,$plugin)) throw new exceptionBase("Unexpected plugin name '$plugin'",500);
	    $this->name = $plugin;
	    
	    $pluginUrl = $this->getPluginUrl();
	    $html = str_replace('___base_url_plugin___',$pluginUrl,$html);
	    
	    $this->html = $html;
	    
	}
	
	public function __toString() {
	    
	    return $this->html;
	    
	}
	
	private function getPluginUrl() {
	    
	    return Bayou::registry('base_url') . Bayou::registry('base_dir') . Bayou::registry('plugin_dir') . $this->name;
	    
	}
	
    }
    
?>