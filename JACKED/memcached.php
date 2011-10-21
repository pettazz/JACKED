<?php

    class memcached extends JACKEDModule{
		const moduleName = 'Memcached';
		const moduleVersion = 1.0;
		const dependencies = 'MySQL';
		const optionalDependencies = 'stats';
		
    	private $M;
    	
        public function __construct($JACKED){
		    JACKEDModule::__construct($JACKED);
		    
        	try{
        	    $this->M = new Memcache;
        	}catch(Exception $e){
	            $this->isModuleEnabled = false;
        	    throw new Exception('Could not create Memcache object.');
        	}

        	try{
        	    $this->M->connect($this->config->server, $this->config->port, $this->config->connect_timeout);
        	}catch(Exception $e){
	            $this->isModuleEnabled = false;
        	    throw new Exception('Could not connect to memcached server.');
        	}
    	}
    	
    	//everything assumes $this->M is connected
    	public function __destruct(){
    	    $this->M->close();
    	}
    	
    	private function setCache($key, $value, $timeout = NULL){
    	    if($timeout === NULL){
    	        $timeout = $this->config->cache_expire;
    	    }
    	    
    	    return $this->M->set($key, $value, MEMCACHE_COMPRESSED, $timeout);
    	}
    	
    	private function getCache($key){
    	    $done = $this->M->get($key);
    	    return $done? $done : false;
    	}
    	
    	private function cacheDelete($key){
    	    return $this->M->delete($key);
    	}
    	
    	//PUBLIC SHIT!
    	//SELECT val FROM table WHERE cond
		//val is just one field, and you only get the first result
		public function getVal($val, $table, $cond = null){
		    $key = md5('getVal' . $val . $table . $cond);
		    $done = $this->getCache($key);
            if($done === false){
                JACKED::debug_dump('couldnt get value from memcached. default to mysql');
                $done = $this->JACKED->MySQL->getVal($val, $table, $cond);
                $this->setCache($key, $done);
            }
            return $done;
		}
		
		//SELECT vals FROM table WHERE cond
		////default link can be overridden
		public function getRowVals($vals, $table, $cond, $link = NULL, $result_type = MYSQL_BOTH){
		    $key = md5('getRowVals' . $vals . $table . $cond);
		    $done = $this->getCache($key);
            if($done === false){
                JACKED::debug_dump('couldnt get value from memcached. default to mysql');
                $done = $this->JACKED->MySQL->getRowVals($vals, $table, $cond, $link, $result_type);
                $this->setCache($key, $done);
            }
            return $done;
		}
		
		//SELECT * FROM table WHERE cond
		////default link can be overridden
		public function getRow($table, $cond, $link = NULL, $result_type = MYSQL_BOTH){
		    $key = md5('getRow' . $table . $cond);
		    $done = $this->getCache($key);
            if($done === false){
                JACKED::debug_dump('couldnt get value from memcached. default to mysql');
                $done = $this->JACKED->MySQL->getRow($table, $cond, $link, $result_type);
                $this->setCache($key, $done);
            }
            return $done;
		}
		
		//SELECT vals FROM table WHERE cond
		//vals is an array of field names
		//returns an array of vals
		////default link can be overridden
		public function getAllVals($vals, $table, $cond, $link = NULL){
		    $key = md5('getAllVals' . $vals . $table . $cond);
		    $done = $this->getCache($key);
            if($done === false){
                JACKED::debug_dump('couldnt get value from memcached. default to mysql');
                $done = $this->JACKED->MySQL->getAllVals($vals, $table, $cond, $link);
                $this->setCache($key, $done);
            }
            return $done;
		}    	
    }

?>
