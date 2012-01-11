<?php

    $settings = array(
	
		//right now jacked users are just elevated Flock users, but someday there will be options
		////for how to handle this.

	    'dbt_users' => 'jacked_users',
	    
	    //the external page that users will access admin through
	    'entry_point' => '/admin/',

	    
	    //Whether to fail on login attempt if the user is already logged in
	    'session_unique' => false
	    	
	);

?>