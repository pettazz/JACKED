<?php

    class JACKEDModule {
        public $config;
        protected $JACKED;
        
	    protected static $moduleName = "Some JACKED Module";
	    protected static $moduleVersion = 0;
	    protected static $dependencies = '';
	    protected static $optionalDependencies = '';
    
        public function __construct($JACKED){
        	$this->config = new Configur($this->getModuleName());
        	
			$this->JACKED = $JACKED;
			$this->JACKED->loadDependencies($this::getModuleDependencies());
			$this->JACKED->loadOptionalDependencies($this::getModuleOptionalDependencies());
    	}
        
        public static function getModuleName(){
            return static::moduleName;
        }
        public static function getModuleVersion(){
            return static::moduleVersion;
        }
        public static function getModuleDependencies(){
            return static::dependencies;
        }
        public static function getModuleOptionalDependencies(){
            return static::optionalDependencies;
        }
        
        
    }

    class ArgumentMissingException extends Exception{
        public function __construct($missingArg, $code = 0, Exception $previous = null){
            $message = "Required argument `$missingArg` not provided.";
            
            parent::__construct($message, $code, $previous);
        }
    }

?>
