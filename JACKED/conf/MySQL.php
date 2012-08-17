<?php

    $settings = array(
        'db_user' => 'jacked',
        'db_name' => 'jacked',
        'db_host' => 'localhost'
    );

    include(JACKED_SECRET_FILE);
    
    $settings['db_pass'] = $db_password;

    $settings['use_memcache'] = false;

?>