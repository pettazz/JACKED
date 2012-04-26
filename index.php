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
    echo '<br /><br />Yapp call start: ' . print_r($JACKED->EYS->getMark('yapp_call'), true) . '<br /><br />';
    echo '<br /><br />Yapp call end: ' . print_r($JACKED->EYS->getMark('yapp_call_end'), true) . '<br /><br />';
    echo '<br /><br />Yapp call took: ' . print_r($timer, true) . '<br /><br />';
    //end test.php
	
	
	echo '</pre>';
	$runtime = microtime(true) - $start;
	echo '<h4>processed in ' . $runtime . ' seconds</h4></body></html>';
?>
