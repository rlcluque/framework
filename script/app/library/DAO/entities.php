<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/dbConnection");

    final class entities {
	
	private $entity = null;
	private $item = null;
	private $list = null;
	private $index = -1;
	
	public function __construct ($entity) {
	    
	    if (empty($entity) || !is_string($entity)) throw new exceptionBase('Not empty string expected',500);
	    else $this->entity = $entity;
	    
	}
	
	public function is_empty() {
	    
	    return !(bool)$this->length();
	    
	}
	
	public function current() {
	    
	    return $this->item;
	    
	}
	
	public function length() {
	    
	    return count($this->list);
	    
	}
	
	public function next() {
	    
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
	
	public function command(array $params=null, $conditions=null, array $order=null, $offset=0, $limit=10000000, array $groupBy=null, $join=null) {
	    
	    $this->list = null;
	    $this->index = -1;
	    $pseudoSql = 'GET ENTITIES ' . $this->entity . " $join";
	    $search = Helper::getInstance('library/DAO/recordset');
	    
	    if (!$search->command($pseudoSql,$params,$conditions,$order,$offset,$limit,$groupBy)) return $this->next();
	    
	    $entities = array();
	    $entity = Helper::getInstance('persistence/'.$this->entity);
	    do {
			$clone = clone $entity;
			$clone->load($search->current());
			$entities[] = $clone;
	    } while($search->next());
	    
	    $this->list = $entities;
	    
	    return $this->next();
	    
	}
	
	public function getCount(array $params=null, $conditions=null, array $order=null, $offset=0, $limit=10000000, array $groupBy=null) {
	    
	    $pseudoSql = 'GET ENTITIES COUNT ' . $this->entity;
	    $search = Helper::getInstance('library/DAO/recordset');
	    
	    return $search->command($pseudoSql,$params,$conditions,$order,$offset,$limit,$groupBy);
	    
	}
	
    }

?>