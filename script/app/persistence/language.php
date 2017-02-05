<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile('library/DAO/entity');
    
    final class language extends entity {
	
	public function getEnabledLans () {
	    
	    Helper::requireFile('library/DAO/recordset');
	    
	    $search = new recordset();
	    $enabledLans = array();
	    
	    if (!$search->command("GET ENTITIES language", array('#1'=>1), "enabled = #1")) return $enabledLans;
	    
            do {
		$lan = clone $this;
		$lan->load($search->current());
		$enabledLans[] = $lan;
            } while($search->next());
	    
	    return $enabledLans;
	    
	}
	
	public function checkCookie() {
	    
	    if (!isset($_COOKIE['lan'])) return false;
            
	    $lanCookie = $_COOKIE['lan'];
	    
	    return ($this->read_by('codigo',$lanCookie));
	    
	}
	
	public function setCookie() {
	    
	    $codigo = $this->codigo;
	    if (empty($codigo)) throw new exceptionBase ("Empty id language",500);
	    setcookie('lan',$codigo,time()+3600,'/');
	    
	}
	
	public function checkBrowser() {
	    
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return false;
		
	    $headerLanBrowser = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (empty($headerLanBrowser)) return false;
            $lanBrowser = $headerLanBrowser[0];
	    
	    $lan = null;
	    $exist = false;
	    $search = $this->getEnabledLans();
	    
	    if ($exist) {
		do {
		    if (strpos($lanBrowser,$search->current()->codigo) !== false) {
			if ($this->read($search->current()->id)) {
			    $exist = true;
			    break;
			}
		    }
		} while($search->next());
	    }
	    
	    return $exist;
	    
	}
	    
	public function getDefault () {
	    
	    if (!$this->read_by('codigo',Bayou::registry('defaultLan'))) throw new exceptionBase ("Not default language",500);
	    
	}
	
    }
    
?>