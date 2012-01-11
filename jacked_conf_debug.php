<?php 
    //delete me
	ini_set('display_errors', 'On');
	error_reporting(E_ALL ^ E_NOTICE);
	//when not working on framework

    //transform Errors into OOP-ish Exceptions
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    set_error_handler("exception_error_handler");

    define('JACKED_MODULES_ROOT', '/Users/pope/Sites/JACKED/');
    define('JACKED_CONFIG_ROOT', '/Users/pope/Sites/JACKED/conf/');
    define('JACKED_LIB_ROOT', '/Users/pope/Sites/JACKED/lib/');
    define('JACKED_SECRET_FILE', '/Users/pope/Sites/mysql.php');
    
    try{
    	include(JACKED_MODULES_ROOT . 'JACKED.php');
    }catch(Exception $e){
        die("<h1>JACKED could not root itself.</h1> <h4>Check your configuration, dude.</h4> <br /> Here's the actual exception: <p><code>" . $e->getMessage() . "</code></p>");
    }
?>