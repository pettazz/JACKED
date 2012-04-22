<?php

    class JACKEDModule {
        public $config;
        protected $JACKED;
        public $isModuleEnabled = true;
        
        protected static $moduleName = "Some JACKED Module";
        protected static $moduleVersion = 0;
        protected static $dependencies = array();
    
        public function __construct($JACKED){
            $this->config = new Configur($this->getModuleName());
            
            $this->JACKED = $JACKED;
            $this->JACKED->loadDependencies($this::getModuleDependencies());
        }
        
        public static function getModuleName(){
            return static::moduleName;
        }
        public static function getModuleVersion(){
            return static::moduleVersion;
        }
        public static function getModuleDependencies(){
            return $dependencies;
        }
        
        
    }

    class ArgumentMissingException extends Exception{
        public function __construct($missingArg, $code = 0, Exception $previous = null){
            $message = "Required argument `$missingArg` not provided.";
            
            parent::__construct($message, $code, $previous);
        }
    }

?>
