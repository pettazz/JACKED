<?php
    /*
        Not entirely unlike ORM.
    */

    class Syrup extends JACKEDModule{

        const moduleName = 'Syrup';
        const moduleVersion = 1.0;
        public static $dependencies = array();

        private $registeredModels;

        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);

            $this->registeredModels = array();

            //import the base classes and correct SyrupDriver based on the driver name
            include($this->config->model_root . 'SyrupModel.php');
            include($this->config->driver_root . 'SyrupDriverInterface.php');
            include($this->config->driver_root . $this->config->storage_driver_name . '.php');

            //turn on auto-registration if it's enabled
            /*if($this->config->lazy_register_all === true){
                $JACKED->attachToEvent('moduleLoaded', function($data){
                    $this->registerModule($data['moduleName']);
                });
            }*/
        }

        /**
         * Make the registered modules accessible directly as properties of the class
         */
        public function __get($module){
            if(array_key_exists($module, $this->registeredModels)){
                return $this->registeredModels[$module];
            }else{
                try{
                    $this->registerModule($module);
                    return $this->registeredModels[$module];
                }catch(Exception $ex){
                    throw new UnknownModelException($module, 0, $ex);
                }
            }
        }
        /**
         * We don't want to allow setting of properties
         */
        public function __set($key, $val){
            
        }

        /**
        * Register a Module with Syrup. Loads the content models, 
        * registers the module, and sets up any relations to other registered modules.
        * 
        * @param $moduleName String The full (case-sensitive) class name of the Module to register
        */
        private function registerModule($moduleName){
            try{
                include($this->config->model_root . $moduleName . '.php');
            }catch(Exception $e){
                throw new UnknownModelException($moduleName, 0, $e);
            }
            
            $className = $moduleName . 'Model';
            $this->registeredModels[$moduleName] = new $className($this->config->driverConfig, $this->JACKED->Logr);
        }
    }


    class UnknownModelException extends Exception{
        public function __construct($name, $code = 0, Exception $previous = null){
            $message = "Could not find a model named: `$name`.";
            
            parent::__construct($message, $code, $previous);
        }
    }
?>