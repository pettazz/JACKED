<?php

    class admin extends JACKEDModule{
        const moduleName = 'admin';
        const moduleVersion = 2.0;
        public static $dependencies = array('MySQL', 'Flock', 'Sessions');
        
        //Controller for making the admin section do stuff
        
        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);
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
            
            $admin = $this->JACKED->MySQL->get('id',
                $this->config->dbt_users,
                'User = \'' . $id . '\''
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