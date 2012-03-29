<?php
    //autoload classes from modules folder when they're called
    ////BE CAREFUL because this will only autoload Modules, not libs or anything else
    spl_autoload_register(function($class){
        $did = false;
        $file = JACKED_MODULES_ROOT . $class . '.php';
        if (file_exists($file)){
            require($file);
            $did = true;
        }else{
            throw new Exception("JACKED can't find a class for the module named " . $class . ".");
        }
        return $did;
    }); 

    //THE fucking class
    class JACKED{
        const moduleName = "JACKED Core";
        const moduleVersion = 3.0;
    
        protected static $_instance = null;
        public $config;
    
        public function __construct($required="", $optional=""){
            self::$_instance = $this;
            self::$_instance->config = new Configur("core");
            //set up debug
            switch(self::$_instance->config->debug){
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
            
            //load util and logging 
            self::$_instance->loadDependencies('Logr');

            self::$_instance->loadDependencies($required);
            self::$_instance->loadOptionalDependencies($optional);
        }
    
        public static function getInstance($required = "", $optional = ""){
            if (self::$_instance === null) {
                self::$_instance = new JACKED();
            }

            self::$_instance->loadDependencies($required);
            self::$_instance->loadOptionalDependencies($optional);
            
            return self::$_instance;
        }
        
        private function isModuleRegistered($name){
            return property_exists(self::$_instance, $name);
        }
        
        private function registerModule($name){
            if($name && !self::$_instance->isModuleRegistered($name))
                self::$_instance->$name = new $name(self::$_instance);
        }
    
        public function loadDependencies($deps){
            foreach(explode(", ", $deps) as $module){
                if(!self::$_instance->isModuleRegistered($module)){
                    try{
                        self::$_instance->registerModule($module);
                    }catch(Exception $e){
                        die('<br />Required module <strong>' . $module . '</strong> couldn\'t be loaded.<br />' . $e->getMessage());
                    }
                }
            }
        }
        
        public function loadOptionalDependencies($mods){
            foreach(explode(", ", $mods) as $module){
                if(!self::$_instance->isModuleRegistered($module)){
                    try{
                        self::$_instance->registerModule($module);
                    }catch(Exception $e){
                        self::$_instance->$module = new Derper();
                        self::$_instance->debug_dump('Optional module <strong>' . $module . '</strong> couldn\'t be loaded.<br />' . $e->getMessage());
                    }
                }
            }
        }
        
        public function importLib($libname){
            //this could certainly be better, but it works for now
            ////for now we'll assume every lib is a single class in a same name .php
            if(!class_exists($libname, FALSE)){ //definitely needs to be better
                $did = false;
                $file = JACKED_LIB_ROOT . $libname . '.php';
                if (file_exists($file)){
                    require($file);
                    $did = true;
                }else{
                    throw new Exception("JACKED can't find a library named " . $libname . ".");
                }
                return $did;
            }else{
                return true;
            }
        }
        
        public function __destruct(){
            //unload?
        }
        
        
        
        ///////////
        //    util   //
              //////////
        //will be its own module when it grows up 
              
        //Recursive array_key_exists, handles nested arrays or objects
        public function array_key_exists_r($needle, $haystack){
            $result = array_key_exists($needle, $haystack);
            if ($result)
                return $result;
            foreach ($haystack as $v)
            {
                if (is_array($v) || is_object($v))
                    $result = array_key_exists_r($needle, $v);
                if ($result)
                    return $result;
            }
            return $result;
        }
        
        //function to strip crap from comments
        public function html2txt($document){
            $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
               '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
               '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
               '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
            );
            $text = preg_replace($search, '', $document);
            return $text;
        }
        
        //emulate strstr()'s before_needle arg in php v < 5.3
        //$h = haystack, $n = needle lol
        public function strstrb($h, $n){
            return array_shift(explode($n,$h,2));
        }
        
        /**
         * Convert BR tags to nl
         *
         * @param string The string to convert
         * @return string The converted string
         */
        public function br2nl($string)
        {
            return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
        }
        
        public function hashPassword($string){
            self::$_instance->importLib('PasswordHash');
            // if($string == 'hunter2'){
            //     self::log('I just copy pasted YOUR ******\'s and it appears to YOU as hunter2 cause its your pw', );
            // }
            $hasher = new PasswordHash(8, FALSE);
            return $hasher->HashPassword($string);
        }
        
        public function checkPassword($string, $someHash){
            self::$_instance->importLib('PasswordHash');            
            $hasher = new PasswordHash(8, FALSE);
            return $hasher->CheckPassword($string, $someHash);
        }

        //http://www.php.net/manual/en/function.uniqid.php#94959
        public function uuid4(){
            return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

                // 16 bits for "time_mid"
                mt_rand( 0, 0xffff ),

                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand( 0, 0x0fff ) | 0x4000,

                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand( 0, 0x3fff ) | 0x8000,

                // 48 bits for "node"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            );
        }
        
        
        ////////////////////////////////////////////
        //    motherfuckin debuggery
        ////////////////////////////////////////////
        
        //all of this is now officially deprecated

        private static function print_repr($title, $var){
            echo '<br /><font color="red">' . $title . ':</font><br /><pre><code>';
            echo print_r($var, true);
            echo '</code></pre><br />';
        }
        
        //not yet
        private static function log_repr($title, $var){
            //lol
        }
        
        //dumps a given var, if conf->debug is not turned off
        public function debug_dump($var){
            $this->Logr->write($var, 1, debug_backtrace());
            /*if(self::$_instance->config->debug > 0){
                self::print_repr('DEBUG DUMP', $var);
            }*/
        }
        
        //prints a backtrace
        public static function backtrace(){
            if(self::$_instance->config->debug > 0){
                self::print_repr('DEBUG BACKTRACE', debug_backtrace());
            }
        }
        
        //whoahdude
        public function full_dump(){
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



        //harp darp
        public function derp(){
            //here there be a space for testing
        }
    }
?>
