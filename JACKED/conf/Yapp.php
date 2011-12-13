<?php

    $settings = array(
	
	    'db_apps' => 'Application',
	    'db_sources' => 'Source',
	    'db_notifications' => 'Notification',
	    
	    'key_restrict' => true, 
	
	    //array of classes to attach to and provide API interface for
	    'interface_classes' => array('Vitogo', 'Society', 'Lookit'),
	    //at some point this may include either a whitelist or blacklist for
	    ////limiting the methods it can use
	    
	    //these hooks are called before the method of the same name is executed
	    //$JACKED is the current JACKED instance, and $args is the ordered array 
	    //of method arguments, by reference, numerically indexed in order of the args
	    //required by the action method
	    'interface_pre_hooks' => array(
	        "autoLogin" => function($JACKED, &$args){
	            $data = $JACKED->MySQL->getRowVals(
	                'uuid, device_hash, user_id', 
	                'Source', 
	                'id = "' . $JACKED->Yapp->getMySourceID() . '"'
	            );
	            if(array_key_exists('device_hash', $data) && array_key_exists('uuid', $data)){
    	            $newToken = md5(strtolower($data['device_hash']) . $data['uuid']);
    	            $args['autoLoggedIn'] = ($args[0] == $newToken);
    	            $args['user_id'] = $data['user_id'];
    	        }
	        }
	    ),
	    
	    //these hooks are called after the method of the same name is executed
	    //$JACKED is the current JACKED instance, and $args is the return value
	    //of the method, by reference
	    'interface_post_hooks' => array(
	        "login" => function($JACKED, &$args){ 
	            if($args){
    	            $JACKED->Yapp->updateSourceUser($args['userid']);
    	            $args['autoLoginToken'] = md5(session_id() . $args['userid']);
    	        }
	        },
	        "logout" => function($JACKED, &$args){
	            if($args == true){
    	            $JACKED->Yapp->updateSourceUser('');
    	        }
	        }
	    )
	);

?>