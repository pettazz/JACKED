<?php

    $settings = array(
	
	    'dbt_users' => 'User',
	
	    'dbt_apps' => 'Application',
	    'dbt_sources' => 'Source',

	    //what to use as the unique user identifier, for things like login
	    //one of: email, username
	    'user_identifier_field' => 'username',

	    //if no app id is given for a Source, assume this one
	    //(probably the webapp)
	    'default_application' => '53'
	    	
	);

?>