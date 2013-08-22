<?php

    $settings = array(
	
		//jacked users are just elevated Flock users
	    'dbt_users' => 'admin_users',
	    
	    //the external page that users will access admin through
	    'entry_point' => '/admin/',

	    
	    //Whether to fail on login attempt if the user is already logged in
	    'session_unique' => false,
	    	

	    //image uploader destination directory
	    // this should be more relevant to a templating module if one ever exists
	    'imgupload_directory' => JACKED_SITE_ROOT . 'assets/img/lol/'
	);

?>