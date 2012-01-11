<?php
    require('../../jacked_conf.php');
	$JACKED = new JACKED("admin");
	$gotime = $JACKED->admin->login($_POST['username'], $_POST['password']);
    if(!($gotime === true)){
        $error = $gotime['reason'];
    }
    header('Location: ' . $JACKED->config->base_url . 'admin/' . ($error? '?error=' . $error : ''));
?>