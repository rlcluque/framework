<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    final class registry {
        
        private $_registry = array();
        
        public function __construct () {
	    
			$local = Helper::loadObject('etc/local');
            
            if (empty($local)) throw new exceptionBase("Unexpected empty local configuration definitions. See 'etc/local.xml'",500);
	    
            foreach ($local as $config) {
				$name = $config->getName();
				$value = $config['value'];
				if ($value == '1' || $value == '0') {
					$value  = $value == 1 ? true: false;
					self::register($name,(bool)$value);
				} else if (is_numeric($value)) {
					$pepe = (int)$value;
					if ($name == 'debugMode') die('hola2');
					self::register($name,(int)$value);
				} else {
					$pepe = (string)$value;
					if ($name == 'debugMode') die('hola3');
					self::register($name,(string)$value);
				}
            }
            
			self::register("author","Rafa Luque");
            self::register("codeSite","UTF-8");
            self::register("timeSessionLogin",3600000);
            self::register("defaultLan",'es');
            self::register("rewriteMode",true);
			self::register("defaultDBConnection",'webframe');
			self::register("dbQueryCache",1);
			
			self::register("pathSeparator","/");
			self::register("modulesDirectory","module");
			self::register("viewerDirectory","viewer");
			self::register("translationDirectory","locale");
			self::register("viewer","default");
			self::register("skin_dir","skin/");
			self::register("plugin_dir","plugin/");
			self::register("idb_dir","var/idb/");
			self::register("tmp_dir","var/tmp/");
	    
        }
	
		public function register ($index,$value) {
			
			$this->_registry[$index] = $value;
			
		}
		
		public function registry ($index) {
			
			if (isset($this->_registry[$index])) return $this->_registry[$index];
			else return null;
			
		}
	
    }
    
?>