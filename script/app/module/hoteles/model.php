<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/model");
    Helper::requireFile('library/DAO/entities');
    
    final class hotelesModel extends model {
        
        public function getHotelesActivos() {
			
			$params = array();
			$conditions = array();
			$order = array();
			
			$params['#1'] = 1;
			$conditions[] = 'AND';
			$conditions[] = "estado = #1";
			$order['nombre'] = 'ASC';
			
			$hotelesActivos = new entities('hotel');
			$hotelesActivos->command($params,$conditions,$order,0);
			
			return $hotelesActivos;
			
		}
        
    }
    
?>