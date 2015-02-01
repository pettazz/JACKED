<?php

    class Util extends JACKEDModule{
        const moduleName = 'Util';
        const moduleVersion = 1.0;

        /**
        * Validates authenticity of an email address
        * 
        * @param string $email The email address to validate.
        * @return bool Whether $email is valid
        */
        public static function validateEmail($email)
        {
            return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
        }

        /**
        * Recursive array_key_exists, handles nested arrays or objects
        * 
        * @param Mixed $needle Key to search for within the object or array $haystack 
        * @param Mixed $haystack Array or Object within which to search for a key $needle
        * @return bool Whether the $needle exists as a key in $haystack
        */
        public static function array_key_exists_recursive($needle, $haystack){
            $result = array_key_exists($needle, $haystack);
            if($result){
                return $result;
            }
            foreach($haystack as $v){
                if(is_array($v) || is_object($v)){
                    $result = self::array_key_exists_recursive($needle, $v);
                }
                if($result){
                    return $result;
                }
            }
            return $result;
        }

        /**
        * Recursive array_keys, handles nested arrays or objects
        * 
        * @param Mixed $haystack Array or Object to get all keys of
        * @return array Every array key within the multidimensional $haystack
        */
        public static function array_keys_recursive($haystack){
            $keys = array_keys($haystack);
            foreach($haystack as $v){
                if(is_array($v) || is_object($v)){
                    $keys = array_unique(array_merge($keys, self::array_keys_recursive($v)));
                }
            }
            return $keys;
        }

        /**
         * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
         * keys to arrays rather than overwriting the value in the first array with the duplicate
         * value in the second array, as array_merge does. I.e., with array_merge_recursive,
         * this happens (documented behavior):
         *
         * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
         *     => array('key' => array('org value', 'new value'));
         *
         * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
         * Matching keys' values in the second array overwrite those in the first array, as is the
         * case with array_merge, i.e.:
         *
         * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
         *     => array('key' => array('new value'));
         *
         * Parameters are passed by reference, though only for performance reasons. They're not
         * altered by this function.
         *
         * @param array $array1
         * @param array $array2
         * @return array
         * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
         * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
         */
        function array_merge_recursive_distinct(array &$array1, array &$array2){
            $merged = $array1; 

            foreach ($array2 as $key => &$value){
                if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
                    $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
                }else{
                    $merged[$key] = $value;
                }
            }

            return $merged;
        }
        
        /**
        * Strips HTML, javascript, comments, and other things we wouldn't want to 
        * to accept in user input like comments from a string.
        * 
        * @param string $string The string to clean up
        * @return string The stripped/cleaned string
        */
        public static function html2txt($string){
            $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
               '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
               '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
               '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
            );
            $text = preg_replace($search, '', $string);
            return $text;
        }
        
        /**
        * Emulate strstr()'s before_needle arg in php v < 5.3
        * Returns part of haystack string starting from and including 
        * the first occurrence of needle to the end of haystack.
        * 
        * @param string $haystack The input string.
        * @param Mixed $needle If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
        * @return Mixed Returns the portion of string, or FALSE if needle is not found.
        */
        public static function strstrb($haystack, $needle){
            return array_shift(explode($needle, $haystack, 2));
        }
        
        /**
         * Convert BR tags to nl
         *
         * @param string $string The string to convert
         * @return string The converted string
         */
        public static function br2nl($string){
            return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
        }

        /**
         * Generate a random string of given length using the given charset. Charset defaults to valid hex chars.
         *
         * @param $length int The desired length of the random string.
         * @param $charset STring The set of characters to select from for each character of the random string.
         * @return string The generated string
         */
        function randomString($length, $charset = 'abcdef0123456789'){
            $result = '';
            $max = strlen($charset) - 1;
            while($length--){
                $result .= $charset[mt_rand(0, $max)];
            }
            return $result;
        }

        /**
         * Generates pseudo-random VALID RFC 4211 COMPLIANT Universally Unique IDentifiers (UUID) version 4.
         * As found here: http://www.php.net/manual/en/function.uniqid.php#94959
         * 
         * @param $dashes bool Whether to include dashes in the generated uuid string. Default: true.
         * @return string The generated UUID
         */
        public static function uuid4($dashes = true){
            $str = ($dashes)? '%04x%04x-%04x-%04x-%04x-%04x%04x%04x' : '%04x%04x%04x%04x%04x%04x%04x%04x';
            
            return sprintf($str,
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

        /**
         * Hash a given string using the included hashlib into something presumably safe to store.
         *
         * @param string $string The string to hash
         * @return string The hashed string
         */
        public function hashPassword($string){
            if(function_exists('password_hash')){
                return password_hash($string, PASSWORD_DEFAULT);
            }else{
                $this->JACKED->loadLibrary('PasswordHash');
                $hasher = new PasswordHash(8, FALSE);
                return $hasher->HashPassword($string);
            }
        }

        /**
         * Determine whether a given hash matches the hash of a given string using the included hashlib.
         *
         * @param string $string The string to check against $someHash
         * @param string $someHash The hash to check $string against
         * @return bool Whether the hash of $string exactly matches the given $someHash
         */
        public function checkPassword($string, $someHash){
            if(function_exists('password_verify')){
                return password_verify($string, $someHash);
            }else{
                $this->JACKED->loadLibrary('PasswordHash');            
                $hasher = new PasswordHash(8, FALSE);
                return $hasher->CheckPassword($string, $someHash);
            }
        }

    }
?>