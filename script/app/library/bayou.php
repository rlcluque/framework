<?php

    if (!defined ("BAYOU_CORE")) error_log ("Call from no bayou core") and die();

    require_once app_path . "library/helper.php";
    require_once app_path . "library/message.php";
    
    final class Bayou {
        
        private static $registry = null;
		private static $router = null;
		private static $dbConnection = null;
        
        public static function start () {
	    
			Helper::requireFile("library/exception/handler");
			
			Helper::requireFile("library/registry");
			
			Helper::requireFile("library/dbConnection");
			Helper::requireFile("library/router");
			
			// Registry start
			self::$registry = new registry();
			
			// Exception Handler start
			$exceptionHandler = new exceptionHandler();
			
			// Load system messages
			Helper::loadTranslation('bayou');
			
			// Open default data base connection
			self::$dbConnection = array();
			$defaultDBConnection = self::registry('defaultDBConnection');
			self::$dbConnection[$defaultDBConnection] = new dbConnection($defaultDBConnection);
			
			/*
			Open more data base connections in next lines
			$sourceName = 'secondConnection';
			self::$dbConnection[$sourceName] = new dbConnection($sourceName);
			*/
			
			// System router start
			self::$router = new router();
	    
        }
	
	public static function routeRequest () {
		
		if (empty(self::$registry) || empty(self::$router) || empty(self::$dbConnection)) throw new Exception ("Bayou not yet started");
	    
	    self::$router->readRouteRequested();
	    self::$router->dispatch();
	    
	}
	
	public static function register ($index,$value) {
	    
	    if (!isset(self::$registry)) throw new Exception ("Not defined yet Bayou registry");
	    
	    self::$registry->register($index,$value);
	    
	}
	
	public static function registry ($index) {
	    
	    if (!isset(self::$registry)) throw new Exception ("Not defined yet Bayou registry");
	    
	    return self::$registry->registry($index);
	    
	}
	
	public static function dbConnection ($source=null) {
	    
	    if (!isset(self::$dbConnection)) throw new Exception ("Not defined yet Bayou data base connections");
	    
	    if (empty($source)) $source = self::registry('defaultDBConnection');
	    else if (!is_string($source)) throw new Exception('String expected');
	    
	    if (array_key_exists($source,self::$dbConnection)) return self::$dbConnection[$source];
	    else return null;
	    
	}
	
	public static function getLan () {
	    
	    if (!isset(self::$router)) throw new Exception ("Not defined yet Bayou router");
	    
	    return self::$router->getLan();
	    
	}
	
	public static function getCurrentLan () {
	    
	    if (!isset(self::$router)) throw new Exception ("Not defined yet Bayou router");
	    
	    return self::$router->getLan();
	    
	}
	
	public static function getCurrentRouteModule() {
	    
	    if (!isset(self::$router)) throw new Exception ("Not defined yet Bayou router");
	    
	    return self::$router->getModule();
	    
	}
	
	public static function getCurrentRoutePattern() {
	    
	    if (!isset(self::$router)) throw new Exception ("Not defined yet Bayou router");
	    
	    return self::$router->getRoute();
	    
	}
	
	public static function getCurrentRouteAction() {
	    
	    if (!isset(self::$router)) throw new Exception ("Not defined yet Bayou router");
	    
	    return self::$router->getAction();
	    
	}
	
	public static function getCurrentRouteActionPattern() {
	    
	    if (!isset(self::$router)) throw new Exception ("Not defined yet Bayou router");
	    
	    return self::$router->getActionPattern();
	    
	}
	
	public static function getCurrentUri() {
	    
	    if (!isset(self::$router)) throw new Exception ("Not defined yet Bayou router");
	    
	    return self::$router->getUri();
	    
	}
	
	public static function getCanonical() {
	    
	    if (empty(self::$registry) || empty(self::$router) || empty(self::$dbConnection)) throw new Exception ("Bayou not yet started");
	    
	    return self::$router->getCanonical();
	    
	}
	
	public static function getAlternate(language $lan) {
	    
	    if (empty(self::$registry) || empty(self::$router) || empty(self::$dbConnection)) throw new Exception ("Bayou not yet started");
	    
	    if (empty($lan )) return self::$router->getCanonical();
	    
	    return self::$router->getAlternate($lan);
	    
	}
	
	public static function getUrlSite() {
	    
	    if (empty(self::$registry) || empty(self::$router) || empty(self::$dbConnection)) throw new Exception ("Bayou not yet started");
	    
	    return self::$router->getUrlSite();
	    
	}
	
    }
    
?>