<?php
    require('../../jacked_conf.php');
	$JACKED = new JACKED("admin");
    try{
        $JACKED->admin->logout();
    }catch(Exception $e){
        //so?
    }
    header('Location: ' . $JACKED->config->base_url . 'admin/');
?>