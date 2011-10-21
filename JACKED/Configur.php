<?php

    class Configur implements ArrayAccess, Countable, IteratorAggregate{
		const moduleName = 'Configur';
		const moduleVersion = 1.5;
    
        protected $_values = array();
    
        //I TOOK YOUR SINGLETON PATTERN/DEPENDENCY INJECTION AND ノ(°□°ノ）
        
        //protected static $_instance = null;
        //protected static $_modules = array();
        /*public static function getInstance(){
            if (self::$_instance === null) {
                self::$_instance = new Configur;
            }

            return self::$_instance;
        }*/
        /*public function loadConf($name){
            //if we've already loaded this conf, dont do it again
            if(!in_array($name, self::$_modules)){
                $file = JACKED_CONFIG_ROOT . $name . '.php';
                if (file_exists($file)){
    				include($file);
    				foreach($settings as $opt => $val){
    					$this->_values[$opt] = $val;
    				}
    				array_push(self::$_modules, $name);
    			}else{
    			    throw new Exception("JACKED Configurator can't find a config file named " . $name . ".");
    			}
    		}
        }*/
        
        //ABRA KADABRA! ヽ( ﾟヮ・)ノ.･ﾟ*｡･+☆ NOW IT MAKES FUCKING SENSE INSTEAD!
        
        public function __construct($name){
            $file = JACKED_CONFIG_ROOT . $name . '.php';
            if (file_exists($file)){
				include($file);
				foreach($settings as $opt => $val){
					$this->_values[$opt] = $val;
				}
			}else{
			    throw new Exception("JACKED Configurator can't find a config file named " . $name . ".");
			}
        }
        
        final protected function __clone(){
            //no thanks brah
        }
        
        public function count(){
            return sizeof($this->_values);
        }
        
        public function offsetExists($offset){
            return key_exists($offset, $this->_values);
        }
        
        public function offsetGet($offset){
            return $this->_values[$offset];
        }
        
        public function offsetSet($offset, $value){
            //these are "constants"
            $this->_values[$offset] = $value;
        }
        
        public function offsetUnset($offset){
            //these are "constants"
            //unset($this->_values[$offset]);
        }
        
        public function getIterator(){
            return new ArrayIterator($this->_values);
        }
        
        public function __set($key, $value){
            //these are "constants"
            //$this->_values[$key] = $value;
        }

        public function __get($key){
            return $this->_values[$key];
        }
        
    }
?>
