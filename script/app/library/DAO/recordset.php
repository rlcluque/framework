<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/dbConnection");

    final class recordset {
		
		private $dbConnection = null;
		private $item = null;
		private $list = null;
		private $index = -1;
		
		public function __construct (dbConnection $dbConnection=null) {
			
			if (empty($dbConnection)) $this->dbConnection = Bayou::dbConnection($dbConnection);
			else $this->dbConnection = $dbConnection;
			
		}
		
		public function is_empty() {
			
			return !(bool)$this->length();
			
		}
		
		public function current() {
			
			return $this->item;
			
		}
		
		public function length () {
			
			return count($this->list);
			
		}
		
		public function next () {
			
			if (empty($this->list)) return false;
			
			$this->index++;
			
			if ($this->index >= $this->length()) {
			$this->index = $this->length();
			return false;
			}
			
			if (!isset($this->list[$this->index])) return false;
			$this->item = $this->list[$this->index];
			
			return true;
			
		}
		
		public function flush_index() {
			
			$this->index = -1;
			$this->next();
			
		}
		
		public function command($pseudoSql=null, array $params=null, $conditions=null, array $order=null, $offset=0, $limit=10000000, array $groupBy=null) {
			
			$this->list = null;
			$this->index = -1;
			
			$result = $this->dbConnection->command($pseudoSql,$params,$conditions,$order,$offset,$limit,$groupBy);
			
			if (!empty($result)) $this->list = $result;
			
			return $this->next();
			
		}
		
		public function getCount($pseudoSql=null, array $params=null, $conditions=null) {
	    
			$this->list = null;
			$this->index = -1;
			
			$result = intval($this->dbConnection->command($pseudoSql,$params,$conditions));
		
			$this->next();
			
			return $result;
			
		}
		
		public function query ($sql) {
			
			$this->list = null;
			$this->index = -1;
			
			$result = $this->dbConnection->query($sql);
			
			if (!empty($result)) $this->list = $result;
			
			return $this->next();
			
		}
		
		public function getConnection() {
			
			return $this->dbConnection;
			
		}
		
    }

?>