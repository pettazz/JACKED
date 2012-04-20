<?php
    //autoload classes from modules folder when they're called
    ////BE CAREFUL because this will only autoload Modules, not libs or anything else
    //maybe there should be a better loader but this is just fine for now
    spl_autoload_register(function($class){
        $did = false;
        $file = JACKED_MODULES_ROOT . $class . '.php';
        if (file_exists($file)){
            require($file);
            $did = true;
        }else{
            throw new Exception("JACKED can't find a class for the module named " . $class . ".");
        }
        return $did;
    }); 

    class JACKED{
        const moduleName = "JACKED Core";
        const moduleVersion = 3.0;
    
        protected static $_instance = null;
        public $config;
    
        public function __construct($required="", $optional=""){
            self::$_instance = $this;
            self::$_instance->config = new Configur("core");
            
            //load util and logging 
            self::$_instance->loadDependencies('Logr, Util');

            //load dependencies
            self::$_instance->loadDependencies($required);
            self::$_instance->loadOptionalDependencies($optional);
        }
        
        private function isModuleRegistered($name){
            return property_exists(self::$_instance, $name);
        }
        
        private function registerModule($name){
            if($name && !self::$_instance->isModuleRegistered($name)){
                self::$_instance->$name = new $name(self::$_instance);
            }
        }
    
        public function loadDependencies($deps){
            foreach(explode(", ", $deps) as $module){
                if(!self::$_instance->isModuleRegistered($module)){
                    try{
                        self::$_instance->registerModule($module);
                    }catch(Exception $e){
                        try{
                            self::$_instance->Logr->write('Required module ' . $module . ' couldn\'t be loaded: ' . $e->getMessage(), 4, $e-getTrace());
                        }catch(Exception $ex){}
                        die('JACKED failed to load required module <strong>' . $module . '</strong>.');
                    }
                }
            }
        }
        
        public function loadOptionalDependencies($mods){
            foreach(explode(", ", $mods) as $module){
                if(!self::$_instance->isModuleRegistered($module)){
                    try{
                        self::$_instance->registerModule($module);
                    }catch(Exception $e){
                        self::$_instance->$module = new Derper();
                        try{
                            self::$_instance->Logr->write('Optional module ' . $module . ' couldn\'t be loaded: ' . $e->getMessage(), 3, $e-getTrace());
                        }catch(Exception $ex){}
                    }
                }
            }
        }
        
        public function importLib($libname){
            //this could certainly be better, but it works for now
            ////for now we'll assume every lib is a single class in a same name .php
            if(!class_exists($libname, FALSE)){ //definitely needs to be better
                $did = false;
                $file = JACKED_LIB_ROOT . $libname . '.php';
                if (file_exists($file)){
                    require($file);
                    $did = true;
                }else{
                    self::$_instance->Logr->write('Library ' . $libname . ' couldn\'t be loaded: File does not exist.', 4);
                    throw new Exception("JACKED can't find a library named " . $libname . ".");
                }
                return $did;
            }else{
                return true;
            }
        }
        
        public function __destruct(){
            //unload?
        }
        
        public function derp(){
            //here there be a space for testing
        }
    }
?>
