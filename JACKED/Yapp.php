<?php

    class Yapp extends JACKEDModule{
        
        /*
            So you made a thing that talks to apps?
            Well, I made a thing that lets you make your website able to talk to apps.
            Call it Yapp.
        */
        
        const moduleName = 'Yapp';
        const moduleVersion = 2.1;
        public static $dependencies = array('MySQL', 'Sessions', 'Flock');
        
        public function __construct($JACKED){
            $this->request_method = $_SERVER['REQUEST_METHOD'];
            
            JACKEDModule::__construct($JACKED);
            $JACKED->loadDependencies($this->config->interface_classes);
        }
        
        /////////////////////////////////////////////////////
        //internal 
        private function success($data){
            //some API consumers don't like it when we wrap a single array 
            ////in an array for no reason
            if((count($data) == 1) && (is_array($data[0]))){
                $data = $data[0];
            }
            return json_encode(array("done" => True, "data" => $data));
        }
        
        private function error($message){
            return json_encode(array("done" => False, "message" => $message));
        }
        
        private function exception($exception){
            $this->JACKED->Logr->write($exception->getMessage(), 2, $exception->getTrace());
            return json_encode(array("done" => False, "message" => $exception->getMessage()));
        }
        
        //replace these with the new JACKED module events
        private function execPreCallHooks($methodname, &$args){
            if(array_key_exists($methodname, $this->config->interface_pre_hooks)){
                call_user_func_array($this->config->interface_pre_hooks[$methodname], array($this->JACKED, &$args));
            }
        }
        
        private function execPostCallHooks($methodname, &$args){
            if(array_key_exists($methodname, $this->config->interface_post_hooks)){
                call_user_func_array($this->config->interface_post_hooks[$methodname], array($this->JACKED, &$args));
            }
            return $args;
        }
        
        //////////////////////////////////////////////////////
        //public api-related
        public function getMyUUID(){
            return $this->JACKED->Flock->getUnique();
        }
        
        public function getMySourceID(){
            return $this->JACKED->Flock->getSourceGUID();
        }
        
        public function getMyApplicationInfo(){
            return $this->JACKED->Flock->getApplication();
        }
        
        public function isDevice(){
            return $this->JACKED->Flock->isDevice();
        }
        
        public function getMyApplicationID(){
            return $this->JACKED->Flock->getApplicationGUID();
        }
        /*
        public function updateSourceUser($userid){
            //if($this->isDevice()){
                return $this->JACKED->MySQL->update(
                    $this->config->db_sources,
                    array(
                        'user_id' => $userid,
                        'device_hash' => (($userid)? md5(session_id() . $userid) : '')
                    ), 
                    "id = '" . $this->getMySourceID() . "'"
                );
            //}
        }*/
        
        //start a new API session for the given APIKey and UUID
        ////returns the new API session id if it all works, 
        ////or an Exception
        public function open($apiKey, $uuid = null){
            $app = $this->JACKED->Flock->getApplicatonByAPIKey($apiKey);
            if(!$app){
                throw new APIKeyNotFoundException();
            }else if($app['device'] == 1 && !$uuid){
                throw new NoUUIDForDeviceException();
            }
            $source = $this->JACKED->Flock->getSource($uuid, $app['guid']);
                
            $this->JACKED->Sessions->write("auth.API", md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']));
            
            return session_id();
        }
        
        //KILL IT ALL WITH FIRE
        public function close(){
            $this->JACKED->Sessions->write("auth.API", array());
            $this->JACKED->Sessions->delete("auth.API");
        }
        
        //boolean: is an API session currently active for me? Here is my session id. HALP?
        public function isActive($sid){
            $good = false;
        
            //dis is a really needs more security.
            if($this->JACKED->Sessions->idExists($sid) && $this->JACKED->Sessions->read("auth.API") != "false"){
                if($this->JACKED->Sessions->read("auth.API") == md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']))
                    $good = true;
            }
            
            return $good;
        }
        
        //Allow some simple Sessions access
        ////uses Yapp.APISession Session var
        public function store($key, $value){
            return $this->JACKED->Sessions->write('Yapp.APISession' . $key, $value);
        }
        
        public function read($key){
            return $this->JACKED->Sessions->read('Yapp.APISession' . $key);
        }
        
        ///////
        //    NOTIFICATIONS  //
                        ///////
        
        //this should be better too

        //gets any visible notifications
        public function getNotifications(){
            $this->trimNotifications();
            $now = time();
            return $this->JACKED->MySQL->getAll("*", $this->config->db_notifications, "`start` <= '" . $now . "' AND `end` >= '" . $now . "' AND (`Application` = '0' OR `Application` = '" . $this->getMyApplicationID() . "')");
        }
        
        //trims the notification table of anything that expired a month ago
        public function trimNotifications(){
            $when = time() - 2592000;
            return $this->JACKED->MySQL->delete($this->config->db_notifications, "`end` <= '" . $when . "' AND (`Application` = '0' OR `Application` = '" . $this->getMyApplicationID() . "')");
        }
        
        
        /////////////////////////////////////////////////////////////////
        //                     API CALL HANDLER!!!!!!WOOOOOOOOOOOO
        /////////////////////////////////////////////////////////////////
        public function call(){
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
                                return $this->success(array('api_token' => $openres, 'ttl' => $this->JACKED->Sessions->config->session_expiry, 'user_id' => $this->JACKED->Sessions->read('user.Source.user_id'), 'notifications' => $this->getNotifications()));
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
                                $this->execPreCallHooks($methodname, $orderedArguments);
                                $result = call_user_func_array(array($this->JACKED->$class, $methodname), $orderedArguments);
                                return $this->success($this->execPostCallHooks($methodname, $result));
                            }
                        }
                        throw new APIActionNotRecognizedException();
                        break;
                }
                
            }catch(Exception $e){
                return $this->exception($e);
            }
            
        }//end call method
        
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
