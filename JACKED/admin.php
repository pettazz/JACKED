<?php

    class admin extends JACKEDModule{
		const moduleName = 'admin';
		const moduleVersion = 2.0;
		const dependencies = 'MySQL, Sessions';
		const optionalDependencies = '';
		
		//Controller for making the admin section do stuff
		
		public function __construct($JACKED){
		    JACKEDModule::__construct($JACKED);
		}
		
	    //get a list of all the installed modules
	    public function getModules(){
	        $mods = $this->JACKED->MySQL->getResult($this->JACKED->config->mod_table, '1');
    		while($row = mysql_fetch_array($mods)){
    		
    			$modules[$row['shortName']]['moduleName'] = $row['name'];
    			$modules[$row['shortName']]['moduleVersion'] = $row['version'];
    			$modules[$row['shortName']]['moduleDescription'] = $row['description'];
    		}
    		
    		return $modules;
	    }
		
		//Log into JACKED with the given username and password combination
		public function login($username, $password){
    		$username = $this->JACKED->MySQL->sanitize($username);
    		$password = $this->JACKED->MySQL->sanitize($password);

    		$vals = $this->JACKED->MySQL->getRowVals('id, password', $this->config->dbt_users, "username='$username'");
    		
    		if($vals['password']){
    		    $userID = $vals['id'];
    			
    			if($this->JACKED->checkPassword($password, $vals['password'])){
    				if($this->config->session_unique){
    					if($this->JACKED->MySQL->getVal('id', $this->JACKED->Sessions->config->db_sessions, "data LIKE '%userid|s:1:\"" . $userID . "\"%'")){
    						return array('done' => 'NOEP', 'reason' => 'This user is already logged in.');
    					}
    				}	
    				$this->JACKED->Sessions->write("auth.post", array(
    					'loggedIn' => true,
    					'user'     => $username, 
    					'userid'   => $userID,
    					'sessionID' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])
    				));
    				return true;
    			}else{
    				return array('done' => 'NOEP', 'reason' => 'Incorrect password');
    			}
    		}else{
    			return array('done' => 'NOEP', 'reason' => 'That user does not exist');
    		}
    	}
    	
    	//log out of the current session
    	public function logout(){
    	    $this->JACKED->Sessions->write("auth.post", array(
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
    		return ($this->JACKED->Sessions->read('auth.post.loggedIn') && ($this->JACKED->Sessions->read('auth.post.sessionID') == md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])));
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