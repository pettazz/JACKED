<?php

    class stats extends JACKEDModule{
		const moduleName = 'stats';
		const moduleVersion = 1.0;
		const dependencies = '';
		const optionalDependencies = '';
		
		//whoah, based on StatsD by the Etsy people, this uses NerdPorn instead
    	
        public function __construct($JACKED){
		    JACKEDModule::__construct($JACKED);
        	
        	//make sure our NerdPorn server exists, otherwise, disable the module so we dont waste our time
        	try{
        	    $fp = fsockopen("udp://" + $this->config->host, $this->config->port, $errno, $errstr);
        	}catch(Exception $e){
        	    $this->isModuleEnabled = false;
        	    throw $e;
        	}
    	}
    	
    	public function timing($wat, $time, $sampleRate = 1){
    	    self::timeinfo($wat, $time, $sampleRate);
    	}
    	public function increment($wat, $sampleRate = 1){
    	    self::increment($wat, $sampleRate);
    	}
    	public function decrement($wat, $sampleRate = 1){
    	    self::decrement($wat, $sampleRate);
    	}
    	public function update($wat, $delta=1, $sampleRate=1){
    	    self::updateStats($wat, $delta, $sampleRate);
    	}
    	
        /**
        * Log timing information
        *
        * @param string $stats The metric to in log timing info for.
        * @param float $time The ellapsed time (ms) to log
        * @param float|1 $sampleRate the rate (0-1) for sampling.
        **/
        private static function timeinfo($stat, $time, $sampleRate=1) {
            self::send(array($stat => "$time|ms"), $sampleRate);
        }
        
         /**
        * Increments one or more stats counters
        *
        * @param string|array $stats The metric(s) to increment.
        * @param float|1 $sampleRate the rate (0-1) for sampling.
        * @return boolean
        **/
        private static function inc($stats, $sampleRate=1) {
            self::updateStats($stats, 1, $sampleRate);
        }

        /**
        * Decrements one or more stats counters.
        *
        * @param string|array $stats The metric(s) to decrement.
        * @param float|1 $sampleRate the rate (0-1) for sampling.
        * @return boolean
        **/
        private static function dec($stats, $sampleRate=1) {
            self::updateStats($stats, -1, $sampleRate);
        }

        /**
        * Updates one or more stats counters by arbitrary amounts.
        *
        * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
        * @param int|1 $delta The amount to increment/decrement each metric by.
        * @param float|1 $sampleRate the rate (0-1) for sampling.
        * @return boolean
        **/
        public static function updateStats($stats, $delta=1, $sampleRate=1) {
            if (!is_array($stats)) { $stats = array($stats); }
            $data = array();
            foreach($stats as $stat) {
                $data[$stat] = "$delta|c";
            }
            
            self::send($data, $sampleRate);
        }
        
        /*
        * Squirt the metrics over UDP
	        ^haha squirt
        **/
        private static function send($data, $sampleRate=1) {
            if (! self::getEnabled()) { return; }

            // sampling
            $sampledData = array();

            if ($sampleRate < 1) {
                foreach ($data as $stat => $value) {
                    if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
                        $sampledData[$stat] = "$value|@$sampleRate";
                    }
                }
            } else {
                $sampledData = $data;
            }

            if (empty($sampledData)) { return; }

            // Wrap this in a try/catch - failures in any of this should be silently ignored
            try {
                $host = $this->config->host;
                $port = $this->config->port;
                $fp = fsockopen("udp://$host", $port, $errno, $errstr);
                if (! $fp) { return; }
                foreach ($sampledData as $stat => $value) {
                    fwrite($fp, "$stat:$value");
                }
                fclose($fp);
            } catch (Exception $e) {
            }
        }
    }

?>
