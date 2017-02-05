<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/controller");
    
    abstract class module extends controller {
	
	private $api = null;
	
	private $action = null;
	private $pattern = null;
	private $params = null;
	
	private $matched = false;
	
	public function __construct() {
	    
	    parent::__construct();
	    
	    $this->loadApi();
	    
	}
	
	final public function getAction() {
	    
	    if (!$this->matched) throw new exceptionBase("Not yet matched.",500);
	    
	    return $this->action['name'];
	    
	}
	
	final public function getActionPattern() {
	    
	    if (!$this->matched) throw new exceptionBase("Not yet matched.",500);
	    
	    return (string)$this->pattern;
	    
	}
	
	final protected function getParam($name) {
	    
	    if (!$this->matched) throw new exceptionBase("Not yet matched.",500);
	    
	    if (!is_string($name) || empty($name)) throw new exceptionBase("Expected not empty string",500);
	    
	    if (empty($this->params)) return null;
	    if (!array_key_exists($name,$this->params)) return null;
	    
	    return $this->params[$name];
	    
	}
	
	final protected function getCountParams() {
	    
	    if (!$this->matched) throw new exceptionBase("Not yet matched.",500);
	    
	    return count($this->params);
	    
	}
	
	final public function dispatch() {
	    
	    if (!$this->matched) throw new exceptionBase("Not yet matched.",500);
	    
		if (Bayou::getLan()->codigo == 'ca') setlocale(LC_ALL, 'ca_ES.UTF-8', 'Catalan_Spain', 'Catalan');
		else setlocale(LC_ALL, Bayou::getLan()->locale.'.UTF-8');
		
	    $this->loadLocale();
	    
	    call_user_func(array($this,(string)$this->action['name']));
	    
	}
	
	final public function getAlternate(language $lan) {
	    
	    if (!$this->matched) throw new exceptionBase("Not yet matched.",500);
	    
	    $alternate = null;
	    $action = $this->action;
	    foreach ($action->patterns->pattern as $mpattern) {
		if ($mpattern['lan'] == $lan->codigo || $mpattern['lan'] == 'all') {
		    $alternate = (string)$mpattern;
		}
	    }
	    
	    if (is_null($alternate)) throw new exceptionBase("No pattern for language ". $lan->codigo ." in procedure ".(string)$action['name'],500);
	    
	    return $alternate;
	    
	}
	
	final public function match ($url,language $lan) {
	    
	    if (!is_string($url)) throw new exceptionBase("String expected",500);
	    
	    $this->matched = false;
	    $this->action = null;
	    $this->pattern = null;
	    $this->params = null;
	    
	    $action = null;
	    $pattern = null;
	    $procedures = $this->api->procedures->procedure;
	    foreach ($procedures as $procedure) {
			
			$paramCount = 0;
			$paramStrict = null;
			if (isset($procedure->params)) {
				$paramCount = count($procedure->params->children());
				$paramStrict = (string)$procedure->params['strict'];
			}
			$patterns = $procedure->patterns->pattern;
			
			foreach ($patterns as $mpattern) {
				
				if ($mpattern['lan'] != $lan->codigo && $mpattern['lan'] != 'all') continue;
				$spattern = (string)$mpattern;
				$search = str_replace('/','\/',$spattern);
				if ($paramStrict === 'yes') $paramRepetition = (string)$paramCount;
				else $paramRepetition = "0,".(string)$paramCount;
				
				if (empty($spattern)) $suffix = '([a-z0-9_-]+\/?){'.$paramRepetition.'}(\Z|\/\Z)/is';
				else $suffix = '(\/[a-z0-9_-]+\/?){'.$paramRepetition.'}(\Z|\/\Z)/is';
				
				$search = '/^'.$search.$suffix;
				if (preg_match($search,$url) && strlen($spattern) >= strlen((string)$pattern)) {
					$pattern = $mpattern;
					$action = $procedure;
				}
			}
	    }
		
	    if (empty($action)) return false;
	    
	    $this->action = $action;
	    $this->pattern = $pattern;
	    
		
	    if (isset($action->params)) {
			$pattern = str_replace('/','\/',(string)$this->pattern);
			$pattern = '/^'.$pattern.'(\/)?/is';
			$sValueParams = preg_replace($pattern,'',$url);
			$aValueParams = explode('/',$sValueParams);
			$aValueParams = array_filter($aValueParams);
			$i = 0;
			foreach ($action->params->param as $param) {
				if (count($aValueParams) > $i) $this->params[(string)$param['name']] = Helper::clean_input_string($aValueParams[$i]);
				else $this->params[(string)$param['name']] = '';
				$i++;
			}
	    }
		
	    
	    $this->matched = true;
	    
	    return true;
	    
	}
	
	final private function loadApi() {
	    
	    $me = $this->getName();
	    $modules = Helper::loadObject('etc/modules')->module;
            $api = null;
	    
            foreach ($modules as $module) {
		if ($module['name'] == $me) {
		    $api = $module;
		    break;
		}
	    }
	    
	    if (empty($api)) throw new exceptionBase("Api not defined in 'etc/modules.xml' for module '$me'",500);
	    
	    $methods = array();
	    $procedures = $api->procedures->procedure;
	    foreach ($procedures as $procedure) {
		$name = $procedure['name'];
		if (!in_array($name,get_class_methods($this))) throw new exceptionBase("No implementation of procedure '$name' in module '".get_class($this)."'",500);
		if (in_array($name,$methods)) throw new exceptionBase("Duplicated declaration of procedure '$name' in module '$me' at 'etc/modules.xml'.",500);
		$methods[] = $name;
	    }
	    
	    $this->api = $api;
	    
	}
	
    }
    
?>