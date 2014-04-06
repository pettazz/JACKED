<?php
    $JACKED = new JACKED("admin");
    $gotime = $JACKED->admin->login($_POST['username'], $_POST['password']);
    if(!($gotime === true)){
        $JACKED->Sessions->write('admin.loginform.error', $gotime['reason']);
    }
    $uri = isset($_POST['qs'])? $_POST['qs'] : $JACKED->admin->config->entry_point;

    header('Location: ' . $uri);
?>