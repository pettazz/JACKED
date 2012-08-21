<?php
    
    require('jacked_conf.php');
    $JACKED = new JACKED("Syrup");
    $JACKED->Syrup->registerModule('Blag');

    echo $JACKED->Syrup->Blag->count(array('alive' => '1')) . " blog posts exist.";

?>