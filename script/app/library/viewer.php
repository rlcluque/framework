<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    abstract class viewer {
	
	private $viewer = null;
	private $controller = null;
	
	private $path = null;
	
	private $vars = array();
	private $views = array();
	
	/* todo Hay que eliminar esta propiedad y crear un objeto Block independiente con su MVC */
	protected $pathBlocks = 'front';
	private $blocks = array();
	
	private $viewFileType = '.phtml';
	
        public function __construct($pathController) {
	    
	    if (empty($pathController) || !is_string($pathController)) throw new exceptionBase('Not empty string expected',500);
	    
	    $viewer = get_class($this);
	    $pattern = '/^[a-zA-Z0-9_]+Viewer\Z/is';
            if (!preg_match($pattern,$viewer)) throw new exceptionBase("Unexpected viewer class name '$viewer'",500);
	    
	    $this->viewer = $viewer;
	    $this->controller = $pathController;
	    
	    $path  = Bayou::registry('viewerDirectory');
	    $path .= Bayou::registry('pathSeparator');
	    $path .= Bayou::registry('viewer');
	    $path .= Bayou::registry('pathSeparator');
	    
	    $pathViewer  = $path . $pathController;
	    $pathViewer .= Bayou::registry('pathSeparator');
	    $pathBlocks  = $path . 'blocks' . Bayou::registry('pathSeparator') . $this->pathBlocks;
	    $pathBlocks .= Bayou::registry('pathSeparator');
	    
	    $this->path = $pathViewer;
	    $this->pathBlocks = $pathBlocks;
	    
	    $this->loadViews();
	    $this->loadBlocks();
	    
	}
	
	final public function __set($name,$value) {
	    
	    $this->vars[$name] = $value;
	    
	}
	
	final public function __get($name) {
	    
	    if (array_key_exists($name,$this->vars)) return $this->vars[$name];
	    else return null;
	    
	}
	
	final public function __call($name,$arguments) {
	    
	    if (!in_array($name,$this->views)) throw new exceptionBase("'$name' view does not exist for controller '".$this->controller."'",500);
	    
	    $phtml = app_path . $this->path . Bayou::registry('pathSeparator') . $name . '.phtml';
	    
	    if (is_readable($phtml)) {
			$this->loadCommonVars();
			if (is_null($this->__get('alternates'))) $this->__set('alternates',Helper::getAlternates());
			require_once $phtml;
	    } else {
			throw new Exception("Failed requering view '$phtml'.File doesn't exist.");
	    }
	    
	}
	
	final protected function getController() {
	    
	    return $this->controller;
	    
	}
	
	final protected function block($block) {
	    
	    if (!in_array($block,$this->blocks)) throw new exceptionBase("Block '$block' does not exist for viewer '".$this->viewer."'",500);
	    
	    $phtml = app_path . $this->pathBlocks  . $block . '.phtml';
	    
	    if (is_readable($phtml)) require_once $phtml;
	    else throw new Exception("Failed requering view '$phtml'.File doesn't exist.");
	    
	}
	
	final protected function plugin($plugin) {
	    
	    Helper::requireFile("plugin/$plugin");
	    
	    $object = new $plugin();
	    
	    echo $object;
	    
	}
	
	final protected function loadCommonVars() {
	    
	    $this->vars['codeLan'] = Bayou::getLan()->codigo;
	    $this->vars['code'] = Bayou::registry('codeSite');
	    $this->vars['copyright'] = Bayou::registry('copyright');
	    $this->vars['author'] = Bayou::registry('author');
	    $this->vars['codeSite'] = Bayou::registry('codeSite');
	    
	    $this->vars['currentLan'] = Bayou::getCurrentLan();
	    $this->vars['currentRoute'] = Bayou::getCurrentRouteModule();
	    $this->vars['currentRoutePattern'] = Bayou::getCurrentRoutePattern();
	    $this->vars['currentAction'] = Bayou::getCurrentRouteAction();
	    $this->vars['currentActionPattern'] = Bayou::getCurrentRouteActionPattern();
	    $this->vars['currentUri'] = Bayou::getCurrentUri();
		
	}
	
	final protected function getViewerSkinUrl() {
	    
	    return Bayou::registry('base_url') . Bayou::registry('base_dir') . Bayou::registry('skin_dir') . Bayou::registry('viewer') . '/';
	    
	}
	
	final protected function getPluginsUrl() {
	    
	    return Bayou::registry('base_url') . Bayou::registry('base_dir') . Bayou::registry('plugin_dir');
	    
	}
	
	final protected function getVarIdb() {
	    
	    return Bayou::registry('base_url') . Bayou::registry('base_dir') . Bayou::registry('idb_dir');
	    
	}
	
	final private function loadViews() {
	    
	    $path = $this->path;
	    $controller = $this->controller;
	    
	    if (!Helper::dir_exist($path)) throw new exceptionBase("Not path viewer for '$controller' controller",500);
	    
	    if (!$files = Helper::get_dir_files($path)) return true;
	    
	    $files = array_filter($files,array($this,'is_view_file'));
	    $files = str_replace($this->viewFileType,'',$files);
	    
	    $this->views = $files;
	    
	}
	
	final private function loadBlocks() {
	    
	    $path = $this->pathBlocks;
	    
	    if (!Helper::dir_exist($path)) throw new exceptionBase("Path 'blocks' of viewer '".Bayou::registry('viewerDirectory')."' does not exist.",500);
	    
	    if (!$files = Helper::get_dir_files($path)) return true;
	    
	    $files = array_filter($files,array($this,'is_view_file'));
	    $files = str_replace($this->viewFileType,'',$files);
	    
	    $this->blocks = $files;
	    
	}
	
	final private function get_view_files($files) {
	    
	    $files = array_filter($files,array($this,"is_view_file"));
	    
	    $availabledFiles = $this->availabledFiles;
	    foreach($availabledFiles as $availabledFile){
		$files = str_replace(".$availabledFile",'',$files);
	    }
	    
	    return $files;
	    
	}
	
	final private function is_view_file($file) {
	    
	    return preg_match('/^[a-zA-Z0-9_]+.'.$this->viewFileType.'\Z/is',$file);
	    
	}
	
    }
    
?>