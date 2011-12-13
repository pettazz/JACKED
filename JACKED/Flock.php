<?php

    class Flock extends JACKEDModule{
		const moduleName = 'Flock';
		const moduleVersion = 1.0;
		const dependencies = 'MySQL, Sessions';
		const optionalDependencies = '';
		
		//Flock provides user management functions
		////manage your flock of sheeple
		
		//login session management
		
        /**
         * Login the given user with the given password.
         * 
         * Uses the auth.Flock Session array
         *
         * @param string $username The username to log in with (usually an email)
         * @param string $password The user's password 
         * @throws UserNotFoundException if the given username does not exist
         * @throws IncorrectPasswordException if the given password does not match the username's login
         * @return boolean Whether the user is now logged in successfully
         */
		public function login($username, $password){
		    $username = $this->JACKED->MySQL->sanitize($username);

    		$vals = $this->JACKED->MySQL->getRowVals('id, password', $this->config->dbt_users, "email='$username'");
    		
    		if($vals['password']){
    		    $userID = $vals['id'];
    			$hash = $this->JACKED->checkPassword($password, $vals['password'], true);
    			if($hash){
    				$this->JACKED->Sessions->write("auth.Flock", array(
    					'loggedIn' => true,
    					'username'     => $username, 
    					'email'     => $username, 
    					'userid'   => $userID,
    					'sessionID' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']),
    					'hash' => $hash
    				));
    				return true;
    			}else{
    				throw new IncorrectPasswordException();
    			}
    		}else{
    			throw new UserNotFoundException();
    		}
		}
		
        /**
         * Login the given user with the given password hash.
         * 
         * Uses the auth.Flock Session array
         *
         * @param string $username The username to log in with (usually an email)
         * @param string $hpassword The user's password hash
         * @throws UserNotFoundException if the given username does not exist
         * @throws IncorrectPasswordException if the given password does not match the username's login
         * @return boolean Whether the user is now logged in successfully
         */
		public function hashedLogin($username, $hpassword){
		    $username = $this->JACKED->MySQL->sanitize($username);

    		$vals = $this->JACKED->MySQL->getRowVals('id, password', $this->config->dbt_users, "email='$username'");
    		
    		if($vals['password']){
    		    $userID = $vals['id'];
    			
    			if($hpassword == $vals['password']){
    				$this->JACKED->Sessions->write("auth.Flock", array(
    					'loggedIn' => true,
    					'username'     => $username, 
    					'email'     => $username, 
    					'userid'   => $userID,
    					'sessionID' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']),
    					'hash' => $hpassword
    				));
    				return true;
    			}else{
    				throw new IncorrectPasswordException();
    			}
    		}else{
    			throw new UserNotFoundException();
    		}
		}
		
        /**
         * Checks the current session to see if a user is logged in
         *
         * @return boolean Whether the user is logged in
         */
		public function checkLogin(){
		    return(
		        $this->JACKED->Sessions->read("auth.Flock.loggedIn") &&
		        $this->JACKED->Sessions->read("auth.Flock.sessionID") == md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])
		    );
		}
		
        /**
         * Verifies that the current session has a user is logged in
         *
         * @throws NotLoggedInException if the user is not logged in
         * @return boolean Whether the user is logged in
         */
		public function requireLogin(){
		    if(!$this->checkLogin())
		        throw new NotLoggedInException();
		        
		    return true;
		}
		
		/**
         * Gets user details for the currently logged in user
         *
         * @throws NotLoggedInException if the user is not logged in
         * @return array Associative array of all the details of the user
         */
		public function getUserSession(){
		    $this->JACKED->Flock->requireLogin();
		    return $this->JACKED->Sessions->read("auth.Flock");
		}
		
		/**
         * Logs out the current user session
         *
         * @throws NotLoggedInException if the user is not logged in
         * @return boolean Whether the logout was successful
         */
		public function logout(){
            if($this->JACKED->Flock->checkLogin()){
    		    $this->JACKED->Sessions->write("auth.Flock", array(
    				'loggedIn' => false,
    				'username'     => NULL, 
    				'userid'   => NULL,
    				'sessionID' => NULL
    			));
    			return true;
    		}else{
    		    throw new NotLoggedInException();
    		}
		}
				
		//user management
		
		/**
         * Creates a new user with the given data
         *
         * @param $username string New username/email
         * @param $password string New password
         * @param $details array Associative array of any other user details
         * @throws ExistingUserException if the username already exists
         * @return boolean Whether the creation was successful
         */
		public function createUser($username, $password, $details=array()){
		    if($this->JACKED->MySQL->getVal('email', $this->config->dbt_users, "email='" . $username. "'")){
		        throw new ExistingUserException();
		    }
		    $details = array_merge($details, array(
		        'email' => $username, 'password' => $this->JACKED->hashPassword($password)
		    ));
		    return $this->JACKED->MySQL->insertValues($this->config->dbt_users, $details);
		}
		
		/**
         * Update an existing user with the given details
         *
         * @param $username string Username to change
         * @param $details array Associative array of user details
         * @throws UserNotFoundException if the username is not found
         * @return boolean Whether the update was successful
         */
		public function updateUser($username, $details=array()){
		    $id = $this->JACKED->MySQL->getVal('id', $this->config->dbt_users,  "email='" . $username. "'");
		    if(!$id){
		        throw new UserNotFoundException();
		    }
		    
		    $details['email'] = $username;
		    return $this->JACKED->MySQL->update($this->config->dbt_users, $details, 'id=' . $id);
		}
		
		/**
         * Update an existing user with the given details
         *
         * @param $userid int User ID to change
         * @param $details array Associative array of user details
         * @throws UserNotFoundException if the user is not found
         * @return boolean Whether the update was successful
         */
		public function updateUserByID($userid, $details=array()){
		    if(!$this->JACKED->MySQL->getVal('id', $this->config->dbt_users, 'id=' . $userid)){
		        throw new UserNotFoundException();
		    }
		    
		    $details['email'] = $username;
		    return $this->JACKED->MySQL->update($this->config->dbt_users, $details, 'id=' . $id);
		}
		
		/**
         * Update an existing user's username with the given details
         *
         * @param $userid int User ID to change
         * @param $username String New username
         * @throws ExistingUserException if the new username already exists
         * @return boolean Whether the update was successful
         */
		public function updateUserEmail($userid, $email){
		   try{
                $this->getUser($email);
                throw new ExistingUserException();
            }catch(UserNotFoundException $e){
                return (
                    $emailDone = $this->JACKED->MySQL->update(
                        $this->config->dbt_users,
                        array(
                            'email' => $email
                        ),
                        'id = ' . $userid
                    ) &&
                    $this->JACKED->Sessions->write("auth.Flock.username", $email) &&
                    $this->JACKED->Sessions->write("auth.Flock.email", $email)
        		);
            } 
		}
		
		/**
         * Update an existing user's username with the given details
         *
         * @param $userid int User ID to change
         * @param $password String New password
         * @return boolean Whether the update was successful
         */
		public function updateUserPassword($userid, $password){
            //this would be a good place to check things like mininum password security stuff
            return $emailDone = $this->JACKED->MySQL->update(
                $this->config->dbt_users,
                array(
                    'password' => $this->JACKED->hashPassword($password)
                ),
                'id = ' . $userid
            );
		}
		
		/**
         * Gets user details for a given username
         *
         * @param $username string Username to get details for
         * @throws UserNotFoundException if the user is not found
         * @return array Associative array of all the details of the user details
         */
		public function getUser($username){
		    $id = $this->JACKED->MySQL->getVal('id', $this->config->dbt_users,  "email='" . $username. "'");
		    if(!$id){
		        throw new UserNotFoundException();
		    }
		    
		    $done = $this->JACKED->MySQL->getRow($this->config->dbt_users, 'id=' . $id);
		    unset($done['password']);
		    return $done;
		}
		
		/**
         * Gets user details for a given user id
         *
         * @param $userid int User ID to get details for
         * @throws UserNotFoundException if the user is not found
         * @return array Associative array of all the details of the user details
         */
		public function getUserByID($userid){
		    if(!$this->JACKED->MySQL->getVal('id', $this->config->dbt_users, 'id=' . $userid)){
		        throw new UserNotFoundException();
		    }
		    
		    $done = $this->JACKED->MySQL->getRow($this->config->dbt_users, 'id=' . $userid);
		    unset($done['password']);
		    return $done;
		}
		
		/**
         * Deletes the given user by username
         *
         * @param $username string The user to delete
         * @throws UserNotFoundException if the user is not found
         * @return boolean Whether the delete worked
         */
		public function deleteUser($username){
		    $id = $this->JACKED->MySQL->getVal('id', $this->config->dbt_users,  "email='" . $username. "'");
		    if(!$id){
		        throw new UserNotFoundException();
		    }
		    
		    return $this->JACKED->MySQL->delete($this->config->dbt_users, 'id=' . $id);
		}
		
		/**
         * Deletes the given user by id
         *
         * @param $userid int The user to delete
         * @throws UserNotFoundException if the user is not found
         * @return boolean Whether the delete worked
         */
		public function deleteUserByID($userid){
		    if(!$this->JACKED->MySQL->getVal('id', $this->config->dbt_users, 'id=' . $userid)){
		        throw new UserNotFoundException();
		    }
		    
		    return $this->JACKED->MySQL->delete($this->config->dbt_users, 'id=' . $userid);
		}
		
    }

    class UserNotFoundException extends Exception{
        protected $message = "User does not exist.";
    }
    class IncorrectPasswordException extends Exception{
        protected $message = "Incorrect password for user.";
    }
    class NotLoggedInException extends Exception{
        protected $message = "User is not logged in.";
    }
    class ExistingUserException extends Exception{
        protected $message = "User already exists.";
    }

?>