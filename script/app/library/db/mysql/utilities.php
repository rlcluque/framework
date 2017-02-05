<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/external/ezdb1");
    
    //final class library.db.mysql.utilities {
    final class utilities {
	
	private $numericTypes = array('int','float','double','decimal','bit','binary','year','bool');
	private $decimalTypes = array('float','double','decimal');
	private $stringTypes = array('varchar','text','char','blob');
	private $dateTypes = array('time','date','datetime','timestamp');
	
	public function __construct () {
            
        }
	
	public function getPropertyName($property) {
	    
	    if (empty($property)) return null;
	    if (!isset($property->Field)) return null;
	    
	    return $property->Field;
	    
	}
	
	public function getPropertyType($property) {
	    
	    if (empty($property)) return null;
	    if (!isset($property->Type)) return null;
	    
	    return $property->Type;
	    
	}
	
	public function getPropertyNull($property) {
	    
	    if (empty($property)) return null;
	    if (!isset($property->Null)) return null;
	    
	    return $property->Null;
	    
	}
	
	public function getPropertyKey($property) {
	    
	    if (empty($property)) return null;
	    if (!isset($property->Key)) return null;
	    
	    return $property->Key;
	    
	}
	
	public function getPropertyValidation($property) {
	    
	    if (empty($property)) return null;
	    if (!isset($property->Comment)) return null;
	    
	    return $property->Comment;
	    
	}
	
	public function getPropertyDefault($property) {
	    
	    if (empty($property)) return null;
	    
	    $name = $this->getPropertyName($property);
	    if ($name == 'id') return 0;
	    
	    //if (!isset($property[8])) return null;
	    
	    $default = $property->Default;
	    if ($this->propertyIsEntity($property)) {
		if (empty($default)) return null;
	    }
	    
	    return $default;
	    
	}
	
	public function propertyIsEntity($property) {
	    
	    if (empty($property)) throw new exceptionBase("Unexpected empty array",500);
	    
	    $key = $this->getPropertyKey($property);
	    
	    return (!empty($key) && $key != 'UNI');
	    
	}
	
	public function propertyIsUnique($property) {
	    
	    if (empty($property)) throw new exceptionBase("Unexpected empty array",500);
	    
	    $key = $this->getPropertyKey($property);
            
            return ($key == 'UNI');
	    
	}
	
	public function propertyIsNotNull(array $property) {
	    
	    if (empty($property)) throw new exceptionBase("Unexpected empty array",500);
	    
	    $value = $this->getPropertyNull($property);
            
            return ($value == 'NO');
	    
	}
	
	public function propertyIsDecimal ($property) {
	    
	    $propertyType = $this->getPropertyType($property);
	    
	    $types = $this->decimalTypes;
	    foreach($types as $type) {
		if (strpos($propertyType,$type) !== false) return true;
	    }
	    
	    return false;
	    
	}
	
	public function propertyIsNumeric ($property) {
	    
	    $propertyType = $this->getPropertyType($property);
	    
	    $types = $this->numericTypes;
	    foreach($types as $type) {
		if (strpos($propertyType,$type) !== false) return true;
	    }
	    
	    return false;
	    
	}
	
	public function propertyIsDate ($property) {
	    
	    $propertyType = $this->getPropertyType($property);
	    
	    $types = $this->dateTypes;
	    foreach($types as $type) {
			if (strpos($propertyType,$type) !== false) return true;
	    }
	    
	    return false;
	    
	}
	
	public function validateDatetime ($var) {
	    
	    if (empty($var) || !is_string($var)) return false;
	    
	    if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{0,2}/',$var)) return false;
	    
	    $datetime = explode(' ',$var);
	    $fulldate = $datetime[0];
	    $fulltime = $datetime[1];
	    
	    $date = explode('-',$fulldate);
	    $time = explode(':',$fulltime);
	    
	    $year = intval($date[0]);
	    $month = intval($date[1]);
	    $day = intval($date[2]);
	    
	    $hour = intval($time[0]);
	    $minute = intval($time[1]);
	    $second = intval($time[2]);
	    
	    return checkdate($month,$day,$year);
	    
	}
	
	public function getTime($unix=null) {
	    
	    if (!empty($unix) && !is_int($unix)) throw new exceptionBase("Unix times expected",500);
	    if (empty($unix)) $unix = time();
	    
	    return date('Y-m-d H:i:s',$unix);
	    
	}
	
	public function getDate($unix=null) {
	    
	    if (!empty($unix) && !is_int($unix)) throw new exceptionBase("Unix times expected",500);
	    if (empty($unix)) $unix = time();
	    
	    return date('Y-m-d',$unix);
	    
	}
	
    }
    
?>