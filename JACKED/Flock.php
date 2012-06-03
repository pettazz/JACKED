<?php

    class Flock extends JACKEDModule{
        const moduleName = 'Flock';
        const moduleVersion = 1.1;
        public static $dependencies = array('MySQL', 'Sessions');
        
        //Flock provides user management functions
        ////manage your flock of sheeple
        
        /**
         * Get or generate the Source UUID for a user. If the user is logged in as well, tag this new Source
         * with the user id.
         *
         * @param string $unique [optional] If a UDID or MAC or other uniquely identifying id is accessible,
         * this should be passed in here. Defaults to the remote IP + UserAgent.
         * @param string $application [optional] The GUID of the Application that this Source is connected 
         * through. Defaults to config->default_application.
         * @return string GUID for this Source.
         */
        public function getSource($unique = false, $application = false){
            if($this->JACKED->Sessions->check('Flock.Source')){
                return $this->JACKED->Sessions->read('Flock.Source.guid');
            }else{
                if(!$application){
                    $application = $this->config->default_application;
                }
                if(!$unique){
                    $unique = $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'];
                }
                //hashpassword uses bcrypt and is irreversible, so why not?
                $unique_hash = $this->JACKED->Util->hashPassword($unique);

                if($this->checkLogin()){
                    $user = $this->JACKED->Sessions->read("auth.Flock.userid");
                }else{
                    $user = NULL;
                }

                $sources = $this->JACKED->MySQL->getRows(
                    $this->config->dbt_sources,
                    '`unique` = "' . $unique_hash . '"'
                );

                if(!$sources){
                    // $sources has no matches for this unique
                    ////create a new Source, tag it with the user id if there is one
                    $new_guid = $this->JACKED->Util->uuid4();
                    $this->JACKED->MySQL->insert(
                        $this->config->dbt_sources,
                        array(
                            'guid' => $new_guid,
                            'unique' => $unique_hash,
                            'Application' => $application,
                            'User' => $user
                        )
                    );
                    $this->JACKED->Sessions->write('Flock.Source', array(
                        'guid' => $new_guid,
                        'unique' => $unique_hash,
                        'Application' => $application,
                        'User' => $user
                    ));
                    $retval = $new_guid;

                }else if(count($sources) == 1){
                    // $sources has exactly one match for this unique AND
                    if(($user && $sources[0]['user'] == $user) || !$user){
                        //user is logged in and the single Source is tagged as this user, OR
                        //user is not logged in
                        ////return the Source guid
                        $retval = $sources[0]['guid'];
                        $this->JACKED->Sessions->write('Flock.Source', $sources[0]);
                    }elseif($user && $sources[0]['user'] != $user){
                        //user is logged in and the single source is tagged as another user
                        ////create a new source for this user
                        $new_guid = $this->JACKED->Util->uuid4();
                        $this->JACKED->MySQL->insert(
                            $this->config->dbt_sources,
                            array(
                                'guid' => $new_guid,
                                'unique' => $unique_hash,
                                'Application' => $application,
                                'User' => $user
                            )
                        );
                        $this->JACKED->Sessions->write('Flock.Source', array(
                            'guid' => $new_guid,
                            'unique' => $unique_hash,
                            'Application' => $application,
                            'User' => $user
                        ));
                        $retval = $new_guid;
                    }
                }else{
                    // $sources has more than one match AND
                    if($user){
                        // user is logged in
                        ////get the one that matches this user
                        $user_source = $this->JACKED->MySQL->getRow(
                            $this->config->dbt_sources,
                            '`unique` = "' . $unique_hash . '" AND User = ' . $user
                        );
                        $this->JACKED->Sessions->write('Flock.Source', $user_source);
                    }else{
                        // user is not logged in
                        ////get the one that has no user
                        $user_source = $this->JACKED->MySQL->getRow(
                            $this->config->dbt_sources,
                            '`unique` = "' . $unique_hash . '" AND User IS NULL'
                        );
                        $this->JACKED->Sessions->write('Flock.Source', $user_source);
                    }
                    if(!$user_source){
                        // query returned nothing
                        ////create a new source for this user
                        $new_guid = $this->JACKED->Util->uuid4();
                        $this->JACKED->MySQL->insert(
                            $this->config->dbt_sources,
                            array(
                                'guid' => $new_guid,
                                'unique' => $unique_hash,
                                'Application' => $application,
                                'User' => $user
                            )
                        );
                        $this->JACKED->Sessions->write('Flock.Source', array(
                            'guid' => $new_guid,
                            'unique' => $unique_hash,
                            'Application' => $application,
                            'User' => $user
                        ));
                        $retval = $new_guid;
                    }else{
                        // query returned a Source
                        ////return its guid
                        $retval = $user_source['guid'];

                        $this->JACKED->Sessions->write('Flock.Source', $user_source);
                    }
                }

                return $retval;
            }
        }

        /**
         * Get the Unique for the current source
         *
         * @return string Unique hash of the current source, or false if no Source yet exists.
         */
        public function getUnique(){
            return $this->JACKED->Sessions->read("Flock.Source.unique");
        }
        
        /**
         * Get the GUID of the current Source
         *
         * @return string GUID of the current source, or false if no Source yet exists.
         */
        public function getSourceGUID(){
            return $this->JACKED->Sessions->read("Flock.Source.guid");
        }
        
        /**
         * Get the GUID of the Application that the current Source is using
         *
         * @return string GUID of the current source Application, or false if no Source yet exists.
         */
        public function getApplicationGUID(){
            return $this->JACKED->Sessions->read("Flock.Source.Application");
        }
        
        /**
         * Get all the data for the Application that the current Source is using
         *
         * @return array Associative array of the current Application data, or false if no Source yet exists.
         */
        public function getApplication(){
            return $this->JACKED->MySQL->getRow(
                $this->config->dbt_apps,
                'guid = "' . $this->getApplicationGUID() . '"'
            );
        }
        
        /**
         * Whether the current Source Application is on a device (smartphone, tablet, etc) that 
         * allows for some kind of more accurate Unique to be provided
         *
         * @return boolean If the current Application is on a device
         */
        public function isDevice(){
            $app = $this->getApplication();
            return (bool) $app['device'];
        }
        
        /**
         * Get the Application data for the Application with the given API key
         *
         * @param String $key The API key
         * @return array Associative array of the Application data, or false if there is no API key match.
         */
        public function getApplicatonByAPIKey($key){
            return $this->JACKED->MySQL->getRow(
                $this->config->dbt_apps,
                'apiKey = "' . $key . '"'
            );
        }

        /**
         * Get the data in the current Source's data field in the db
         *
         * @return mixed The data stored in the Source, false if there is no current Source
         */
        public function readSourceData(){
            $source = $this->getSourceGUID();
            if($source){
                $value = unserialize($this->JACKED->MySQL->get(
                    'data',
                    $this->config->dbt_sources,
                    'guid = "' . $source . '"'
                ));
            }else{
                $value = false;
            }
            return $value;
        }

        /**
         * Store some data in the current Source's data field in the db
         *
         * @param mixed $data The data to store
         * @return boolean Whether the store was successful, also false if there is no current Source
         */
        public function storeSourceData($data){
            $source = $this->getSourceGUID();
            if($source){
                $value = $this->JACKED->MySQL->update(
                    $this->config->dbt_sources,
                    array('data' => serialize($data)),
                    'guid = "' . $source . '"'
                );
            }else{
                $value = false;
            }
            return $value;
        }

        /**
         * Tag an anonymous Source with a User guid
         *
         * @param string $source The guid of the Source to tag
         * @param string $user The guid of the User to tag the Source as
         * @return boolean Whether the tagging was successful
         */
        private function tagSource($source, $user){
            return (
                $this->JACKED->MySQL->update(
                    $this->config->dbt_sources,
                    array('User' => $user),
                    'guid = "' . $source . '"'
                ) &&
                $this->JACKED->Sessions->write('Flock.Source.user', $user)
            );
        }

        
        /**
         * Login the given user with the given password.
         * 
         * Uses the auth.Flock Session array
         *
         * @param string $identifier The username or email to log in with 
         * @param string $password The user's password 
         * @throws UserNotFoundException if the given username does not exist
         * @throws IncorrectPasswordException if the given password does not match the username's login
         * @return boolean Whether the user is now logged in successfully
         */
        public function login($identifier, $password){
            $username = $this->JACKED->MySQL->sanitize($identifier);
            $vals = $this->JACKED->MySQL->getAll('guid, password', $this->config->dbt_users, $this->config->user_identifier_field . "='$username'");
            
            if($vals['password']){
                $userID = $vals['guid'];
                $hash = $this->JACKED->Util->checkPassword($password, $vals['password'], true);
                if($hash){
                    $this->JACKED->Sessions->write("auth.Flock", array(
                        'loggedIn' => true,
                        'username'     => $username, 
                        'email'     => $username, 
                        'userid'   => $userID,
                        'sessionID' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']),
                        'hash' => $hash
                    ));
                    if($this->JACKED->Sessions->check('Flock.Source') && $this->JACKED->Sessions->check('Flock.Source.user') != $userID){
                        $this->tagSource($this->JACKED->Sessions->read('Flock.Source.guid'), $userID);
                    }
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
        /* this was probably a bad idea to begin with. 
        public function hashedLogin($username, $hpassword){
            $username = $this->JACKED->MySQL->sanitize($username);

            $vals = $this->JACKED->MySQL->getAll('id, password', $this->config->dbt_users, "email='$username'");
            
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
        }*/
        
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
                    'email' => NULL,
                    'username' => NULL, 
                    'userid' => NULL,
                    'sessionID' => NULL
                ));
                $this->JACKED->Sessions->delete("Flock.Source");
                return true;
            }else{
                throw new NotLoggedInException();
            }
        }
                
        //user management
        
        /**
         * Creates a new user with the given data
         *
         * @param $username string New user username
         * @param $email string New user email
         * @param $password string New password
         * @param $details array Associative array of any other user details
         * @throws ExistingUserException if the username already exists
         * @return boolean Whether the creation was successful
         */
        public function createUser($username, $email, $password, $details=array()){
            if($this->JACKED->MySQL->getRow($this->config->dbt_users, "email='" . $email. "'") || 
                $this->JACKED->MySQL->getRow($this->config->dbt_users, "username='" . $username. "'")){
                throw new ExistingUserException();
            }
            $guid = $this->JACKED->Util->uuid4();
            $details = array_merge($details, array(
                'guid' => $guid, 'email' => $email, 'username' => $username, 'password' => $this->JACKED->Util->hashPassword($password)
            ));
            $done = $this->JACKED->MySQL->insert($this->config->dbt_users, $details);
            if($done === false){
                return false;
            }else{
                return $guid;
            }
        }
        
        /**
         * Update an existing user name with the given details
         *
         * @param $userguid string GUID of the user to update
         * @param $details array Associative array of user details
         * @return boolean Whether the update was successful
         */
        public function updateUser($userguid, $details=array()){
            if(array_key_exists('email', $details)){ 
                unset($details['email']);
            }
            if(array_key_exists('password', $details)){ 
                unset($details['password']);
            }
            if(array_key_exists('guid', $details)){ 
                unset($details['guid']);
            }
            return $this->JACKED->MySQL->update($this->config->dbt_users, $details, 'guid=' . $userguid);
        }
        
        /**
         * Update an existing user's email with the given details
         *
         * @param $userguid string User GUID to change
         * @param $email String New email
         * @throws ExistingUserException if the new email already exists
         * @return boolean Whether the update was successful
         */
        public function updateUserEmail($userguid, $email){
           try{
                $this->getUserByGUID($userguid);
                throw new ExistingUserException();
            }catch(UserNotFoundException $e){
                return (
                    $emailDone = $this->JACKED->MySQL->update(
                        $this->config->dbt_users,
                        array(
                            'email' => $email
                        ),
                        'guid = ' . $userguid
                    ) &&
                    $this->JACKED->Sessions->write("auth.Flock.username", $email) &&
                    $this->JACKED->Sessions->write("auth.Flock.email", $email)
                );
            } 
        }
        
        /**
         * Update an existing user's username with the given details
         *
         * @param $userguid string GUID of User to change
         * @param $username String New username
         * @throws ExistingUserException if the new username already exists
         * @return boolean Whether the update was successful
         */
        public function updateUsername($userguid, $email){
           try{
                $this->getUserByGUID($userguid);
                throw new ExistingUserException();
            }catch(UserNotFoundException $e){
                return (
                    $emailDone = $this->JACKED->MySQL->update(
                        $this->config->dbt_users,
                        array(
                            'email' => $email
                        ),
                        'guid = ' . $userguid
                    ) &&
                    $this->JACKED->Sessions->write("auth.Flock.username", $email) &&
                    $this->JACKED->Sessions->write("auth.Flock.email", $email)
                );
            } 
        }
        
        /**
         * Update an existing user's username with the given details
         *
         * @param $userguid string User ID to change
         * @param $password String New password
         * @return boolean Whether the update was successful
         */
        public function updateUserPassword($userguid, $password){
            //this would be a good place to check things like mininum password security stuff
            return $emailDone = $this->JACKED->MySQL->update(
                $this->config->dbt_users,
                array(
                    'password' => $this->JACKED->Util->hashPassword($password)
                ),
                'guid = ' . $userguid
            );
        }
        
        /**
         * Gets user details for a given username
         *
         * @param string $identifier The username or email to log in with 
         * @throws UserNotFoundException if the user is not found
         * @return array Associative array of all the details of the user details
         */
        public function getUser($identifier){
            $guid = $this->JACKED->MySQL->get('guid', $this->config->dbt_users,  $this->config->user_identifier_field . "='" . $identifier. "'");
            if(!$guid){
                throw new UserNotFoundException();
            }
            
            $done = $this->JACKED->MySQL->getRow($this->config->dbt_users, 'guid=' . $guid);
            unset($done['password']);
            return $done;
        }
        
        /**
         * Gets user details for a given user guid
         *
         * @param $userguid string User GUID to get details for
         * @throws UserNotFoundException if the user is not found
         * @return array Associative array of all the details of the user details
         */
        public function getUserByGUID($userguid){
            if(!$this->JACKED->MySQL->get('guid', $this->config->dbt_users, 'guid=' . $userguid)){
                throw new UserNotFoundException();
            }
            
            $done = $this->JACKED->MySQL->getRow($this->config->dbt_users, 'guid=' . $userguid);
            unset($done['password']);
            return $done;
        }
        
        /**
         * Deletes the given user by guid
         *
         * @param $userguid string The GUID of the user to delete
         * @throws UserNotFoundException if the user is not found
         * @return boolean Whether the delete worked
         */
        public function delete($userguid){
            if(!$this->JACKED->MySQL->get('guid', $this->config->dbt_users, 'guid=' . $userguid)){
                throw new UserNotFoundException();
            }
            
            return $this->JACKED->MySQL->delete($this->config->dbt_users, 'guid=' . $userguid);
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