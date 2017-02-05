<?php
    
    if (!defined ("BAYOU_CORE")) error_log("call from not bayou core") and die();
    
    final class router {
        
        private $initialized = false;
        
        private $routes = null;
        private $url = null;
        private $lan = null;
        private $route = null;
        private $module = null;
        private $action = '';
        
        public function __construct ($url=null) {
            
            $this->routes = Helper::loadObject('etc/routes');
            
            if (!empty($url)) $this->setUrl($url);
            
        }
        
        private function setUrl ($url) {
            
            $this->initialized = false;
            
            $this->url = null;
            $this->lan = null;
            $this->route = null;
            $this->module = null;
            $this->action = '';
            
            if (!empty($url)) {
                if (!is_string($url)) throw new exceptionBase("Unexpected type '".strtoupper(gettype($url))."'",500);
                
                $pattern = '/^(\Z|[a-z0-9_-]+(\Z|\/\Z|\/[a-z0-9_-]+\/?)*)\Z/is';
                if (preg_match($pattern,$url)) $this->url = $url;
                else throw new exceptionBase("Bad requested route '$url'",400);
            }
            
            $this->initialized = true;
            $this->setLan();
            $this->setRoute();
            $this->setModule();
            $this->setAction();
            
        }
        
        public function getUrl() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            return $this->url;
            
        }
        
        private function setLan () {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            Helper::requireFile('persistence/language');
            $lanSearch = new language();
            
            $url = $this->url;
            $lan = null;
            
            if (empty($url)) {
                if (!$lanSearch->checkCookie()) {
                    if (!$lanSearch->checkBrowser()) $lanSearch->getDefault();
                }
                $lan = $lanSearch;
            } else {
                $enabledLans = $lanSearch->getEnabledLans();
                if (empty($enabledLans)) throw new exceptionBase("No language enabled",500);
                foreach($enabledLans as $enabledLan) {
                    $codeLan = $enabledLan->codigo;
                    $pattern = '/^'.$codeLan.'(\Z|\/\Z|\/[a-z0-9_-]+(\Z|\/\Z|\/[a-z0-9_-]+\/?)*)\Z/is';
                    if (preg_match($pattern,$url)) {
                        $lan = $enabledLan;
                        break;
                    }
                }
            }
            
            if (empty($lan)) throw new exceptionBase("Unexpected 'lan' value in url '$url'",400);
            $this->lan = $lan;
			
        }
        
        public function getLan() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            return $this->lan;
            
        }
        
        private function setRoute() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $url = $this->url;
            $lan = $this->lan;
            
            $this->route = null;
            $this->module = null;
            $this->action = '';
            
            // If empty url, load index module
            if (empty($url)) {
                $this->route = $this->routes->index;
                return true;
            }
            
            // If there's only lan in url, load index mudule
            if (!empty($lan)) {
                $pattern = '/^'.$lan->codigo.'(\Z|\/\Z)/is';
                if (preg_match($pattern,$url)) {
                    $this->route = $this->routes->index;
                    return true;
                }
            }
            
            $routes = $this->routes->route;
            $search = array();
            foreach ($routes as $route) {
                $routeLan = null;
                foreach ($route->pattern as $rpattern) {
                    if ($rpattern['lan'] == 'all' || $rpattern['lan'] == $lan->codigo) {
                        $routeLan = $rpattern;
                        break;
                    }
                }
                if (is_null($routeLan)) continue; // <-- No pattern for this language
                $pattern = $this->buildRoutePattern($routeLan['value']);
                if (preg_match($pattern,$url)) {
                    if (empty($search)) $search = array('route'=>$route,'routeLan'=>$routeLan);
                    else if (strlen($routeLan['value']) > strlen($search['routeLan']['value'])) $search = array('route'=>$route,'routeLan'=>$routeLan);
                }
            }
            
            if (empty($search)) throw new exceptionBase("Bad requested route '$url'",400);
            
            $this->route = $search['route'];
            
            return true;
            
        }
        
        public function getRoute() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            //return $this->route['module'];
            
            return $this->getRoutePattern();
            
        }
        
        private function setModule() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $module = (string)$this->route['module'];
	    
            $modulePath  = Bayou::registry('modulesDirectory') . Bayou::registry('pathSeparator');
            $modulePath .= str_replace('_',Bayou::registry('pathSeparator'),$module) . Bayou::registry('pathSeparator');
            
            $controllerFile = $modulePath . 'controller';
            $controllerName = $module.'Controller';
	    
            Helper::requireFile($controllerFile);
            
            if (!class_exists($controllerName)) throw new exceptionBase("'$controllerName' controller definition does not exist in file '$controllerFile'",500);
            
            $this->module = new $controllerName();
            
            if (!($this->module instanceof module)) throw new exceptionBase("Unexpected class in '$controllerName' controller definition. 'Module' class needed.",500);
            
        }
        
        public function getModule() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            if (!isset($this->module)) return null;
            
            //return get_class($this->module);
            return $this->route['module'];
            
        }
        
        private function setAction () {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $this->action = '';
            
            $url = $this->url;
            $lan = $this->lan;
            $route = $this->route;
            $routePattern = $this->getRoutePattern();
            
            if ($route->getName() != 'index') {
                $pattern = '/^'.$lan->codigo.'\/'.str_replace('/','\/',$routePattern).'(\/)?/is';
                $this->action = preg_replace($pattern,'',$url);
                $pattern = '/\/\Z/is';
                $this->action = preg_replace($pattern,'',$this->action);
            }
            
            if (!$this->module->match($this->action,$lan)) throw new exceptionBase("Bad requested action '".$this->action."'",400);
            
            return true;
            
        }
        
        public function getAction() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            return $this->module->getAction();
            
        }
        
        public function getActionPattern() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            return $this->module->getAction();
            
        }
        
        public function readRouteRequested() {
            
            $url = Helper::getRequest('nav');
            
            $this->setUrl($url);
            
        }
        
        public function dispatch() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $authentication = (string)$this->route['authentication'];
            $actionPattern = $this->module->getActionPattern();
            
            if ($authentication != 'none') {
                if (!Helper::user_logged()) {
                    $authenticationPattern = Bayou::registry($authentication.'_login_pattern');
                    $router = new router($this->getLan()->codigo.'/'.$authenticationPattern);
                    $params = array();
                    $params['refer'] = $this->getUri();
                    $router->redirect($params);
                }
            }
            
            $this->lan->setCookie();
            $this->module->dispatch();
            
        }
        
        public function redirect(array $params=array(),$type=null) {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $url = $this->getUrlSite();
            $rewriteMode = Bayou::registry('rewriteMode');
            
            $nav = $this->url;
            $aParams = array();
            $sParams = '';
            if (!empty($params)) {
                $keys = array_keys($params);
                foreach ($keys as $key) {
                    $aParams[] = "$key=".urlencode($params[$key]);
                }
                $sParams = join('&',$aParams);
                if ($rewriteMode) $nav .= "/?$sParams";
                else $nav = "?nav=$nav&$sParams";
            }
            
            $url .= $nav;
            
			try {
				ob_end_clean();
			} catch (Exception $e) {
				
			}
            
            switch ($type) {
                
                case 301:
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: '.$url);
                    die();
                break;
                
                default:
                    header('Location: '.$url);
                    die();
                break;
            }
            
            die();
            
        }
        
        public function getUri() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $action = $this->action;
            $routePattern = $this->getRoutePattern();
            
            $uri = $routePattern;
            if (empty($uri)) $uri = $action;
            else $uri .= '/' . $action;
            
            return $uri;
            
        }
        
        public function getUrlSite() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $base_url = Bayou::registry('base_url');
            $base_dir = Bayou::registry('base_dir');
            
            $urlSite = $base_url . $base_dir;
            
            return $urlSite;
            
        }
        
        public function getCanonical() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            return $this->getAlternate($this->lan);
            
        }
        
        public function getAlternate(language $lan) {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            if (empty($lan)) throw new exceptionBase("Empty language",500);
            
            $rewriteMode = Bayou::registry('rewriteMode');
            $route = $this->route;
            
            $routeAlternate = null;
            $moduleAlternate = null;
            $patterns = $route->pattern;
            if ($route->getName() == 'index') {
                $routeAlternate = '';
                $moduleAlternate = '';
            } else {
                foreach ($patterns as $rpattern) {
                    if ($rpattern['lan'] == 'all' || $rpattern['lan'] == $lan->codigo) {
                        $routeAlternate = $rpattern['value'];
                        $moduleAlternate = $this->module->getAlternate($lan);
                        break;
                    }
                }
            }
            if (is_null($routeAlternate)) throw new exceptionBase("No pattern for language ". $lan->codigo ." in route ".(string)$route['module'],500);
            
            $alternate = $this->getUrlSite();
            
            if ($rewriteMode) $alternate .= $lan->codigo . '/';
            else $alternate .= '?nav=' . $lan->codigo . '/';
            
            if (!empty($routeAlternate)) $alternate .= "$routeAlternate/";
            if (!empty($moduleAlternate)) $alternate .= "$moduleAlternate/";
            
            return $alternate;
            
        }
        
        private function buildRoutePattern(SimpleXMLElement $routeLan) {
            
            $lan = $this->lan;
            $pattern = '';
            
            $pattern = str_replace('/','\/',$routeLan);
            
            if (!empty($lan)) $pattern = $lan->codigo.'\/'.$pattern;
            $pattern = '/^'. $pattern . '(\Z|\/\Z|\/[a-z0-9_-]+\/?)*\Z/is';
            
            return $pattern;
            
        }
        
        private function getRoutePattern() {
            
            if (!$this->initialized) throw new exceptionBase("Uninitialized route",500);
            
            $lan = $this->lan;
            $route = $this->route;
            $pattern = null;
            
            foreach ($route->pattern as $spattern) {
                if ($spattern['lan'] == 'all' || $spattern['lan'] == $lan->codigo) {
                    $pattern = $spattern;
                    break;
                }
            }
            
            return $pattern['value'];
            
        }
        
    }

?>