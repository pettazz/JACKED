<?php

    class Memcacher extends JACKEDModule{
        const moduleName = 'Memcacher';
        const moduleVersion = 1.0;
        const dependencies = '';
        const optionalDependencies = '';
        
        private $M;
        
        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);

            try{
                $this->M = new Memcache;
                $this->M->connect($this->config->server, $this->config->port, $this->config->connect_timeout);
            }catch(Exception $e){
                throw new Exception('Could not connect to memcached server.');
            }
        }
        
        //everything assumes $M is connected
        public function __destruct(){
            try{
                $this->M->close();
            }catch(Exception $e){}
        }
        
        /**
        * Set the value of a new key in the cache. Will not overwrite existing keys with the same name.
        * 
        * @param $key String The key to store the value as
        * @param $value Mixed The value to store in the cache
        * @param $timeout int [optional] Set the seconds expiration on this value in the cache (defaults to module config value)
        * @return Boolean Whether the value was set successfully, or False if the key already exists
        */
        private function add($key, $value, $timeout = NULL){
            if($timeout === NULL){
                $timeout = $this->config->cache_expire;
            }
            
            return $this->M->add($key, $value, MEMCACHE_COMPRESSED, $timeout);
        }
        
        /**
        * Set the value of a key in the cache. Overwrites an existing key with the same name.
        * 
        * @param $key String The key to store the value as
        * @param $value Mixed The value to store in the cache
        * @param $timeout int [optional] Set the seconds expiration on this value in the cache (defaults to module config value)
        * @return Boolean Whether the value was set successfully
        */
        private function set($key, $value, $timeout = NULL){
            if($timeout === NULL){
                $timeout = $this->config->cache_expire;
            }
            
            return $this->M->set($key, $value, MEMCACHE_COMPRESSED, $timeout);
        }
        
        /**
        * Increment the value of a key in the cache. Doesn't create a new key if $key does not exist.
        * 
        * @param $key String The key whose value to increment
        * @param $inc Mixed The value to increment $key by
        * @return Mixed The newly incremented value, or False on failure
        */
        private function increment($key, $inc){
            return $this->M->increment($key, $inc);
        }
        
        /**
        * Decrement the value of a key in the cache. Doesn't create a new key if $key does not exist.
        * 
        * @param $key String The key whose value to decrement
        * @param $dec Mixed The value to decrement $key by
        * @return Mixed The newly decremented value, or False on failure
        */
        private function decrement($key, $dec){
            return $this->M->decrement($key, $dec);
        }
        
        /**
        * Replace the value of an existing key in the cache
        * 
        * @param $key String The key to be replaced
        * @param $value Mixed The value to store in the cache
        * @param $timeout int [optional] Set the seconds expiration on this value in the cache (defaults to module config value)
        * @return Boolean Whether the value was replaced successfully
        */
        private function replace($key, $value, $timeout = NULL){
            if($timeout === NULL){
                $timeout = $this->config->cache_expire;
            }
            
            return $this->M->replace($key, $value, MEMCACHE_COMPRESSED, $timeout);
        }
        
        /**
        * Get the value of a key in the cache
        * 
        * @param $key String The key to store the value as
        * @return Mixed The cache value for $key (defaults to false if none was found)
        */
        private function get($key){
            $done = $this->M->get($key);
            return $done? $done : false;
        }
        
        /**
        * Delete a key from the cache
        * 
        * @param $key String The key to be deleted
        * @return Boolean Whether the delete was successful
        */
        private function delete($key){
            return $this->M->delete($key);
        }     
        
        /**
        * Clear all existing values from the entire cache
        * 
        * @return Boolean Whether the flush was successful
        */
        private function flush(){
            return $this->M->flush();
        }       
    }

?>
