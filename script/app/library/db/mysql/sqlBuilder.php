<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    final class sqlBuilder {
        
        private $initialized = false;
        
        private $availabledOperations = array('select','insert','update','delete','show columns','entities count','view count');
        private $operation = null;
        
        // For insert and update clauses
        private $sets = null;
        
        // For select clauses
        private $fields = null;
        private $groupBy = null;
        private $distinct = null;
        
        // For general use
        private $table = null;
        private $params = null;
        private $conditions =  null;
        private $order = null;
        private $offset = null;
        private $limit = null;
        
        public function __construct ($pseudoSql=null) {
            
            if (empty($pseudoSql)) return true;
            
            $operation = $this->pseudoSqlValidate($pseudoSql);
            
            if (!in_array($operation,$this->availabledOperations)) throw new exceptionBase('Unexpected operation',500);
            
            $this->operation = $operation;
            
        }
        
        public function getSqlQuery() {
            
            if (!in_array($this->operation,$this->availabledOperations)) throw new exceptionBase('Unexpected operation',500);
            
            $sqlQuery = '';
            
            switch ($this->operation) {
                case 'select':
                    $select = $this->getClauseSelect();
                    $from = $this->getClauseFrom();
                    $where = $this->getClauseWhere();
                    $groupBy = $this->getClauseGroupBy();
                    $order = $this->getClauseOrder();
                    $limit = $this->getClauseLimitOffset();
                    $sqlQuery = "$select $from $where $groupBy $order $limit";
                break;
                case 'insert':
                    $insert = $this->getClauseInsert();
                    $set = $this->getClauseSet();
                    $sqlQuery = "$insert $set";
                break;
                case 'update':
                    $update = $this->getClauseUpdate();
                    $set = $this->getClauseSet();
                    $where = $this->getClauseWhere();
                    $order = $this->getClauseOrder();
                    $limit = $this->getClauseLimit();
                    $sqlQuery = "$update $set $where $order $limit";
                break;
                case 'delete':
                    $delete = $this->getClauseDelete();
                    $where = $this->getClauseWhere();
                    $order = $this->getClauseOrder();
                    $limit = $this->getClauseLimit();
                    $sqlQuery = "$delete $where $order $limit";
                break;
                case 'show columns':
                    $show = $this->getClauseShowColumns();
                    $sqlQuery = $show;
                break;
                case 'view count':
                    $select = $this->getClauseSelect();
                    $from = $this->getClauseFrom();
                    $where = $this->getClauseWhere();
                    $sqlQuery = "$select $from $where";
                break;
                case 'entities count':
                    $select = $this->getClauseSelect();
                    $from = $this->getClauseFrom();
                    $where = $this->getClauseWhere();
                    $sqlQuery = "$select $from $where";
                break;
                default:
                    throw new exceptionBase('Unexpected operation',500);
                break;
            }
            
            $sqlQuery = trim($sqlQuery);
            $sqlQuery = preg_replace('/\s\s+/',' ',$sqlQuery);
            
            return $sqlQuery;
            
        }
        
        public function setFields(array $fields) {
            
            $this->fields = $fields;
            
        }
        
        public function setTable($table) {
            
            $this->table = $table;
            
        }
        
        public function setDistinct($distinct) {
            
            $this->distinct = $distinct;
            
        }
        
        public function setSets(array $sets) {
            
            $this->sets = $sets;
            
        }
        
        public function setParams(array $params) {
            
            $this->params = $params;
            
        }
        
        public function setConditions($conditions) {
            
            if (!is_string($conditions) && !is_array($conditions)) throw new exceptionBase('String or array needed',500);
            
            $this->conditions = $conditions;
            
        }
        
        public function setOrder(array $order) {
            
            $this->order = $order;
            
        }
        
        public function setOffset($offset) {
            
            if (!empty($offset)) $this->offset = intval($offset);
            
        }
        
        public function setLimit($limit) {
            
            if (!empty($limit)) $this->limit = intval($limit);
            
        }
        
        public function setGroupBy(array $groupBy) {
            
            $this->groupBy = $groupBy;
            
        }
        
        public function getOperation() {
            
            return $this->operation;
            
        }
        
        public function setOperation($operation) {
            
            if (!in_array($this->operation,$this->availabledOperations)) throw new exceptionBase('Unexpected operation',500);
            
            $this->operation = $operation;
            
        }
        
        
        // Get clauses:
        
        public function getClauseSelect() {
            
            $distinct = $this->distinct;
            
            return "SELECT $distinct " . join(', ',$this->fields);
            
        }
        
        public function getClauseInsert() {
            
            return "INSERT INTO " . $this->table;
            
        }
        
        public function getClauseUpdate() {
            
            return "UPDATE " . $this->table;
            
        }
        
        public function getClauseDelete() {
            
            return 'DELETE ' . $this->getClauseFrom();
            
        }
        
        public function getClauseShowColumns() {
            
            return 'SHOW FULL COLUMNS ' . $this->getClauseFrom();
            
        }
        
        public function getClauseFrom() {
            
            return "FROM " . $this->table;
            
        }
        
        public function getClauseSet() {
            
            $sets = $this->sets;
            
            if (empty($sets)) return '';
            
            $aSets = array();
            $names = array_keys($sets);
            foreach ($names as $name) {
                $value = $sets[$name];
                if (is_null($value)) {
                    $value = 'NULL';
                } elseif (is_string($value)) {
                    $value = "'". mysqli_real_escape_string(Bayou::dbConnection()->getConnection(),stripslashes($value)) ."'";
                }
                $aSets[] = "$name = $value";
            }
            return 'SET ' . join(', ',$aSets);
            
        }
        
        public function getClauseWhere() {
            
            $conditions= $this->conditions;
            $params = $this->params;
            
            $where = $this->buildWhereClause($conditions);
            if (empty($params)) return $where;
            
            $namesParams = array_keys($params);
            foreach($namesParams as $name) {
                $value = $params[$name];
                if (is_null($value)) $value = 'NULL';
                elseif (is_string($value)) $value = "'$value'";
                $where = str_replace($name,$value,$where);
            }
            
            $pattern = '/(\s*=\s*NULL)/';
            $where = preg_replace($pattern,' IS NULL',$where);
            $where = "WHERE $where";
            
            return $where;
            
        }
        
        public function getClauseGroupBy() {
            
            if (empty($this->groupBy)) return '';
            
            return 'GROUP BY ' . join(', ',$this->groupBy);
            
        }
        
        public function getClauseOrder() {
            
            if (empty($this->order)) return '';
            
            $order = $this->order;
            $array = array();
            
            $fields = array_keys($order);
            foreach($fields as $field) {
                $type = $order[$field];
                $array[] = "$field $type";
            }
            
            return 'ORDER BY ' . join(', ',$array);
            
        }
        
        public function getClauseLimitOffset() {
            
            if (empty($this->limit)) return '';
            
            $clause = 'LIMIT ' . $this->limit;
            
            if ($this->offset !== null) $clause .= ' OFFSET ' . $this->offset;
            
            return $clause;
            
        }
        
        public function getClauseLimit() {
            
            if (empty($this->limit)) return '';
            
            $clause = 'LIMIT ' . $this->limit;
            
            return $clause;
            
        }
        
        static public function pseudoSqlValidate ($pseudoSql) {
            
            if (empty($pseudoSql)) throw new exceptionBase('Unexpected empty string',500);
            if (!is_string($pseudoSql)) throw new exceptionBase('Unexpected type',500);
	    
            $operation = '';
            
            //if (preg_match('/^GET\s+ENTITIES\s+([a-z0-9_]+)\s*\z/is',$pseudoSql)) {
            if (preg_match('/^GET\s+ENTITIES\s+([a-z0-9_]+)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql)) {
                $operation = 'select';
            } elseif (preg_match('/^GET\s+ENTITIES\s+COUNT\s([a-z0-9_]+)\s*\z/is',$pseudoSql)) {
                $operation = 'entities count';
            //} elseif (preg_match('/^GET\s+VIEW\s+COUNT\s+(DISTINCT\s+)?(\([a-z0-9_\.]+\))\s+FROM\s+\(([a-z0-9_\,]+)\)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
            } elseif (preg_match('/^GET\s+VIEW\s+COUNT\s+(\((DISTINCT\s+)?[a-z0-9_\.]+\))\s+FROM\s+\(([a-z0-9_\,]+)\)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                $operation = 'view count';
            } elseif (preg_match('/^GET\s+PROPERTIES\s+ENTITY\s+([a-z0-9_]+)\s*\z/is',$pseudoSql)) {
                $operation = 'show columns';
            } elseif (preg_match('/^GET\s+VIEW\s+(DISTINCT\s+)?(\([a-z0-9_\,\.]+\))\s+FROM\s+\(([a-z0-9_\,]+)\)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                $operation = 'select';
            } elseif (preg_match('/^ADD\s+ENTITY\s+([a-z0-9_]+)\s*\z/is',$pseudoSql)) {
                $operation = 'insert';
            } elseif (preg_match('/^EDIT\s+ENTITY\s+([a-z0-9_]+)\s+([0-9]+)\s*\z/is',$pseudoSql)) {
                $operation = 'update';
            } elseif (preg_match('/^REMOVE\s+ENTITY\s+([a-z0-9_]+)\s+([0-9]+)\s*\z/is',$pseudoSql)) {
                $operation = 'delete';
            } else {
                throw new exceptionBase('Bad pseudo-sql syntax',500);
            }
            
            return $operation;
            
        }
	
        static public function pseudoSqlGetFields ($pseudoSql) {
            
            if (empty($pseudoSql)) throw new exceptionBase('Unexpected empty string',500);
            if (!is_string($pseudoSql)) throw new exceptionBase('Unexpected type',500);
	    
            $fields = array();
            
            //if (preg_match('/^GET\s+ENTITIES\s+([a-z0-9_]+)\s*\z/is',$pseudoSql,$result)) {
                //$fields[] = $result[1].'.*';
                //$fields[] = '*';
            if (preg_match('/^GET\s+ENTITIES\s+([a-z0-9_]+)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                //var_dump($result);die();
                $fields[] = $result[1].'.*';
                //$fields[] = '*';
            } elseif (preg_match('/^GET\s+VIEW\s+(DISTINCT\s+)?(\([a-z0-9_\,\.]+\))\s+FROM\s+(\([a-z0-9_\,]+\))\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                $sFields = $result[2];
                if (!preg_match('/^\([a-z0-9_]+\.[a-z0-9_]+(\,[a-z0-9_]+\.[a-z0-9_]+)*\)\z/',$sFields)) throw new exceptionBase('Select clause not valid',500);
                if (!preg_match_all('/([a-z0-9_]+\.[a-z0-9_]+)+/',$sFields,$result)) throw new exceptionBase('Fields of select clause not valid',500);
                $fields = $result[0];
            } elseif (preg_match('/^GET\s+ENTITIES\s+COUNT\s([a-z0-9_]+)\s*\z/is',$pseudoSql)) {
                $fields[] = 'COUNT(*)';
            } elseif (preg_match('/^GET\s+VIEW\s+COUNT\s+\(((DISTINCT\s+)?[a-z0-9_\.]+)\)\s+FROM\s+\(([a-z0-9_\,]+)\)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                $fields[] = 'COUNT('.$result[1].')'; // SELECT COUNT(DISTINCT column_name) FROM table_name
            }
            
            return $fields;
            
        }
        
        static public function pseudoSqlGetDistinct($pseudoSql) {
            
            if (empty($pseudoSql)) throw new exceptionBase('Unexpected empty string',500);
            if (!is_string($pseudoSql)) throw new exceptionBase('Unexpected type',500);
            
            $distinct = '';
            
            if (preg_match('/^GET\s+VIEW\s+DISTINCT\s+/',$pseudoSql)) $distinct = 'DISTINCT';
            if (preg_match('/^GET\s+ENTITIES\s+/',$pseudoSql)) $distinct = 'DISTINCT';
            
            return $distinct;
        }
	
        /* todo hacer: "GET ENTITIES lo_que_sea JOIN ...." */
        static public function pseudoSqlGetTables ($pseudoSql) {
            
            if (empty($pseudoSql)) throw new exceptionBase('Unexpected empty string',500);
            if (!is_string($pseudoSql)) throw new exceptionBase('Unexpected type',500);
	    
            $tables = '';
            
            if (preg_match('/^GET\s+(ENTITIES|PROPERTIES\s+ENTITY)\s+([a-z0-9_]+)\s*\z/is',$pseudoSql,$result)) {
                $tables = $result[2];
                
                
            } elseif (preg_match('/^GET\s+ENTITIES\s+([a-z0-9_]+)\s+(\s*JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                $sTables = $result[1];
                $aTables = explode(',',$sTables);
                if (empty($result[3])) $sJoins = ''; else $sJoins = $result[3];
                $tables = $sTables;
                if (!empty($sJoins)) {
                    if (!preg_match('/^\([a-z0-9_\.]+(<\->|\-\->|<\-\-)[a-z0-9_\.]+(\,[a-z0-9_\.]+(<\->|\-\->|<\-\-)[a-z0-9_\.]+)*\)\z/',$sJoins)) throw new exceptionBase('From clause not valid',500);
                    if (!preg_match_all('/([a-z0-9_\.]+)((<\->|\-\->|<\-\-)([a-z0-9_\.]+))?/',$sJoins,$result)) throw new exceptionBase('Tables of from clause not valid',500);
                    $aLeft = $result[1];
                    $aJoin = $result[3];
                    $aRight = $result[4];
                    $i = 0;
                    foreach($aLeft as $leftTable) {
                        if (isset($aJoin[$i]) xor isset($aRight[$i])) throw new exceptionBase('Tables of from clause not valid',500);
                        if (isset($aJoin[$i]) && isset($aRight[$i]) && $aJoin[$i] != '' && $aRight[$i] != '')  {
                            $join = $aJoin[$i];
                            $rightTable = $aRight[$i];
                            if (in_array($rightTable,$aTables)) $table = str_replace('.id','',$leftTable);
                            else $table = str_replace('.id','',$rightTable);
                            
                            if (strpos($leftTable,'.id') === false) $leftTable .= '.' . str_replace('.id','',$rightTable);
                            else if (strpos($rightTable,'.id') === false) $rightTable .= '.' . str_replace('.id','',$leftTable);
                            
                            switch ($join) {
                                case '<->':
                                    $tables .= " INNER JOIN $table ON $leftTable = $rightTable";
                                break;
                                case '-->':
                                    $tables .= " LEFT JOIN $table ON $leftTable = $rightTable";
                                break;
                                case '<--':
                                    $tables .= " RIGHT JOIN $table ON $leftTable = $rightTable";
                                break;
                                default:
                                    throw new exceptionBase('Join of from clause not valid',500);
                                break;
                            }
                        } else {
                            if ($i > 0) $tables .= ", $leftTable";
                        }
                        $i++;
                    }
                    //die($tables);
                }
            
            
            
            } elseif (preg_match('/^GET\s+VIEW\s+(DISTINCT\s+)?(\([a-z0-9_\,\.]+\))\s+FROM\s+\(([a-z0-9_\,]+)\)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                $sTables = $result[3];
                $aTables = explode(',',$sTables);
                if (empty($result[5])) $sJoins = ''; else $sJoins = $result[5];
                $tables = $sTables;
                if (!empty($sJoins)) {
                    if (!preg_match('/^\([a-z0-9_\.]+(<\->|\-\->|<\-\-)[a-z0-9_\.]+(\,[a-z0-9_\.]+(<\->|\-\->|<\-\-)[a-z0-9_\.]+)*\)\z/',$sJoins)) throw new exceptionBase('From clause not valid',500);
                    if (!preg_match_all('/([a-z0-9_\.]+)((<\->|\-\->|<\-\-)([a-z0-9_\.]+))?/',$sJoins,$result)) throw new exceptionBase('Tables of from clause not valid',500);
                    $aLeft = $result[1];
                    $aJoin = $result[3];
                    $aRight = $result[4];
                    $i = 0;
                    foreach($aLeft as $leftTable) {
                        if (isset($aJoin[$i]) xor isset($aRight[$i])) throw new exceptionBase('Tables of from clause not valid',500);
                        if (isset($aJoin[$i]) && isset($aRight[$i]) && $aJoin[$i] != '' && $aRight[$i] != '')  {
                            $join = $aJoin[$i];
                            $rightTable = $aRight[$i];
                            if (in_array($rightTable,$aTables)) $table = str_replace('.id','',$leftTable);
                            else $table = str_replace('.id','',$rightTable);
                            
                            if (strpos($leftTable,'.id') === false) $leftTable .= '.' . str_replace('.id','',$rightTable);
                            else if (strpos($rightTable,'.id') === false) $rightTable .= '.' . str_replace('.id','',$leftTable);
                            
                            switch ($join) {
                                case '<->':
                                    $tables .= " INNER JOIN $table ON $leftTable = $rightTable";
                                break;
                                case '-->':
                                    $tables .= " LEFT JOIN $table ON $leftTable = $rightTable";
                                break;
                                case '<--':
                                    $tables .= " RIGHT JOIN $table ON $leftTable = $rightTable";
                                break;
                                default:
                                    throw new exceptionBase('Join of from clause not valid',500);
                                break;
                            }
                        } else {
                            if ($i > 0) $tables .= ", $leftTable";
                        }
                        $i++;
                    }
                    //die($tables);
                }
            //} elseif (preg_match('/^GET\s+VIEW\s+COUNT\s+(DISTINCT\s+)?(\([a-z0-9_\.]+\))\s+FROM\s+\(([a-z0-9_\,]+)\)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
            } elseif (preg_match('/^GET\s+VIEW\s+COUNT\s+\(((DISTINCT\s+)?[a-z0-9_\.]+)\)\s+FROM\s+\(([a-z0-9_\,]+)\)\s*(\s+JOIN\s+(\([a-z0-9_\,\.\<\>\-]+\)))?\s*\z/',$pseudoSql,$result)) {
                $sTables = $result[3];
                $aTables = explode(',',$sTables);
                if (empty($result[5])) $sJoins = ''; else $sJoins = $result[5];
                $tables = $sTables;
                if (!empty($sJoins)) {
                    if (!preg_match('/^\([a-z0-9_\.]+(<\->|\-\->|<\-\-)[a-z0-9_\.]+(\,[a-z0-9_\.]+(<\->|\-\->|<\-\-)[a-z0-9_\.]+)*\)\z/',$sJoins)) throw new exceptionBase('From clause not valid',500);
                    if (!preg_match_all('/([a-z0-9_\.]+)((<\->|\-\->|<\-\-)([a-z0-9_\.]+))?/',$sJoins,$result)) throw new exceptionBase('Tables of from clause not valid',500);
                    $aLeft = $result[1];
                    $aJoin = $result[3];
                    $aRight = $result[4];
                    $i = 0;
                    foreach($aLeft as $leftTable) {
                        if (isset($aJoin[$i]) xor isset($aRight[$i])) throw new exceptionBase('Tables of from clause not valid',500);
                        if (isset($aJoin[$i]) && isset($aRight[$i]) && $aJoin[$i] != '' && $aRight[$i] != '')  {
                            $join = $aJoin[$i];
                            $rightTable = $aRight[$i];
                            if (in_array($rightTable,$aTables)) $table = str_replace('.id','',$leftTable);
                            else $table = str_replace('.id','',$rightTable);
                            
                            if (strpos($leftTable,'.id') === false) $leftTable .= '.' . str_replace('.id','',$rightTable);
                            else if (strpos($rightTable,'.id') === false) $rightTable .= '.' . str_replace('.id','',$leftTable);
                            
                            switch ($join) {
                                case '<->':
                                    $tables .= " INNER JOIN $table ON $leftTable = $rightTable";
                                break;
                                case '-->':
                                    $tables .= " LEFT JOIN $table ON $leftTable = $rightTable";
                                break;
                                case '<--':
                                    $tables .= " RIGHT JOIN $table ON $leftTable = $rightTable";
                                break;
                                default:
                                    throw new exceptionBase('Join of from clause not valid',500);
                                break;
                            }
                        } else {
                            if ($i > 0) $tables .= ", $leftTable";
                        }
                        $i++;
                    }
                    //die($tables);
                }
                //EDIT ENTITY lo_que_sea 3007
            } elseif (preg_match('/^(EDIT|REMOVE)\s+ENTITY\s+([a-z0-9_]+)\s*([0-9]+)\s*\z/is',$pseudoSql,$result)) {
                $tables = $result[2];
            } elseif (preg_match('/^ADD\s+ENTITY\s+([a-z0-9_]+)\s*\z/is',$pseudoSql,$result)) {
                $tables = $result[1];
            } elseif (preg_match('/^GET\s+ENTITIES\s+COUNT\s+([a-z0-9_]+)\s*\z/is',$pseudoSql,$result)) {
                $tables = $result[1];
            } else {
                
                throw new exceptionBase($pseudoSql,500);
            }
            
            return $tables;
	    
	}
        
        static public function pseudoSqlGetId ($pseudoSql) {
            
            if (empty($pseudoSql)) throw new exceptionBase('Unexpected empty string',500);
            if (!is_string($pseudoSql)) throw new exceptionBase('Unexpected type',500);
	    
	    $id = '';
            
            if (preg_match('/^(EDIT|REMOVE)\s+ENTITY\s+([a-z0-9_]+)\s*([0-9]+)\s*\z/is',$pseudoSql,$result)) {
                $id = $result[3];
            }
            
            return $id;
            
        }
        
        private function buildWhereClause($conditions) {
            
            if (empty($conditions)) return '';
            
            if (is_string($conditions)) return $conditions;
            
            $operator = $conditions[0];
            $components = array();
            for($i=1;$i<count($conditions);$i++) {
                $components[] = $this->buildWhereClause($conditions[$i]);
            }
            
            $components = array_filter($components);
            
            $sConditions = '('.join(" $operator ",$components).')';
            
            return $sConditions;
            
        }
        
    }
    
?>