<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    final class source {
	
		private $driver = null;
		private $host = null;
		private $port = null;
		private $schema = null;
		private $user = null;
		private $password = null;
		
		private $availableDrivers = array('mysql');
		
		public function __construct ($sourceName) {
			
			$source = Helper::loadObject("source/$sourceName");
			
			foreach ($source as $config) {
				$name = $config->getName();
				$value = $config['value'];
				if ($value == '1' || $value == '0') $this->$name = (bool)$value;
				else if (is_numeric($value)) $this->$name = (int)$value;
				else $this->$name = (string)$value;
			}
			
			if (!in_array($this->driver,$this->availableDrivers)) throw new exceptionBase("Unknown data base driver '".$this->driver. "'",500);
			
		}
		
		public function __get($name) { return $this->$name; }
	
    }

?>