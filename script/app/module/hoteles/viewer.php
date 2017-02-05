<?php
    
    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/viewer");
    
    final class hotelesViewer extends viewer { }
    
?>
