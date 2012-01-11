<?php

    class admin extends JACKEDModule{
		const moduleName = 'admin';
		const moduleVersion = 2.0;
		const dependencies = 'MySQL, Flock, Sessions';
		const optionalDependencies = '';
		
		//Controller for making the admin section do stuff
		
		public function __construct($JACKED){
		    JACKEDModule::__construct($JACKED);
		}
		
	    //get a list of all the installed modules
	    public function getModules(){
	        $modres = $this->JACKED->MySQL->getResult($this->JACKED->config->mod_table, '1');
    		while($row = mysql_fetch_array($modres)){
    			$modules[$row['shortName']]['moduleName'] = $row['name'];
    			$modules[$row['shortName']]['moduleVersion'] = $row['version'];
    			$modules[$row['shortName']]['moduleDescription'] = $row['description'];
    		}
    		return $modules;
	    }
	    
	    //check whether a given module is installed
	    public function isModuleInstalled($shortName){
	        return !($this->JACKED->MySQL->getVal('id', $this->JACKED->config->mod_table, 'shortName = "' . $shortName . '"') === false);
	    }
		
		//Log into JACKED with the given username and password combination
		public function login($username, $password){
    		try{
    		    $this->JACKED->Flock->login($username, $password);
    		}catch(IncorrectPasswordException $e){
    		    return array('reason' => 'Incorrect password.');
    		}catch(UserNotFoundException $e){
    		    return array('reason' => 'User does not exist.');
    		}
    		$id = $this->JACKED->Sessions->read('auth.Flock.userid');
    		
    		$admin = $this->JACKED->MySQL->getVal('id',
    		    $this->config->dbt_users,
    		    'user_id = ' . $id
    		);
    		
        	if($admin){
        		$this->JACKED->Sessions->write("auth.admin", array(
    				'loggedIn' => true,
    				'user'     => $username, 
    				'userid'   => $id,
    				'sessionID' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])
    			));
    		}else{
    		    return array('reason' => 'This user does not have admin privileges.');
    		}
    		
    	}
    	
    	//log out of the current session
    	public function logout(){
    	    $this->JACKED->Sessions->write("auth.admin", array(
				'loggedIn' => false,
				'user'     => NULL, 
				'userid'   => NULL,
				'sessionID' => NULL
			));
			return true;
    	}
    	    	
    	//checkLogin(void) -> boolean
    	//returns logged in status
    	public function checkLogin(){
    		return ($this->JACKED->Sessions->read('auth.admin.loggedIn') && ($this->JACKED->Sessions->read('auth.admin.sessionID') == md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
    	}
    	
    	//requireLogin(void) -> boolean
    	//checks logged in status, returns true if logged in, otherwise:
    	////if $bool is true, returns false, if $bool is false, throws Exception
    	public function requireLogin($bool=false){
    		$answer = true; 
    		if(!$this->checkLogin()){
    		    if($bool){
        			$answer = false;
        		}else{
        		    throw new Exception("User is not logged in.");
        		}
    		}
    		return $answer;
    	}
	}
?>