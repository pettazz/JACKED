<?php

    abstract class JACKEDModule{
        public $config;
        protected $JACKED;
        public $isModuleEnabled = true;
        
        const moduleName = "Some JACKED Module";
        const moduleVersion = 0;
    
        public function __construct($JACKED){
            $this->config = new Configur($this->getModuleName());
            
            $this->JACKED = $JACKED;
            $this->JACKED->loadDependencies($this::getModuleDependencies());
        }
        
        public static function getModuleDependencies(){
            if(isset(self::dependencies)){
                return self::dependencies;
            }else{
                return array();
            }
        }
        
    }

    class ArgumentMissingException extends Exception{
        public function __construct($missingArg, $code = 0, Exception $previous = null){
            $message = "Required argument `$missingArg` not provided.";
            
            parent::__construct($message, $code, $previous);
        }
    }

?>
