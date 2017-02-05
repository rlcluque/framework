<?php

    if (!defined ("BAYOU_CORE")) error_log("Call from no bayou core") and die();
    
    Helper::requireFile("library/exception/base");
    Helper::requireFile("library/exception/validation");

    final class exceptionHandler {
	
	public function __construct() {
	    
	    if (Bayou::registry("debugMode") === true) ini_set("display_errors","1");
	    else ini_set("display_errors","Off");
	    
	    set_exception_handler(array($this,'handler'));
	    set_error_handler(array($this,'errorHandler'),E_ALL);
	    
	}
	
	public static function handler(Exception $e) {
	    
	    $httpError = 500;
	    
	    if ($e instanceof exceptionBase) {
			$httpError = $e->getHttpError();
	    } elseif ($e instanceof exceptionValidation) {
			$e->dieProcess();
	    }
	    
		if ($httpError != 400) {
			$text = self::exceptionToString($e);
			$text = wordwrap($text, 70, "\r\n");
			@mail(Bayou::registry('administrator_mail'), 'Error en la web', $text);
			self::exceptionToLog($e);
		}
		
	    if (Bayou::registry('debugMode') === true) {
			echo self::exceptionToString($e);
	    } else {
			//Bayou::redirectToHTMLError($htmlError);
			//die("Esto es un mensaje de error tipo $httpError");
			//todo resolver esto con p‡ginas de error
			header('Location: '. Bayou::registry('base_url') . Bayou::registry('base_dir'));
            die();
	    }
	    
	}
	
	/* todo este procedimiento tiene que ser del objeto excepcion, no del manejador */
	private static function exceptionToString (Exception $e) {
	    
	    $exception = ucfirst(get_class($e));
	    $message = $e->getMessage();
	    $selfFile = $_SERVER['PHP_SELF'];
	    $time = date("Y/m/d H:i:s",time());
	    
	    $str = "[$time] - Exception '$exception' catched by $selfFile:\n";
	    
	    $traces = $e->getTrace();
	    $strTrace = '';
	    for ($i = count($traces)-1; $i >=0; $i--) {
		if (isset($traces[$i]["file"])) $file = $traces[$i]["file"];
		else $file = "No file";
		if (isset($traces[$i]["line"])) $line = $traces[$i]["line"];
		else $line = "No line";
		if (isset($traces[$i]["function"])) $function = $traces[$i]["function"];
		else $function = "No function";
		if (isset($traces[$i]["args"])) $arguments = "(" . Helper::recursive_join(",",$traces[$i]["args"]) . ")";
		else $arguments = "No arguments";
		$strTrace .= " $file:$line"."->"."$function$arguments\n";
	    }
	    
	    $str .= "$strTrace $message\n";
	    
	    return $str;
	    
	}
	
	private static function exceptionToLog(Exception $e) {
	    
	    $str = self::exceptionToString($e);
	    
	    Helper::logError($str);
	    
	}
	
	public static function errorHandler($errno, $errstr, $errfile, $errline) {
	    
	    $str = "Error #$errno: '$errstr' in file $errfile:$errline";
	    
	    switch ($errno) {
		
		/* Run-time warnings (non-fatal errors).
		* Execution of the script is not halted.
		*/
		case E_WARNING:
		    throw new Exception ($str);
		break;
		
		/* Run-time notices.
		* Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.
		*/
		case E_NOTICE:
		    throw new Exception ($str);
		break;
		
		/* User-generated error message.
		* This is like an E_ERROR, except it is generated in PHP code by
		* using the PHP function trigger_error().
		*/
		case E_USER_ERROR:
		    throw new Exception ($str);
		break;
		
		/* User-generated warning message.
		* This is like an E_WARNING, except it is generated in PHP code by
		* using the PHP function trigger_error().
		*/
		case E_USER_WARNING:
		    throw new Exception ($str);
		break;
		
		/* User-generated notice message.
		* This is like an E_NOTICE, except it is generated in PHP code by
		* using the PHP function trigger_error().
		*/
		case E_USER_NOTICE:
		    throw new Exception ($str);
		break;
		
		/* Enable to have PHP suggest changes to your code which will ensure the
		* best interoperability and forward compatibility of your code.
		*/
		case E_STRICT:
		    throw new Exception ($str);
		break;
		
		/* Catchable fatal error. It indicates that a probably dangerous
		* error occured, but did not leave the Engine in an unstable state.
		* If the error is not caught by a user defined handle (see also
		* set_error_handler()), the application aborts as it was an E_ERROR.
		*/
		case E_RECOVERABLE_ERROR:
		    throw new Exception ($str);
		    //echo $str;
		break;
		
		/* Run-time notices. Enable this to receive warnings about code that
		* will not work in future versions.
		*/
		case E_DEPRECATED:
		    throw new Exception ($str);
		break;
		
		/* User-generated warning message. This is like an E_DEPRECATED, except it
		* is generated in PHP code by using the PHP function trigger_error().
		*/
		case E_USER_DEPRECATED:
		    throw new Exception ($str);
		break;
		
		default:
		    throw new Exception ($str);
		break;
	    }
	    
	    /* Don't execute PHP internal error handler */
	    //return true;
	    
	}
	
    }
    
?>