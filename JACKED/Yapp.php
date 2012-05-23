<?php

    /*
    ???:     (1) should pre/post call events pass args by pointer so that they can be edited pre/post call? 
                    and should it fire "preCall" . $methodName instead of a generic preCall event for every method?
    !!!TODO: x(1) replace execPreCallHooks and post with new JACKED Module events
             (2) Persistently store API session data in the Source's data field
             (3) Make notifications not shit and re-enable
    */

    class Yapp extends JACKEDModule{
        
        /*
            So you made a thing that talks to apps?
            Well, I made a thing that lets you make your website able to talk to apps.
            Call it Yapp.

            Event hooks:

                preCall - Fired just before the called method is executed.
                data: 
                    String className - name of the class whose method is to be executed
                    String methodName - name of the method to be executed
                    Array orderedArguments - associative array of all arguments being passed to 
                        the method with their names as keys.

                postCall - Fired just after the called method is executed.
                data: 
                    String className - name of the class whose method was executed
                    String methodName - name of the method executed
                    mixed result - the return value of the executed method, to be returned from
                        the Yapp->call method.

        */
        
        const moduleName = 'Yapp';
        const moduleVersion = 2.1;
        public static $dependencies = array('MySQL', 'Sessions', 'Flock');
        
        public function __construct($JACKED){
            $this->request_method = $_SERVER['REQUEST_METHOD'];
            
            JACKEDModule::__construct($JACKED);
            $JACKED->loadDependencies($this->config->interface_classes);
        }
        

        /**
        * JSON encode the result of a successful call. 
        * 
        * @param mixed $data The resulting data of the call
        * @return String JSON encoded @data
        */
        private function success($data){
            return json_encode(array("done" => True, "data" => $data));
        }
        
        /**
        * JSON encode the result of an error during a call. 
        * 
        * @param mixed $message The error message
        * @return String JSON encoded @message
        */
        private function error($message){
            return json_encode(array("done" => False, "message" => $message));
        }
        
        /**
        * JSON encode and log an exception during a call. 
        * 
        * @param mixed $exception The exception to encode
        * @return String JSON encoded @exception
        */
        private function exception($exception){
            $this->JACKED->Logr->write($exception->getMessage(), 2, $exception->getTrace());
            return json_encode(array("done" => False, "message" => $exception->getMessage()));
        }


        
        /**
        * Open a new API session for an application using the given API key, 
        * with the given unique identifier. Flock will create a unique identifier with IP and
        * user agent if one isn't supplied here.
        * 
        * @param String $apiKey The API key of the application connecting
        * @param String $unique [optional] The unique identifier of the device/machine connecting, if applicable.
        * @return String The token for the newly opened API session
        */
        public function open($apiKey, $uuid = null){
            $app = $this->JACKED->Flock->getApplicatonByAPIKey($apiKey);
            if(!$app){
                throw new APIKeyNotFoundException();
            }else if($app['device'] == 1 && !$uuid){
                throw new NoUUIDForDeviceException();
            }
            $source = $this->JACKED->Flock->getSource($uuid, $app['guid']);
                
            $this->JACKED->Sessions->write("auth.API", md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']));
            $this->JACKED->Sessions->write('Yapp.APISession', $this->JACKED->Flock->readSourceData());

            return session_id();
        }
        
        /**
        * Close the current API session and destroy all its data.
        */
        public function close(){
            //KILL IT ALL WITH FIRE
            $this->JACKED->Sessions->write("auth.API", array());
            $this->JACKED->Sessions->delete("auth.API");
        }
        
        /**
        * Check whether a given API session is active.
        * 
        * @param String $sid The token of the API session to check
        * @return Boolean Whether the given session is active
        */
        public function isActive($sid){
            $good = false;
        
            //dis is a really needs more security.
            if($this->JACKED->Sessions->idExists($sid) && $this->JACKED->Sessions->read("auth.API") != "false"){
                if($this->JACKED->Sessions->read("auth.API") == md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']))
                    $good = true;
            }
            
            return $good;
        }
        
        /**
        * Store a key -> value pair in the current API session.
        * 
        * @param String $apiKey The API key of the application connecting
        * @param String $unique [optional] The unique identifier of the device/machine connecting. 
        * @return String The token for the newly opened API session
        */
        public function store($key, $value){
            return (
                $this->JACKED->Sessions->write('Yapp.APISession' . $key, $value) &&
                $this->JACKED->Flock->storeSourceData($this->JACKED->Sessions->read('Yapp.APISession'))
            );
        }
        
        public function read($key){
            return $this->JACKED->Sessions->read('Yapp.APISession' . $key);
        }
        
        
        //TODO(3)
        ///////
        //    NOTIFICATIONS  //
                        ///////

        //gets any visible notifications
        /*public function getNotifications(){
            $this->trimNotifications();
            $now = time();
            return $this->JACKED->MySQL->getAll(
                "*", 
                $this->config->db_notifications, 
                "`start` <= '" . $now . "' AND `end` >= '" . $now . 
                  "' AND (`Application` = '0' OR `Application` = '" . $this->getMyApplicationID() . "')"
            );
        }
        
        //trims the notification table of anything that expired a month ago
        public function trimNotifications(){
            $when = time() - 2592000;
            return $this->JACKED->MySQL->delete(
                $this->config->db_notifications, 
                "`end` <= '" . $when . 
                  "' AND (`Application` = '0' OR `Application` = '" . $this->getMyApplicationID() . "')"
            );
        }*/
        
        
        /**
        * Perform an API call. Uses $_REQUEST data.
        * 
        * @return String JSON encoded success, failure, or exception
        */
        public function call(){
            /////////////////////////////////////////////////////////////////
            //                     API CALL HANDLER!!!!!!WOOOOOOOOOOOO
            /////////////////////////////////////////////////////////////////
            try{
                $api_authorized = true;
                //First, if we're using keys, let's use keys.
                if($this->config->key_restrict){
                    $api_authorized = false; //guilty until proven innocent
                    
                    //check if there's a token being passed
                    if(array_key_exists('api', $_REQUEST)){
                        //if so, validate it
                        
                        if($this->isActive($_REQUEST['api'])){
                            //this is the only way to continue to the actions dispatch
                            $api_authorized = true;
                        }else{
                            throw new APITokenExpiredException();
                        }
                    }else{
                        //if not, see if they're trying to open a new api session
                        if(array_key_exists('action', $_REQUEST) && $_REQUEST['action'] == 'open'){
                            //okay, let's try to log them in, but don't validate for the rest of the actions yet. Just api_done their new sessid
                            if(!array_key_exists('key', $_REQUEST)){
                                throw new APIKeyNotProvidedException();
                            }else{
                                $uuid = (array_key_exists('uuid', $_REQUEST))? $_REQUEST['uuid'] : NULL;
                                $openres = $this->open($_REQUEST['key'], $uuid);    
                                return $this->success(array(
                                    'api_token' => $openres, 
                                    'ttl' => $this->JACKED->Sessions->config->session_expiry, 
                                    'user_id' => $this->JACKED->Sessions->read('user.Source.user_id'))
                                );
                            }
                        }else{ 
                            throw new APIRestrictedException();
                        }
                    }
                }
            
                //halt i am reptar
                if(!$api_authorized){
                    throw new APIRestrictedException();
                }

                if(array_key_exists('action', $_REQUEST))
                    $methodname = $_REQUEST['action'];
                else
                    throw new APIActionNotRecognizedException();

                //check for a few Yapp methods, otherwise delegate to our APIs
                switch($methodname){
                    //Yapp->close()
                    case 'close':
                        return $this->success($this->close());
                        break;
                    //Yapp->isActive($sid)
                    case 'isActive':
                        if(array_key_exists('sid', $_REQUEST)){
                            return $this->success($this->isActive($_REQUEST['sid']));
                        }else{
                            throw new ArgumentMissingException('sid');
                        }
                        break;
                    //Yapp->store($key, $value)
                    case 'store':
                        if(array_key_exists('key', $_REQUEST)){
                            if(array_key_exists('value', $_REQUEST)){
                                return $this->success($this->store($_REQUEST['key'], $_REQUEST['value']));
                            }else{
                                throw new ArgumentMissingException('value');
                            }
                        }else{
                            throw new ArgumentMissingException('key');
                        }
                        break;
                    //Yapp->read($key)
                    case 'read':
                        if(array_key_exists('key', $_REQUEST)){
                            return $this->success($this->read($_REQUEST['key']));
                        }else{
                            throw new ArgumentMissingException('key');
                        }
                        break;
                        
                        
                    default:
                        //dispatch to class interfaces
                        foreach($this->config->interface_classes as $class){
                            if(method_exists($this->JACKED->$class, $methodname)){
                                $reflection = new ReflectionClass($class);
                                $method = $reflection->getMethod($methodname);
                
                                // map $arguments to $orderedArguments for the function
                                $orderedArguments = array();
                
                                foreach($method->getParameters() as $parameter){
                                    if(array_key_exists($parameter->name, $_REQUEST)){
                                        $orderedArguments[] = $_REQUEST[$parameter->name];
                                    }else if($parameter->isOptional()){
                                        $orderedArguments[] = $parameter->getDefaultValue();
                                    }else{
                                        throw new ArgumentMissingException($parameter->name);
                                    }
                                }
                
                                // call method with ordered arguments
                                /// ??? (1)
                                $this->fireEvent('preCall', array(
                                    'className' => $class, 
                                    'methodName' => $methodname, 
                                    'orderedArguments' => $orderedArguments)
                                );
                                $result = call_user_func_array(array($this->JACKED->$class, $methodname), $orderedArguments);
                                $this->fireEvent('postCall', array(
                                    'className' => $class, 
                                    'methodName' => $methodname, 
                                    'result' => $result)
                                );
                                return $result;
                            }
                        }
                        throw new APIActionNotRecognizedException();
                        break;
                }
                
            }catch(Exception $e){
                return $this->exception($e);
            }
            
        }
        
    }
    

    class NoUUIDForDeviceException extends Exception{
        protected $message = 'No UUID was provided for this device. UUIDs are required for devices.';
    }
    class APIKeyNotFoundException extends Exception{
        protected $message = 'Given API Key was not found.';
    }
    class APIKeyNotProvidedException extends Exception{
        protected $message = 'API key was not provided.';
    }
    class APITokenExpiredException extends Exception{
        protected $message = 'The given API token has expired; the session must be reauthenticated.';
    }
    class APIRestrictedException extends Exception{
        protected $message = 'Access to the API is restricted to authenticated sessions.';
    }
    class APIActionNotRecognizedException extends Exception{
        protected $message = 'The API Action was not recognized or none was provided.';
    }
?>
