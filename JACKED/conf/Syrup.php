<?php

    $settings = array(
    
        //whether to automagically register all meta-content types with all content types
        ////if true, every Meta-Content Module loaded on a given page will be registered with
        ////every Content Module, with no explicit registration call.
        'lazy_register_all' => true,

        //location of the model classes, relative to JACKED_MODULES_ROOT
        'model_root' => 'Syrup/models/',

        //location of the driver classes, relative to JACKED_MODULES_ROOT
        'driver_root' => 'Syrup/drivers/',

        //storage engine driver to use
        //// TODO: multiple drivers and/or swapping
        'storage_driver_name' => "MySQL",

        //Driver-specific settings
        'driverConfig' => array(

            //database name
            'db_name' => 'jacked',
            //databae user
            'db_user' => 'jacked',
            //database host
            'db_host' => 'localhost',
            //password is in included file
        )
            
    );

    include(JACKED_SECRET_FILE);

    $settings['driverConfig']['db_pass'] = $db_password;

?>