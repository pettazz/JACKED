<?php

    class Logr extends JACKEDModule{
        const moduleName = 'Logr';
        const moduleVersion = 1.0;
        const dependencies = '';
        const optionalDependencies = '';
        
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
                        }catch(Exception $e){
                            self::printMessage('<strong>Error configuring log file: </strong>' . $e->getMessage())
                        }
                        break;
                    // case 'MySQL':
                    //     try{
                    //         $JACKED->loadDependency('MySQL');
                    //     }catch(Exception $e){
                    //        
                    //     }
                    //     break;
                    default:
                        //stderr? or whatever
                        break;
                }
            }
        }
        
        public function __destruct(){
            /*try{
                $this->M->close();
            }catch(Exception $e){}*/
        }

        public static function printMessage($msg){
            list($callee) = debug_backtrace();
            echo '<fieldset style="background: #fefefe !important; border:2px red solid; padding:5px">';
            echo '<legend style="background:lightgrey; padding:5px;">'.$callee['file'].' @ line: '.$callee['line'].'</legend><pre>';

            echo '<br />'. $msg;

            echo "</pre>";
            echo "</fieldset>";
        }

        public static function full_dump(){
            list($callee) = debug_backtrace();
            $arguments = func_get_args();
            $total_arguments = count($arguments);

            echo '<fieldset style="background: #fefefe !important; border:2px red solid; padding:5px">';
            echo '<legend style="background:lightgrey; padding:5px;">'.$callee['file'].' @ line: '.$callee['line'].'</legend><pre>';
            $i = 0;
            foreach ($arguments as $argument)
            {
                echo '<br/><strong>Debug #'.(++$i).' of '.$total_arguments.'</strong>: ';
                print_r($argument);
            }

            echo "</pre>";
            echo "</fieldset>";
        }
        
        /**
        * Set the value of a new key in the cache. Will not overwrite existing keys with the same name.
        * 
        * @param $key String The key to store the value as
        * @param $value Mixed The value to store in the cache
        * @param $timeout int [optional] Set the seconds expiration on this value in the cache (defaults to module config value)
        * @return Boolean Whether the value was set successfully, or False if the key already exists
        */

?>