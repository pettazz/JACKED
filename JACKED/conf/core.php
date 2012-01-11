<?php

	$settings = array(
	
	    'client_name' => 'JACKED',
	    'email_url' => '@jackedmanager.com',
	    
	    'base_url' => 'http://jackedmanager.com/',
	    
	    'remote_addr' => preg_match('/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/', $_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "127.0.0.1",
	    
	    'mod_table' => 'jacked_modules',
	    
	    
	    //for a deprecated function
	    'salt_length' => 17,
	    
	    'apikey_google_anal' => 'UA-derp-1',
	    
	    'stopwords' => 'I, a, about, an, are, as, at, be, by, com, de, en, for, from, how, in, is, it, la, of, on, or, that, the, this, to, was, what, when, where, who, why, will, with, und, the, www',
	    
	    'debug' => 0
	
	);

?>
