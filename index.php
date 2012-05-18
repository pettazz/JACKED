<?php
    $start = microtime(true);
    echo '<html><head><script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script></head><body><pre>';
    
    
    
    //test.php
    
	require('jacked_conf.php');
	$JACKED = new JACKED(array("Yapp", "EYS"));
	$JACKED->EYS->setMark('yapp_call');
    echo $JACKED->Yapp->call();
    $JACKED->EYS->setMark('yapp_call_end');
    $timer = $JACKED->EYS->getDelta('yapp_call', 'yapp_call_end');
    echo '<br /><br />Yapp call took: ' . round(($timer['time'] * 1000), 4) . 'ms and increased allocated memory by ' . round($timer['memory'] / 2048, 4) . ' MB.<br /><br />';
    //end test.php
	
	
	echo '</pre>';
	$runtime = microtime(true) - $start;
	echo '<h4>processed in ' . ($runtime * 1000) . ' ms</h4></body></html>';
?>
