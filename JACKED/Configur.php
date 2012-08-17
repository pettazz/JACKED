<?php

    class Configur implements ArrayAccess, Countable, IteratorAggregate{
        const moduleName = 'Configur';
        const moduleVersion = 1.5;
    
        protected $_values = array();
        
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
            $this->_values[$offset] = $value;
        }
        
        public function offsetUnset($offset){
            unset($this->_values[$offset]);
        }
        
        public function getIterator(){
            return new ArrayIterator($this->_values);
        }
        
        public function __set($key, $value){
            $this->_values[$key] = $value;
        }

        public function __get($key){
            return $this->_values[$key];
        }
        
    }
?>
