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
    
        public function __construct($dependencies = array()){
            self::$_instance = $this;
            self::$_instance->config = new Configur("core");
            
            //load util and logging 
            self::$_instance->loadDependencies(array('Logr', 'Util'));

            //load dependencies
            //sanity
            if(!is_array($dependencies)){
                $dependencies = explode(", ", $dependencies);
            }
            self::$_instance->loadDependencies($dependencies);
        }
        
        public static function getInstance(){
            if(!isset(self::$_instance)){
                self::$_instance = new JACKED();
                self::$_instance->Logr->write('The JACKED static instance was accessed before instantiation. Dependences may not have been loaded', 2);
            }
            return self::$_instance;
        }

        private function isModuleRegistered($name, $version = false){
            $instance = self::getInstance();
            if(property_exists($instance, $name)){
                if($version){
                    $module = $instance->$name;
                    return (float) $module::getModuleVersion() == (float) $version;
                }else{
                    return true;
                }
            }
        }
        
        private function registerModule($name, $version = false){
            if($name && !self::$_instance->isModuleRegistered($name, $version)){
                self::$_instance->$name = new $name(self::getInstance());
            }
        }
    
        public function loadDependencies($deps){
            $instance = self::getInstance();
            foreach($deps as $module => $modDetails){
                //for sanity
                if(!is_array($modDetails)){
                    $module = $modDetails;
                    $modDetails = array('version' => false, 'required' => true);
                }else{
                    if(!array_key_exists($modDetails, 'version')){
                        $modDetails['version'] = false;
                    }
                    if(!array_key_exists($modDetails, 'required')){
                        $modDetails['required'] = true;
                    }
                }

                if(!$instance->isModuleRegistered($module, $modDetails['version'])){
                    try{
                        $instance->registerModule($module, $modDetails['version']);
                    }catch(ModuleLoadException $e){
                        if($module['required']){
                            try{
                                $instance->Logr->write('Required module ' . $module . ' (v' . $modDetails['version'] . ') couldn\'t be loaded: ' . $e->getMessage(), 4, $e->getTrace());
                            }catch(Exception $ex){}
                            die('JACKED failed to load required module <strong>' . $module . ' (v' . $modDetails['version'] . ')</strong>.');
                        }else{
                            $instance->$module = new Derper();
                            try{
                                $instance->Logr->write('Optional module ' . $module . ' (v' . $modDetails['version'] . ') couldn\'t be loaded: ' . $e->getMessage(), 3, $e-getTrace());
                            }catch(Exception $ex){}
                        }
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
                    $instance = self::getInstance();
                    $instance->Logr->write('Library ' . $libname . ' couldn\'t be loaded: File does not exist.', 4);
                    throw new Exception("JACKED can't find a library named " . $libname . ".");
                }
                return $did;
            }else{
                return true;
            }
        }
        
        public function __destruct(){
            self::$_instance = NULL;
        }
        
        public function derp(){
            //here there be a space for testing
        }
    }


    class ModuleLoadException extends Exception{
        protected $message = 'The requested module could not be loaded.';
    }
?>
