<?php

    class Derper extends JACKEDModule{
		const moduleName = 'Derper';
		const moduleVersion = 1.0;
		
        public function __construct(){
            $this->isModuleEnabled = false;
            return false;
    	}
    	
    	public function __call($name, $args){
    	    //lol nah
    	    return false;
    	}
    	public static function __callStatic($name, $args){
    	    //lol nah
    	    return false;
    	}
    }

?>