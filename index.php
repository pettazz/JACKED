<?php
    $start = microtime(true);
    echo '<html><head><script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script></head><body><pre>';
    
    
    
    //test.php
    
	require('jacked_conf_debug.php');
	$JACKED = new JACKED("Yapp");
    $JACKED->config->offsetSet('debug', 1);
    echo $JACKED->Yapp->call();

    //end test.php
	
	
	echo '</pre>';
	$runtime = microtime(true) - $start;
	echo '<h4>processed in ' . $runtime . ' seconds</h4></body></html>';
?>
