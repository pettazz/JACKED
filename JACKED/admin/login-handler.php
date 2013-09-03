<?php
    $JACKED = new JACKED("admin");
    $gotime = $JACKED->admin->login($_POST['username'], $_POST['password']);
    if(!($gotime === true)){
        $error = $gotime['reason'];
    }
    header('Location: ' . $JACKED->admin->config->entry_point . ($error? '?error=' . $error : ''));
?>