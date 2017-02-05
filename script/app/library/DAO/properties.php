<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/dbConnection");
    Helper::requireFile("library/DAO/recordset");
    
    abstract class properties {
	
	private $__entity = null;
	private $dbConnection = null;
        
    private $properties = null;
    
    private $aProperties = null;
    private $source = null;
    
    protected function __construct(dbConnection $dbConnection) {
		
		$entity = get_class($this);
		$this->__entity = $entity;
		$entitySerialized = Bayou::registry("properties_$entity");
		$source = null;
		
		if (empty($entitySerialized)) {
			$source = new recordset($dbConnection);
			$succes = $source->command("GET PROPERTIES ENTITY $entity");
			if (!$succes) throw new exceptionBase('Error loading entity from database.',500);
			$entitySerialized = serialize($source);
			Bayou::register("properties_$entity",$entitySerialized);
		} else {
			$source = unserialize($entitySerialized);
		}
		
        $this->source = $source;
	    
		$this->dbConnection = $dbConnection;
        
        $this->loadDefaults();
        
    }
    
    public function __get($name) {
		
        if (!$this->propertyExists($name)) throw new exceptionBase("Property '$name' does not exist.",500);
		
        $property = $this->getValue($name);
		
		if ($this->isObject($name)) {
			$object = $name;
			if ($property instanceof $object) return $property;
			$id = $property;
			$property = Helper::getInstance("persistence/$object");
			if (is_null($id) || $id <= 0) return $property;
			if (!is_numeric($id)) throw new exceptionBase("Expected object or intenger and found '". gettype($id) ."'",500);
			if (!$property->read($id)) return $value;
		}
		
		return $property;
        
    }
    
    public function __set ($name, $value) {
	    
		if (!$this->propertyExists($name)) throw new exceptionBase("Property '$name' does not exist.",500);
		
		if ($name == 'id') {
			if (!is_null($value)) $new_value = $value;
		} elseif ($this->isObject($name)) {
			$object = $name;
            if (is_null($value)) {
				$instance = Helper::getInstance("persistence/$object");
				$new_value = $instance;
            } else {
				if (is_object($value)) {
					Helper::requireFile("persistence/$object");
					if (!class_exists($object)) throw new exceptionBase("Not definition of '$object' in file 'persistence/$object'",500);
					if (!($value instanceof $object)) throw new exceptionBase("Unexpected type '". gettype($value) ."'. Expecting '$object' object.",500);
					$new_value = $value;
				} else {
					if (!is_numeric($value)) throw new exceptionBase("Expected object or intenger and found '". gettype($value) ."'",500);
					$value = intval($value);
					//$instance = Helper::getInstance("persistence/$object");
					//$instance->read($value);
					//$new_value = $instance;
					$new_value = $value;
				}
			}
		} else {
			$new_value = $value;
		}
		
        $this->setValue($name,$new_value);
		
	}
	
	public function getEntity () {
		
		return $this->__entity;
	}
	
	public function getConnection() {
		
		return $this->dbConnection;
		
	}
	
	/*
	protected function __isset($name) {
		
		return (in_array($name,$this->aProperties));
			
	}
	*/
	
	protected function isObject ($name) {
        
        if (!in_array($name,$this->aProperties)) throw new exceptionBase("Property '$name' does not exist.",500);
        
        return ($this->dbConnection->utilities()->propertyIsEntity($this->properties[$name]['structure']) && $name != 'id');
        
    }
	
	protected function propertyExists ($name) {
	    
	    return (in_array($name,$this->getPropertiesName()));
	    
	}
	
	protected function getPropertiesName() {
	    
	    if (empty($this->aProperties)) throw new exceptionBase('Not loaded entity from database.',500);
	    
	    return $this->aProperties;
	    
	}
        
        protected function getArray() {
	    
	    if (empty($this->aProperties)) throw new exceptionBase('Not loaded entity from database.',500);
	    
	    $array = array();
	    $properties = $this->aProperties;
	    foreach ($properties as $property) {
		$array[$property] = $this->getValue($property);
	    }
            
            return $array;
	    
        }
	
	protected function getObject() {
	    
	    if (empty($this->aProperties)) throw new exceptionBase('Not loaded entity from database.',500);
	    
	    $object = new stdClass();
	    $properties = $this->aProperties;
	    
	    foreach ($properties as $property) $object->$property = $this->getValue($property);
            
            return $object;
	    
        }
	
	protected function loadDefaults() {
            
            if (empty($this->source)) throw new exceptionBase('Not loaded entity from database.',500);
            
			$this->properties = null;
			$this->aProperties = null;
	    
            $recordset = $this->source;
			$recordset->flush_index();
			do {
				$property = $recordset->current();
				$name = $this->dbConnection->utilities()->getPropertyName($property);
				$this->aProperties[] = $name;
				$this->properties[$name]['structure'] = $property;
				$this->__set($name,$this->dbConnection->utilities()->getPropertyDefault($property));
			} while ($recordset->next());
	   
        }
        
        // Getter and Setter a m‡s bajo nivel.
        private function setValue($name,$value) {
            
            if (!in_array($name,$this->aProperties)) throw new exceptionBase("Property '$name' does not exist.",500);
            
            if (is_null($value)) {
                $this->properties[$name]['value'] = null;
                return true;
            }
	    
			if ($this->isObject($name)) {
				if (!is_object($value)) if (!settype($value,'integer')) throw new exceptionBase("Error in conversion to integer type.",500);
				$this->properties[$name]['value'] = $value;
				return true;
			}
            
            if ($this->dbConnection->utilities()->propertyIsDecimal($this->properties[$name]['structure'])) {
				if (!settype($value,'float')) throw new exceptionBase("Error in conversion to float type.",500);
                $this->properties[$name]['value'] = $value;
                return true;
            }
            
            if ($this->dbConnection->utilities()->propertyIsNumeric($this->properties[$name]['structure'])) {
				if (!settype($value,'integer')) throw new exceptionBase("Error in conversion to integer type.",500);
				$this->properties[$name]['value'] = $value;
                return true;
            }
	    
			if ($this->dbConnection->utilities()->propertyIsDate($this->properties[$name]['structure'])) {
				if (is_numeric($value)) {
					if (!settype($value,'integer')) throw new exceptionBase("Error in conversion to unix time.",500);
					$this->properties[$name]['value'] = $this->dbConnection->utilities()->getTime($value);
				} else if (is_string($value)) {
					$value = strtotime($value);
					if ($value === false) throw new exceptionBase("String format error. String time needed.",500);
					$this->properties[$name]['value'] = $this->dbConnection->utilities()->getTime($value);
				}
				return true;
            }
            
            $this->properties[$name]['value'] = $value;
            
            return true;
            
        }
        
        private function getValue($name) {
            
            if (!in_array($name,$this->aProperties)) throw new exceptionBase("Property '$name' does not exist.",500);
            
            return $this->properties[$name]['value'];
            
        }
        
    }
    
?>