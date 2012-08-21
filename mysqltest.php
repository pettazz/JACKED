<?php
    $start = microtime(true);
    echo '<html><head><script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script></head><body><pre>';
    
    
    
    //mysqltest.php
    
    require('jacked_conf.php');
    $JACKED = new JACKED(array("MySQL", "Blag", "EYS"));
    $JACKED->EYS->setMark('count_start');
    echo $JACKED->MySQL->query('SELECT COUNT(*) FROM ' . $JACKED->Blag->config->dbt_posts . ' WHERE alive = 1') . " blog posts exist.";
    $JACKED->EYS->setMark('count_stop');
    $timer = $JACKED->EYS->getDelta('count_start', 'count_stop');
    echo '<br /><br />Count took: ' . round(($timer['time'] * 1000), 4) . 'ms and increased allocated memory by ' . round($timer['memory'] / 2048, 4) . ' MB.<br /><br />';
    //end syruptest.php
    
    
    echo '</pre>';
    $runtime = microtime(true) - $start;
    echo '<h4>processed in ' . ($runtime * 1000) . ' ms</h4></body></html>';

?>