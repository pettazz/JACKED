<?php

    class Sessions extends JACKEDModule{
		const moduleName = 'Sessions';
		const moduleVersion = 2.0;
		const dependencies = 'MySQL';
        const optionalDependencies = '';
		
        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);
            
        	//start the session
        	$did = session_set_save_handler(
        	    array($this, '_open'), 
        	    array($this, '_close'), 
        	    array($this, '_read'), 
        	    array($this, '_write'), 
        	    array($this, '_destroy'), 
        	    array($this, '_clean')
        	);
        	session_start();
    	}
    	
    	public function __destruct(){
    	    session_write_close();
    	}
    	
    	//session handler functions
    	
		//session open function
    	private function _open(){
    		$old = time() - $this->config->session_expiry;
    		$this->JACKED->MySQL->delete($this->config->db_sessions, "access < '$old'");
    		return true;
    	}
    	
    	//session read function
    	private function _read($id){
    		return $this->JACKED->MySQL->getVal('data', $this->config->db_sessions, "id = '$id'");
    	}
    	
    	//session destroy function
    	private function _destroy($id){
    	    return $this->JACKED->MySQL->delete(
    	        $this->config->db_sessions,
    	        "id = '$id'"
    	    );
    	}
    	
    	//session clean function
    	private function _clean($max){
    		$old = time() - $max;
    		return $this->JACKED->MySQL->delete(
    	        $this->config->db_sessions,
    	        "access < '$old'"
    		);
    	}
    	
    	//_write and _close are called after object destruction
    	
    	//session write function
    	public function _write($id, $data){
    	    return $this->JACKED->MySQL->replace(
    	        $this->config->db_sessions,
    	        array('id' => $id, 'data' => $data, 'access' => time())
    	    );
    	}
    	
    	//session close function
    	public function _close(){
    		return true;
    	}
    	
    	
    	//public JACKED sessions functions
    	
    	//read a session value!
    	public function read($key){
    	    $keys = explode(".", $key);
        	$session =& $_SESSION;
        
        	foreach ($keys as $e) {
        		if (!isset($session[$e]) || empty($session[$e]))
        			return false;
        
        		if(is_array($session))
            		$session =& $session[$e];
        	}
    	    
    	    return $session;
    	}
    	
    	//write a session value!
    	public function write($key, $value){
    	    $keys = explode(".", $key);
        	$session =& $_SESSION;
        
        	foreach ($keys as $e) {
        		if (!isset($session[$e]) || empty($session[$e]))
        			$session[$e] = array();
        
        		if(is_array($session))
            		$session =& $session[$e];
        	}
    	    
    	    return $session = $value;
    	}
    	
    	//delete a session value!
    	public function delete($key){
	        $keys = explode(".", $key);
        	$session =& $_SESSION;
        
        	foreach ($keys as $e) {
        		if (!isset($session[$e]) || empty($session[$e]))
        			return false;
        
        		if(is_array($session))
            		$session =& $session[$e];
        	}
	        
	        $session = NULL;
	    }
	
	    //check if a session value exists!
	    public function check($key){
        	$keys = explode(".", $key);
        	$session =& $_SESSION;
        
        	foreach ($keys as $e) {
        		if (!isset($session[$e]) || empty($session[$e]))
        			return false;
                
                if(is_array($session))
            		$session =& $session[$e];
        	}
        
        	return true;
	    }
	
	    //check if a session id exists!
	    function idExists($sid){
            return $this->_read($sid) ? true : false;
	    }
    }

?>
