<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/DAO/properties");
    Helper::requireFile("library/dbConnection");
    
    abstract class entity extends properties {
        
	public function __construct(dbConnection $dbConnection=null) {
	    
	    $entity = get_class($this);
	    
	    if (empty($dbConnection)) $connection = Bayou::dbConnection($dbConnection);
	    else $connection = $dbConnection;
	    
	    parent::__construct($connection);
	    
	}
    
    public function read($id) {
	    
	    if (!is_integer($id) || $id <= 0) throw new exceptionBase("Integer expected.",500);
	    if ($id <= 0) throw new exceptionBase("Ingeter bigger than 0 expected.",500);
	    
	    $this->loadDefaults();
	    $entity = $this->getEntity();
	    
	    $search = $this->getConnection()->command("GET ENTITIES $entity", array('#1'=>$id), "id = #1");
	    
	    if (empty($search)) return false;
	    
	    $object = $search[0];
	    
	    $this->load($object);
	    
	    return true;
	    
	}
        
	public function read_by($property,$value) {
	    
	    if (!$this->propertyExists($property)) throw new exceptionBase("Property '$property' does not exist.",500);
	    
	    $this->loadDefaults();
	    $entity = $this->getEntity();
	    
	    $search = $this->getConnection()->command("GET ENTITIES $entity", array('#1'=>$value), "$property = #1");
	    
	    if (empty($search)) return false;
	    
	    $object = $search[0];
	    
	    $this->load($object);
	    
	    return true;
	    
	}
	
	public function save() {
	    
	    $entity = $this->getEntity();
	    $properties = $this->getPropertiesName();
	    
	    $new_values = array();
	    foreach($properties as $property) {
			if ($property == 'id') continue;
			if ($this->$property instanceof entity) $new_values[$property] = $this->$property->id;
			else $new_values[$property] = $this->$property;
	    }
	    
	    $id = $this->id;
	    if ($id > 0) $rowsAffected = $this->getConnection()->execute("EDIT ENTITY $entity $id",$new_values);
	    else $insert_id = $this->getConnection()->execute("ADD ENTITY $entity",$new_values);
	    
	    if (isset($rowsAffected)) return (bool)$rowsAffected;
	    
	    if ($insert_id == 0) return false;
	    
	    $this->id = $insert_id;
	    
	    return true;
	    
        }
	
	public function remove($purge=false) {
	    
	    $entity = $this->getEntity();
	    $id = $this->id;
	    
	    $rowsAffected = $this->getConnection()->execute("REMOVE ENTITY $entity $id");
	    
	    if ($rowsAffected < 1) return false;
	    
	    if ($purge) $this->loadDefaults();
	    
	    return (bool)$rowsAffected;
	    
	}
	
	public function getObject() {
	    
	    return parent::getObject();
	    
	}
	
	public function load ($results) {
	    
	    $this->loadDefaults();
	    
	    if ($results===null) throw new exceptionBase("Null value",500);
	    
	    $propertiesName = $this->getPropertiesName();
	    foreach ($propertiesName as $property) {
			if (!isset($results->$property)) $value = null;
			else $value = $results->$property;
			parent::__set($property,$value);
	    }
	    
	}
	
    }
    
?>