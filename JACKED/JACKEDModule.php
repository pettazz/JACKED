<?php

    abstract class JACKEDModule{
        public $config;
        protected $JACKED;
        protected $events = array();
        public $isModuleEnabled = true;
        
        const moduleName = "Some JACKED Module";
        const moduleVersion = 0;

        public static $dependencies = array();
    
        public function __construct($JACKED){
            $this->config = new Configur(self::getModuleName());
            
            $this->JACKED = $JACKED;
            $this->JACKED->loadDependencies(self::getModuleDependencies());
            $this->fireEvent('moduleLoad', array('moduleName' => self::getModuleName()));
        }

        public static function getModuleName(){
            return static::moduleName;
        }

        public static function getModuleVersion(){
            return static::moduleVersion;
        }
        
        public static function getModuleDependencies(){
            return static::$dependencies;
        }


        public function attachToEvent($event, $callback){
            if(array_key_exists($event, $this->events)){
                $this->events[$event][] = $callback;
            }else{
                $this->events[$event] = array($callback);
            }
        }

        public function fireEvent($event, $data = array()){
            if(array_key_exists($event, $this->events)){
                foreach($this->events[$event] as $observer){
                    $observer($data);
                }
            }
        }

        public function __destruct(){
            $this->fireEvent('moduleUnload');
        }
        
    }

    class ArgumentMissingException extends Exception{
        public function __construct($missingArg, $code = 0, Exception $previous = null){
            $message = "Required argument `$missingArg` not provided.";
            
            parent::__construct($message, $code, $previous);
        }
    }

?>
