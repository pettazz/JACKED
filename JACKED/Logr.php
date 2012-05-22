<?php

    class Logr extends JACKEDModule{
        const moduleName = 'Logr';
        const moduleVersion = 1.0;

        const LEVEL_FATAL = 4;        
        const LEVEL_SEVERE = 3;
        const LEVEL_WARNING = 2;
        const LEVEL_NOTICE = 1;
        const LEVEL_LOL = 0;
        
        private $locations = array();

        private $logfp;
        private $lognl = "\n";

        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);

            //set up debug
            switch($JACKED->config->debug){
                case 1:
                    ini_set('display_errors', 'On');
                    error_reporting(E_ALL ^ E_NOTICE);
                    break;
                case 2:
                    ini_set('display_errors', 'On');
                    error_reporting(-1);
                    break;
                case -1:
                    ini_set('display_errors', 'On');
                    error_reporting(E_ALL ^ E_NOTICE);
                    break;
                case -2:
                    ini_set('display_errors', 'On');
                    error_reporting(-1);
                    break;
        
                default:
                    ini_set('display_errors', 'Off');
                    ini_set('log_errors', 'On');
                    error_reporting(E_ALL ^ E_NOTICE);
                    break;
            }

            foreach($this->config->locations as $loc => $data){
                switch($loc){
                    case 'file':
                        try{
                            if(file_exists($data)){
                                $opened_date = date('Ymd', filemtime($data));
                                if(date('Ymd') != $opened_date){
                                    rename($data, $data . '.' . $opened_date . '.log');
                                }
                            }

                            $this->logfp = fopen($data, 'a');
                            if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
                                $this->lognl = "\r\n";
                            }
                            $this->locations[] = 'file';
                            fwrite($this->logfp, 'Logfile opened. Sup?' . $this->lognl);
                        }catch(Exception $e){
                            $this->write('Error configuring log file: ' . $e->getMessage());
                        }
                        break;
                    case 'MySQL':
                        try{
                            $this->JACKED->loadDependencies(array('MySQL'));
                            $this->locations[] = 'MySQL';
                        }catch(Exception $e){
                            $this->write('Error configuring MySQL logging: ' . $e->getMessage());
                        }
                        break;
                    case 'stdout':
                        $this->locations[] = 'stdout';
                        break;
                }
            }
            //If core debug is enabled, include stdout logging
            if($JACKED->config->debug > 0){
                $this->locations[] = 'stdout';
            }
        }
        
        public function __destruct(){
            foreach($this->locations as $loc){
                switch($loc){
                    case 'file':
                        try{
                            fwrite($this->logfp, 'Logfile closed. Bye!' . $this->lognl . $this->lognl);
                            fclose($this->logfp);
                        }catch(Exception $e){}
                        break;
                    case 'MySQL':
                        try{
                            // nothing to do here
                            // ...for now
                        }catch(Exception $e){}
                        break;
                }
            }
            $this->locations = array();
        }

        /**
        * Translates an error level to a human readable string.
        * 
        * @param $level Int The error level to be converted.
        * @return String Human readable representation of log level
        */
        public static function levelName($level){
            /*
                const LEVEL_FATAL = 4;        
                const LEVEL_SEVERE = 3;
                const LEVEL_WARNING = 2;
                const LEVEL_NOTICE = 1;
                const LEVEL_LOL = 0;
            */
            $str = '';
            switch($level){
                case self::LEVEL_FATAL:
                    $str = 'fatal';
                    break;
                case self::LEVEL_SEVERE:
                    $str = 'severe';
                    break;
                case self::LEVEL_WARNING:
                    $str = 'warning';
                    break;
                case self::LEVEL_NOTICE:
                    $str = 'notice';
                    break;
                case self::LEVEL_LOL:
                    $str = 'lol';
                    break;
                default:
                    $str = 'notice';
                    break;
            }

            return $str;
        }

        /**
        * Prints a given message to the output (most commonly this will be inline on the page being rendered)
        * 
        * @param $msg String The message value to print (preformatted)
        * @param $callee Array The callee data for printing the header. Defaults to list($callee) = debug_backtrace();
        * @param $level Int [optional] The error level of the message to print. One of:  LEVEL_FATAL, LEVEL_SEVERE, LEVEL_WARNING, LEVEL_NOTICE (default), LEVEL_LOL
        */
        public static function printMessage($msg, $callee = NULL, $level = 1){
            if($callee == NULL){
                list($callee) = debug_backtrace();
            }

            //set some style stuff for different levels
            switch($level){
                case 0:
                    $color = "3366FF";
                    $label = "LOL: ";
                    break;
                case 1:
                    $color = "0000FF";
                    $label = "Notice: ";
                    break;
                case 2:
                    $color = "CC6600";
                    $label = "Warning: ";
                    break;
                case 3:
                    $color = "FF3300";
                    $label = "Severe: ";
                    break;
                case 4:
                    $color = "FF0000";
                    $label = "Fatal: ";
                    break;
            }

            echo '<fieldset style="background: #fefefe !important; border:2px ' . $color . ' solid; padding:5px">';
            try{
                echo '<legend style="background:lightgrey; padding:5px;">'. $label . $callee['file'] . ' @ line: ' . $callee['line'] . '</legend>';
            }catch(Exception $e){
                echo '<legend style="background:lightgrey; padding:5px;">Logr Message</legend>';
            }
            echo '<pre><br />'. $msg;

            echo "</pre>";
            echo "</fieldset>";
        }

        /**
        * Prints a given set of values to the output (see printMessage) in a human readable way (print_r style).
        * Magically gets any number of arguments passed to it of Mixed types. 
        */
        public static function printDump(){
            list($callee) = debug_backtrace();
            $arguments = func_get_args();
            $total_arguments = count($arguments);

            $i = 0;
            $msg = '';
            foreach ($arguments as $argument)
            {
                $msg .= '<br/><strong>Debug #'.(++$i).' of '.$total_arguments.'</strong>: ';
                $msg .= print_r($argument, true);
            }

            $this->printMessage($msg, $callee);
        }
        
        /**
        * Write to any configured/active logs.
        * 
        * @param $msg String The message to write to the log
        * @param $level Int [optional] The error level. One of:  LEVEL_FATAL, LEVEL_SEVERE, LEVEL_WARNING, LEVEL_NOTICE (default), LEVEL_LOL
        * @param $stacktrace Array [optional] Output of the debug_backtrace relevant to logging the error, defaults to currently generated
        * @param $skipLocation String [optional] A specific log location to ignore in this write (use to stop infinite recursion)
        */
        public function write($msg, $level = 1, $stacktrace = NULL, $skipLocation = NULL){
            if($stacktrace == NULL){
                $stacktrace = debug_backtrace();
            }
            
            foreach($this->locations as $loc){
                switch($loc){
                    case 'file':
                        if($skipLocation != 'file'){
                            try{
                                $time = date('r');
                                fwrite($this->logfp, '[' . microtime(true) . ' - ' . $time . '] [' . strtoupper(self::levelName($level)) . '] ' . $msg . $this->lognl);
                            }catch(Exception $e){
                                $bt = $e->getTrace();
                                $this->write('Error writing to log file: ' . $e->getMessage(), Logr::LEVEL_WARNING, $bt, 'file');
                            }
                        }
                        break;
                    case 'MySQL':
                        if($skipLocation != 'MySQL'){
                            try{
                                $this->JACKED->MySQL->insert($this->config->locations['MySQL'], array(
                                    'guid' => $this->JACKED->Util->uuid4(),
                                    'timestamp' => microtime(true),
                                    'message' => $msg,
                                    'file' => array_key_exists('file', $stacktrace[0])? $stacktrace[0]['file'] : null,
                                    'line' => array_key_exists('line', $stacktrace[0])? $stacktrace[0]['line'] : null,
                                    'stack_hash' => md5(print_r($stacktrace[0], true))
                                ));
                            }catch(Exception $e){
                                $bt = $e->getTrace();
                                $this->write('Error writing to log table: ' . $e->getMessage(), Logr::LEVEL_WARNING, $bt, 'MySQL');
                            }
                        }
                        break;
                    case 'stdout':
                        if($skipLocation != 'stdout'){
                            self::printMessage($msg, $stacktrace[0]);
                        }
                        break;
                }
            }
            //if global debug is on and we haven't already printed this
            if($this->JACKED->config->debug > 0 && !in_array('stdout', $this->locations)){
                self::printMessage($msg, $stacktrace[0]);
            }
        }
    }
?>