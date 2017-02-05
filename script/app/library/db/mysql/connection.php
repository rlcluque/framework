<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/source");
    Helper::requireFile("library/external/ezdb1");
    Helper::requireFile("library/db/mysql/connection");
    Helper::requireFile("library/db/mysql/sqlBuilder");
    Helper::requireFile("library/db/mysql/utilities");
    
    //final class library.db.mysql.connection {
    final class connection {
        
		private $source = null; // source
        private $connection = null; // ezdb1
		private $utilities = null; // utilities
		private $queryCache = null; // boolean
		private $cache = null;  // array
        
        public function __construct (source $sourceName) {
	    
			$this->source = $sourceName;
			$this->connection = new db($this->source->user,$this->source->password,$this->source->schema,$this->source->host);
			$this->utilities = new utilities();
			$this->queryCache = Bayou::registry("dbQueryCache");
			
			if ($this->queryCache) $this->cache = array();
            
        }
		
		public function utilities() {
			
			return $this->utilities;
			
		}
		
		public function command($pseudoSql, $params=array(), $conditions=null, array $order=null, $offset=null, $limit=null, array $groupBy=null) {
			
			$sqlBuilder = new sqlBuilder($pseudoSql);
			
			$operation = $sqlBuilder->getOperation();
			
			if ($operation != 'select' && $operation != 'show columns' && $operation != 'entities count' && $operation != 'view count') throw new exceptionBase("Only use 'command' function with 'select' or 'show columns' operations",500);
			
			$fields = sqlBuilder::pseudoSqlGetFields($pseudoSql);
			$tables = sqlBuilder::pseudoSqlGetTables($pseudoSql);
			$distinct = sqlBuilder::pseudoSqlGetDistinct($pseudoSql);
			
			$sqlBuilder->setFields($fields);
			$sqlBuilder->setTable($tables);
			$sqlBuilder->setDistinct($distinct);
			if (!empty($params)) $sqlBuilder->setParams($params);
			if (!empty($conditions)) $sqlBuilder->setConditions($conditions);
			if (!empty($order)) $sqlBuilder->setOrder($order);
			$sqlBuilder->setOffset($offset);
			$sqlBuilder->setLimit($limit);
			if (!is_null($groupBy)) $sqlBuilder->setGroupBy($groupBy);
			
			$sql = $sqlBuilder->getSqlQuery();
			
			//Helper::logError($sql);
			
			if (isset($this->cache[$sql])) return $this->cache[$sql];
			
			//Helper::logError($sql);
			
			if ($operation == 'entities count' || $operation == 'view count') $result = intval($this->connection->get_var($sql));
			else $result = $this->connection->get_results($sql);
			
			$this->cache[$sql] = $result;
			
			return $result;
			
		}
		
		public function execute($pseudoSql,array $sets=null, array $params=array(), $conditions=null, array $order=null, $limit=10000000) {
			
			$sqlBuilder = new sqlBuilder($pseudoSql);
			
			$operation = $sqlBuilder->getOperation();
			
			if ($operation == 'select' || $operation == 'show columns' || $operation == 'entities count' || $operation == 'view count') throw new exceptionBase("Use 'command' function with 'select' or 'show columns' operations",500);
			
			$id = (integer)sqlBuilder::pseudoSqlGetId($pseudoSql);
			if (empty($conditions)) $conditions = "id = #id";
			else $conditions = array('AND',"id = #id",$conditions);
			$params['#id'] = $id;
			
			$tables = sqlBuilder::pseudoSqlGetTables($pseudoSql);
			
			$sqlBuilder->setTable($tables);
			
			if ($operation != 'delete') $sqlBuilder->setSets($sets);
			if (!empty($params)) $sqlBuilder->setParams($params);
			if (!empty($conditions)) $sqlBuilder->setConditions($conditions);
			if (!empty($order)) $sqlBuilder->setOrder($order);
			$sqlBuilder->setLimit($limit);
			
			$sql = $sqlBuilder->getSqlQuery();
			
			//Helper::logError($sql);
			
			return $this->connection->query($sql);
			
		}
		
		public function query($sql) {
			
			return $this->connection->get_results($sql);
			
		}
		
		public function sqlBuilder() {
			
			$sqlBuilder = new sqlBuilder();
			
			return $sqlBuilder;
			
		}
		
		public function getDbConnection() {
			
			return $this->connection->dbh;
			
		}
		
    }
    
?>