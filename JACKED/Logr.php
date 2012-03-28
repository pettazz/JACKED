<?php

    class Logr extends JACKEDModule{
        const moduleName = 'Logr';
        const moduleVersion = 1.0;
        const dependencies = '';
        const optionalDependencies = '';

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

            foreach($this->config->locations as $loc => $data){
                switch($loc){
                    case 'file':
                        try{
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
                            $this->JACKED->loadDependencies('MySQL');
                            if(mysql_num_rows($this->JACKED->MySQL->query('show tables like "' . $data . '"')) == 0){
                                throw new Exception("Logging table '$data' not found.");
                            }
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
        */
        public static function printMessage($msg, $callee = NULL){
            if($callee == NULL){
                list($callee) = debug_backtrace();
            }
            echo '<fieldset style="background: #fefefe !important; border:2px red solid; padding:5px">';
            echo '<legend style="background:lightgrey; padding:5px;">'.$callee['file'].' @ line: '.$callee['line'].'</legend><pre>';

            echo '<br />'. $msg;

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
        */
        public function write($msg, $level = 1, $stacktrace = NULL){
            if($stacktrace == NULL){
                $stacktrace = debug_backtrace();
            }
            foreach($this->locations as $loc){
                switch($loc){
                    case 'file':
                        try{
                            $time = date('r');
                            fwrite($this->logfp, '[' . microtime() . ' - ' . $time . '] [' . strtoupper(self::levelName($level)) . '] ' . $msg . $this->lognl);
                        }catch(Exception $e){
                            if($this->JACKED->config->debug > 0){
                                $bt = $e->getTrace();
                                self::printMessage('Error writing to log file: ' . $e->getMessage(), $bt[0]);
                            }
                        }
                        break;
                    case 'MySQL':
                        try{
                            // nothing to do here
                            // ...for now
                        }catch(Exception $e){
                            //dunno yet
                        }
                        break;
                    case 'stdout':
                        self::printMessage($msg, $stacktrace);
                        break;
                    default:
                        if($this->JACKED->config->debug > 0){
                            self::printMessage($msg, $stacktrace);
                        }
                }
            }
        }
    }
?>