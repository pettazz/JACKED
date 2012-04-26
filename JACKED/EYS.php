<?php
    
    //The Electronic Yardstick (say: "eyes")
    //Drop named marks wherever you want, which record both time and memory allocation,
    // get on-demand deltas between any marks.

    class EYS extends JACKEDModule{
        const moduleName = 'EYS';
        const moduleVersion = 1.0;

        private $marks = array();
        private $mem_active = true;

        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);

            //check if we have access to the memory_get_usage function here so we don't
            //have to do it every time
            $this->mem_active = function_exists('memory_get_usage');
        }

        /**
        * Wrapper for getting the current memory usage, to avoid lots of inline ternaries
        * 
        * @param $rounding int [optional] Number of decimal places to round results to. Defaults to 4.
        * @param $scale int [optional] Number of times to divide bytes by 1024. 1 = KB, 2 = MB, etc. Defaults to 0 (bytes).
        * @return float The current memory usage (in bytes or requested $scale), or 0 if we can't call memory_get_usage.
        */
        private function getMemoryUsage($rounding = 4, $scale = 0){
            if($this->mem_active){
                $mem = round(memory_get_usage() / (($scale > 0)? 1024 * $scale : 1), $rounding);
            }else{
                $mem = 0;
            }

            return $mem;
        }

        /**
        * Create and save a mark with the given name for use in later comparisons
        * 
        * @param $name String Name of the mark to create
        */
        public function setMark($name){
            $memory = $this->getMemoryUsage();
            $time = microtime(true);
            $this->marks[$name] = array('memory' => $memory, 'time' => $time);
        }

        /**
        * Get the value of a stored mark by its name and optional values
        * 
        * @param $name String Name of the mark to get
        * @param $values Array [optional] List of which stored values to get for this mark. Defaults to all.
        * @return Array The requested values
        */
        public function getMark($name, $values = false){
            $retval = array();
            if($values){
                foreach($values as $key){
                    $retval[$key] = $this->marks[$name][$key];
                }
            }else{
                $retval = $this->marks[$name];
            }

            return $retval;
        }

        /**
        * Calculate the change between given values for two given marks. Calculated as $name2 - $name1.
        * 
        * @param $name1 String The name of the first mark
        * @param $name2 String The name of the second mark
        * @param $values Array [optional] List of which values to calculate for. Defaults to all.
        * @return Array The deltas for the given values. May show positive or negative (including time if $name1 was recorded after $name2).
        */
        public function getDelta($name1, $name2, $values = false){
            $retval = array();
            $one = $this->getMark($name1, $values);
            $two = $this->getMark($name2, $values);
            foreach($one as $key){
                $retval[$key] = $one[$key] - $two[$key];
            }

            return $retval;
        }

    }
?>
