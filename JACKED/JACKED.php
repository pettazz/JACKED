<?php
    //autoload classes from modules folder when they're called
    ////BE CAREFUL because this will only autoload Modules, not libs or anything else
    //maybe there should be a better loader but this is just fine for now
    function JACKED_SPL_AUTOLOAD_FUNCTION($class){
        $did = false;
        $file = JACKED_MODULES_ROOT . $class . '.php';
        if (file_exists($file)){
            require($file);
            $did = true;
        }else{
            throw new Exception("JACKED can't find a class for the module named " . $class . ".");
        }
        return $did;
    }
    spl_autoload_register('JACKED_SPL_AUTOLOAD_FUNCTION'); 

    class JACKED extends JACKEDModule{
        const moduleName = "JACKED Core";
        const moduleVersion = 3.5;
    
        protected static $_instance = null;
        private $loadedLibraries = array();
        public $config;
    
        public function __construct($dependencies = array()){
            self::$_instance = $this;

            //load configuration
            self::$_instance->config = new Configur("core");
            
            //load util and logging 
            self::$_instance->loadDependencies(array('Logr', 'Util'));

            //load dependencies
            if(!is_array($dependencies)){
                $dependencies = explode(", ", $dependencies);
            }
            self::$_instance->loadDependencies($dependencies);

            // do some basic php setup stuff
            date_default_timezone_set(self::$_instance->config->default_timezone);
        }
        
        public static function getInstance(){
            if(!isset(self::$_instance)){
                self::$_instance = new JACKED();
                self::$_instance->Logr->write('The JACKED static instance was accessed before instantiation. Dependences may not have been loaded', 2);
            }
            return self::$_instance;
        }

        public function isModuleRegistered($name, $version = false){
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
                if($version){
                    $newMod = self::$_instance->$name;
                    if((float) $newMod::getModuleVersion() != (float) $version){
                        throw new Exception("Installed module $name v" . $newMod::getModuleVersion() . " does not match requested version $version");
                    }
                }
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
                    if(!array_key_exists('version', $modDetails)){
                        $modDetails['version'] = false;
                    }
                    if(!array_key_exists('required', $modDetails)){
                        $modDetails['required'] = true;
                    }
                }

                if(!$instance->isModuleRegistered($module, $modDetails['version'])){
                    try{
                        $instance->registerModule($module, $modDetails['version']);
                    }catch(Exception $e){
                        if($modDetails['required']){
                            try{
                                $instance->Logr->write('Required module ' . $module . ' (v' . $modDetails['version'] . ') couldn\'t be loaded: ' . $e->getMessage(), 4, $e->getTrace());
                            }catch(Exception $ex){}
                            die('JACKED failed to load required module <strong>' . $module . ' (v' . $modDetails['version'] . ')</strong>.');
                        }else{
                            $instance->$module = new Derper();
                            try{
                                $instance->Logr->write('Optional module ' . $module . ' (v' . $modDetails['version'] . ') couldn\'t be loaded: ' . $e->getMessage(), 3, $e->getTrace());
                            }catch(Exception $ex){}
                        }
                    }
                }
            }
        }
        
        public function loadLibrary($libname){
            //this could certainly be better, but it works for now
            ////for now we'll assume every lib is a single .php file in JACKED_LIB_ROOT
            ///TODO: have some kind of lib loading standard config file within a lib that explains which files to load, etc
            $instance = self::getInstance();
            if(!in_array($libname, $instance->loadedLibraries)){
                $did = false;
                $file = JACKED_LIB_ROOT . $libname . '.php';
                if(file_exists($file)){
                    include_once($file);
                    array_push($instance->loadedLibraries, $libname);
                    $did = true;
                }else{
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
    }
?>
