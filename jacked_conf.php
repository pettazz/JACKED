<?php 
    //transform Errors into OOP-ish Exceptions
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    set_error_handler("exception_error_handler");

    //define global constants for file locations
    define('JACKED_MODULES_ROOT', '/var/www/jacked-prod/JACKED/');
    define('JACKED_CONFIG_ROOT', '/var/www/jacked-prod/JACKED/conf/');
    define('JACKED_LIB_ROOT', '/var/www/jacked-prod/JACKED/lib/');
    define('JACKED_SECRET_FILE', '/var/www/mysql_jacked.php');

    //load JACKED
    try{
        include(JACKED_MODULES_ROOT . 'JACKED.php');
    }catch(Exception $e){
        die("<h1>JACKED could not root itself.</h1> <h4>Check your configuration, dude.</h4> <br /> Here's the actual exception: <p><code>" . $e->getMessage() . "</code></p>");
    }
?>