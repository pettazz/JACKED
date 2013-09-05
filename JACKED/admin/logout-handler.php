<?php
	$JACKED = new JACKED("admin");
    try{
        $JACKED->admin->logout();
    }catch(Exception $e){
        //so?
    }
    header('Location: ' . $JACKED->admin->config->entry_point);
?>