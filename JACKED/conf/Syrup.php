<?php

    $settings = array(
    
        //whether to allow Syrup to attempt to load a Model that has been referened but 
        ////is not currently registered
        'lazy_load_all' => true,

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

            'model_root' => 'Syrup/models/',
        

            // this is a little janky. Define the relations that will be autoloaded, and 
            // aren't defined explicitly in the models
            'auto_relations' => array(
                'Blag' => array(             //list of relations for this model
                    array(                   //this relation means Blag hasMany Curator(s)
                        'type' => 'hasMany', //relation type 
                        'model' => 'Curator' //Model that defines the relationalGet
                    )
                )
            )

        )
            
    );

    include(JACKED_SECRET_FILE);

    $settings['driverConfig']['db_pass'] = $db_password;

?>