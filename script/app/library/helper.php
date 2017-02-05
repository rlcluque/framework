<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    final class Helper {
	
	private static $translation = array();
	
	public static function user_logged () {
	    
	    if (!isset($_COOKIE['bayou_user'])) return false;
	    if (!isset($_COOKIE['bayou_pw'])) return false;
	    
	    $cookieUser = $_COOKIE['bayou_user'];
	    $cookiePw = $_COOKIE['bayou_pw'];
	    $ip = Helper::getIp();
	    
	    $aCookiePw = explode("~~~", base64_decode($_COOKIE['bayou_pw']));
	    if ($aCookiePw === false || empty($aCookiePw) || count($aCookiePw) != 2)
	    throw new exceptionBase("Bad format cookie requested from ip '$ip'",500);
	    
	    if ($cookieUser !== $aCookiePw[0]) throw new exceptionBase("Bad user cookie requested from ip '$ip'",500);
	    
	    $user = self::getInstance('persistence/user');
	    
	    if (!$user->read_by('login',$cookieUser)) {
		Helper::logInfo("Not user system '$cookieUser' requested from ip '$ip'");
		return false;
	    }
	    if (!$user->enabled) {
		setcookie ("bayou_user", "", time()-3600, '/');
		setcookie ("bayou_pw", "", time()-3600, '/');
		Helper::logInfo("Not enabled system user '".$user->name."' requested from '$ip'");
		return false;
	    }
	    
	    $key = md5 ($user->login . $user->id . $user->rol->name . Bayou::registry('masterkey'));
	    
	    if ($key != $aCookiePw[1]) return false;
	    
	    Bayou::register('currentUser',$user);
	    
	    return true;
	    
	}
	
	public static function getCurrentUser() {
	    
	    return Bayou::registry('currentUser');
	    
	}
	
	public static function getMasterkey() {
	    
	    return Bayou::registry('masterkey');
	    
	}
	
	public static function getMD5($timestamp,$randkey) {
	    
	    $masterKey = Helper::getMasterkey();
	    $currentUser = Helper::getCurrentUser();
	    
	    $md5 = $currentUser->id . $currentUser->login . $currentUser->rol->name . $timestamp . $randkey . $currentUser->nombre . $masterKey;
	    $md5 = md5($md5);
	    
	    return $md5;
	    
	}
	
	public static function validateMD5 ($timestamp, $randkey, $key) {
	    
	    $randkey = intval($randkey);
	    $timestamp = intval($timestamp);
	    
	    return ($key == $this->getMD5($timestamp,$randkey));
	    
	}
	
	public static function getInstanceModule($pathModule) {
	    
	    if (empty($pathModule) && !is_string($pathModule)) throw new Exception("String expected");
	    
	    $nameModule  = str_replace('/','_',$pathModule);
	    $nameModule .= 'Controller';
	    
	    $pathModule = Bayou::registry('modulesDirectory') . Bayou::registry('pathSeparator') . $pathModule . Bayou::registry('pathSeparator') . 'controller';
	    
	    self::requireFile($pathModule);
	    
	    if (!class_exists($nameModule)) throw new exceptionBase("'$nameModule' model definition does not exist in file '$pathModule'",500);
	    
	    $module = new $nameModule();
	    
	    return $module;
	    
	}
	
	public static function getInstance($object) {
	    
	    if (empty($object) && !is_string($object)) throw new Exception("String expected");
	    
	    preg_match('/([a-z0-9_]+)\Z/',$object,$result);
	    $objectName = $result[0];
	    
	    self::requireFile($object);
	    
	    if (!class_exists($objectName)) throw new exceptionBase("'$objectName' object definition does not exist in '$object'",500);
	    
	    $instance = new $objectName();
	    
	    return $instance;
	    
	}
	
	public static function getCanonical($pattern=null) {
	    
	    if (is_null($pattern)) return Bayou::getCanonical();
	    
	    $pattern = (string)$pattern;
	    $lan = Bayou::getLan();
	    
	    $router = new router($lan->codigo . "/$pattern");
	    
	    return $router->getCanonical();
	    
	}
	
	public static function getDir_idb() {
		
		return site_path . Bayou::registry('pathSeparator') . Bayou::registry('idb_dir');
		
	}
	
	/*
	 todo actualizar funcion para conservar parametros en la url
	*/
	public static function getAlternate($pattern=null,language $lan=null) {
	    
	    if (is_null($pattern) && empty($lan)) return Bayou::getCanonical(); //<-- Returns the current pattern
	    
	    if (is_null($pattern)) return Bayou::getAlternate($lan); //<-- Returns the alternate of current pattern
	    
	    // In other cases, it will return the alternate in the current language of the pattern in the default language
	    
	    $currentLan = Bayou::getLan();
	    $defaultLan = self::getDefaultLan();
	    
	    $pattern = (string)$pattern;
	    
	    $router = new router($defaultLan->codigo . '/' . $pattern);
	    
	    return $router->getAlternate($currentLan);
	    
	}
	
	public static function getAlternates($pattern=null) {
	    
	    $lans = self::getAvailableLans();
	    $alternates = array();
	    
	    foreach($lans as $lan) {
		if ($lan->codigo == Bayou::getLan()->codigo) continue;
		$alternates[] = array('language'=>$lan,'url'=>self::getAlternate($pattern,$lan));
	    }
	    
	    return $alternates;
	    
	}
	
	public static function getUrlSite() {
	    
	    return Bayou::getUrlSite();
	    
	}
	
	public static function getAvailableLans() {
	    
	    Helper::requireFile("persistence/language");
	    
	    $search = new language();
	    
	    $lans = $search->getEnabledLans();
	    
	    return $lans;
	    
	}
	
	public static function getDefaultLan() {
	    
	    Helper::requireFile("persistence/language");
	    
	    $defaultLan = new language();
	    
	    $defaultLan->getDefault();
	    
	    return $defaultLan;
	    
	}
	
	public static function loadTranslation($file) {
	    
	    if (empty($file) && !is_string($file)) throw new Exception("String expected");
	    
	    if ($file != 'bayou') $lan = Bayou::getLan();
	    
	    $path  = Bayou::registry('translationDirectory') . Bayou::registry('pathSeparator');
	    if ($file == 'bayou') $path .= 'bayou.csv';
	    else $path .= $lan->codigo . Bayou::registry('pathSeparator') . $file . '.csv';
	    
	    $path = app_path . $path;
	    
	    if (!is_readable($path)) throw new Exception("Translation file '$file' not found");
	    
	    if (($handle = fopen($path, 'r')) === false) throw new Exception("Failed open file operation '$file'");
	    
	    $line = 0;
	    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
		$line++;
		$fields = count($data);
		if ($fields != 2) throw new Exception("Unexpected translation format. Found $fields fields in line $line");
		if (empty($data[0]) || empty($data[1])) throw new Exception("Unexpected empty string values in line $line");
		if (isset(self::$translation[$data[0]])) throw new Exception("Duplicate key translation in line $line");
		self::$translation[$data[0]] = $data[1];
	    }
	    
	}
	
	public static function ___($text) {
	    
	    if (empty($text) || !is_string($text)) throw new Exception("Need not empty string");
	    if (!isset(self::$translation[$text])) throw new Exception("Not key translation define");
	    
	    echo self::$translation[$text];
	    
	}
	
	public static function ___get($text) {
	    
	    if (empty($text) || !is_string($text)) throw new Exception("Need not empty string");
	    if (!isset(self::$translation[$text])) throw new Exception("Not key translation define");
	    
	    return self::$translation[$text];
	    
	}
	
	public static function includeFile ($file,$extension='php') {
	    
	    if (empty($extension) || !is_string($extension)) throw new Exception("String expected.");
	    if (empty($file) || !is_string($file)) throw new Exception("String expected.");
            
            $file = app_path.$file.".$extension";
	    
	    if (is_readable($file)) include_once $file;
	    else throw new Exception("Failed including '$file' file. File doesn't exist.");
            
	}
	
	public static function requireFile ($file,$extension='php') {
	    
	    if (empty($extension) || !is_string($extension)) throw new Exception("String expected.");
	    if (empty($file) || !is_string($file)) throw new Exception("String expected.");
            
            $file = app_path.$file.".$extension";
	    
	    if (!is_readable($file)) throw new Exception("Failed requering '$file' file. File doesn't exist.");
	    
	    try {
		require_once $file;
	    } catch (Exception $e) {
		throw new Exception("Failed requering '$file' file: " . $e->getMessage());
	    }
            
	}
	
	public static function loadObject($path) {
	    
	    //if (empty($extension) || !is_string($extension)) throw new Exception("String expected.");
	    
	    $file = app_path.$path.'.xml';
	    
	    if (!is_readable($file)) throw new Exception("Failed loading object definition file '$file'. File doesn't exist.");
	    
	    $object = new SimpleXMLElement($file,LIBXML_DTDVALID,true);
            
	    return $object;
	    
	}
	
	// public static function getInstance ($class) { }
	
	public static function logInfo($str) {
	    
	    $logInfo = app_path . "var/logs/info.log";
	    
	    // Que hacer si falla la escritura en los logs?
	    $result =  error_log("$str\n", 3, "$logInfo");
	    
	}
	
	public static function logError($str) {
	    
	    $logError = app_path . "var/logs/error.log";
	    
	    // Que hacer si falla la escritura en los logs?
	    error_log("$str\n", 3, "$logError");
	    
	}
	
	public static function dir_exist($path) {
	    
	    if (empty($path) && !is_string($path)) return false;
	    
	    $path = app_path . $path;
	    
	    return is_dir($path);
	    
	}
	
	public static function get_dir_files($path) {
	    
	    if (empty($path) && !is_string($path)) return false;
	    
	    $path = app_path . $path;
	    
	    return scandir($path);
	    
	}
	
	public static function recursive_join ($glue,$pieces) {
	    
	    $retVal = array();
	    foreach ($pieces as $r_pieces) {
		if (is_array($r_pieces)) {
		    $retVal[] = self::recursive_join($glue,$r_pieces);
		} else {
		    if (is_object($r_pieces)) $retVal[] = get_class($r_pieces);
		    else $retVal[] = $r_pieces;
		}
	    }
	    
	    try {
		return join($glue,$retVal);
	    } catch (Exception $e) {
		return "";
	    }
	    
	}
	
	public static function textToHtml($text) {
            
	    return nl2br(htmlentities($text));
            
	}
	
	public static function getFullMonth($monthNumber) {
	    
	    return strftime("%B",strtotime("2009-$monthNumber-1"));
	    
	}
	
	public static function toShortText($text, $long=20) {
            
	    $array = explode(' ',$text);
	    $newText = '';
	    $n = 0;
	    $i = 0;
	    while ( ($i < count($array)) && ($n + strlen($array[$i]) + 4 < $long) ) {
		$newText .= ' ' . $array[$i];
		$n = strlen($newText);
		$i++;
	    }
            
	    if ($i < count($array)) {
		$newText .= ' ...';
	    }
            
	    return $newText;
	    
	}
	
	public static function getIp() {
	    
	    if (getenv('HTTP_X_FORWARDED_FOR')) $ip = getenv('HTTP_X_FORWARDED_FOR');
	    else $ip = getenv('REMOTE_ADDR');
	    
	    return $ip;
	    
	}
	
	public static function getRandom () {
            
	    return rand(1000000,100000000);
            
	}
	
	public static function getRequest($name,$isHTML=false) {
	    
	    if (empty($name)) return null;
	    if (!isset($_GET[$name])) return null;
	    
	    $var = $_GET[$name];
	    if (!$isHTML) $var = self::clean_input_string($var);
	    else $var = self::clean_input_string($var,true);
	    
	    return $var;
	    
	}
	
	public static function postRequest($name,$isHTML=false) {
	    
	    if (empty($name)) return null;
	    if (!isset($_POST[$name])) return null;
	    
	    $var = $_POST[$name];
	    if (!$isHTML) $var = self::clean_input_string($var);
	    
	    return $var;
	    
	}
	
	public static function cookieRequest($name) {
	    
	    if (empty($name)) return $var;
	    if (!isset($_COOKIE[$name])) return null;
	    
	    $var = $_COOKIE[$name];
	    $var = self::clean_input_string($var);
	    
	    return $var;
	    
	}
	
	public static function clean_input_string($string, $html=false) {
	    
	    //return $string;
	    return preg_replace('/[<>\r\n\t\(\)]/', '', stripslashes($string));
	    
	}
	
	
    }
    
?>