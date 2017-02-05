<?php
    
    ini_set("display_errors","On");
    ini_set("log_errors","On");
    
    error_reporting(E_ALL);
    
    define ("site_path", realpath(dirname(__FILE__)));
    
    require "script/app/library/init.php";
    
?>