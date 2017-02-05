<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/module");
    
    final class hotelesController extends module {
		
		final protected function index() {
			
			$this->viewer()->hotelesActivos = $this->model()->getHotelesActivos();
			
			$this->viewer()->hoteles();
			
		}
		
    }
    
?>