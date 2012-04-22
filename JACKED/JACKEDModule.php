<?php

    abstract class JACKEDModule{
        public $config;
        protected $JACKED;
        public $isModuleEnabled = true;
        
        const moduleName = "Some JACKED Module";
        const moduleVersion = 0;

        protected static $dependencies = array();
    
        public function __construct($JACKED){
            $this->config = new Configur($this->getModuleName());
            
            $this->JACKED = $JACKED;
            $this->JACKED->loadDependencies($this::getModuleDependencies());
        }

        public static function getModuleName(){
            return static::moduleName;
        }
        
        public static function getModuleDependencies(){
            return static::$dependencies;
        }
        
    }

    class ArgumentMissingException extends Exception{
        public function __construct($missingArg, $code = 0, Exception $previous = null){
            $message = "Required argument `$missingArg` not provided.";
            
            parent::__construct($message, $code, $previous);
        }
    }

?>
