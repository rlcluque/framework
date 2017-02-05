<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/source");
    
    //final class library.dbConnection {
    final class dbConnection {
        
		private $source = null;
		private $connection = null;
		
		public function __construct ($sourceName) {
			
			if (!is_string($sourceName)) throw new exceptionBase("String expected",500);
			$check = '/^[a-zA-Z0-9_]+/is';
			if (!preg_match($check,$sourceName)) throw new exceptionBase("Unexpected db source name '$sourceName'",500);
			
			$this->source = new source($sourceName);
			
			switch ($this->source->driver) {
				case 'mysql':
					Helper::requireFile("library/db/mysql/connection");
					$this->connection = new connection($this->source);
				break;
			}
				
		}
		
		public function __call($method,$arguments) {
			
			$connectionMethods = get_class_methods($this->connection);
			
			if (in_array($method,$connectionMethods)) return call_user_func_array(array($this->connection,$method),$arguments);
			else throw new exceptionBase("Unknown method called",500);
			
		}
		
		public function getConnection() {
			
			return $this->connection->getDbConnection();
			
		}
	
    }
    
?>