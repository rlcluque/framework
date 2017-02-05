<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    abstract class controller {
	
	private $path = null;
	
	private $name = null;
	private $model = null;
	private $viewer = null;
	
	public function __construct() {
	    
	    $controller = get_class($this);
	    
	    $pattern = '/^[a-zA-Z0-9_]+Controller\Z/is';
            if (!preg_match($pattern,$controller)) throw new exceptionBase("Unexpected controller class name '$controller'",500);
	    $this->name = str_replace('Controller','',$controller);
	    
	    $this->setPath();
	    
	    $this->setModel();
	    $this->setViewer();
	    
	}
	
	final protected function path() {
	    
	    return $this->path;
	    
	}
	
	final protected function model() {
	    
	    return $this->model;
	    
	}
	
	final protected function viewer() {
	    
	    return $this->viewer;
	    
	}
	
	final protected function getName() {
	    
	    return $this->name;
	    
	}
	
	final protected function loadLocale() {
	    
	    $translation = strtolower(str_replace(Bayou::registry('pathSeparator'),'_',$this->path()));
	    Helper::loadTranslation($translation);
	    
		/* todo Este no es el mejor sitio para esto: */
	    $translation = 'html';
	    Helper::loadTranslation($translation);
	    
	}
	
	final private function setPath() {
	    
	    $controller = $this->name;
	    
	    $path = str_replace('_',Bayou::registry('pathSeparator'),$controller);
	    $path = Bayou::registry('modulesDirectory') . Bayou::registry('pathSeparator') . $path;
	    
	    $this->path = $path;
	    
	}
	
	final private function setModel() {
	    
	    $pathModel  = $this->path . Bayou::registry('pathSeparator') . 'model';
	    $nameModel  = $this->name . 'Model';
	    
	    Helper::requireFile($pathModel);
	    
	    if (!class_exists($nameModel)) throw new exceptionBase("'$nameModel' model definition does not exist in file '$pathModel'",500);
	    
	    $this->model = new $nameModel();
	    
	}
	
	final private function setViewer() {
	    
	    $pathViewer  = $this->path . Bayou::registry('pathSeparator') . 'viewer';
	    $nameViewer  = $this->name . 'Viewer';
	    
	    Helper::requireFile($pathViewer);
	    
	    if (!class_exists($nameViewer)) throw new exceptionBase("'$nameViewer' model definition does not exist in file '$pathViewer'",500);
	    
	    $this->viewer = new $nameViewer($this->path);
	    
	}
	
    }
    
?>