<?php

    class MySQL extends JACKEDModule{
        const moduleName = 'MySQL';
        const moduleVersion = 2.5;
        const dependencies = '';
        const optionalDependencies = '';
        
        private $mysql_link = NULL;
        
        public function __destruct(){
            if($this->isLinkOpen()){
                mysql_close($this->mysql_link);
                $this->mysql_link = NULL;
            }
        }
        
        //LOOK ITS STUFF TO MAKEHAS WORKING

        /**
        * Checks if the MySQL link is open.
        * 
        * @param $link int [optional] MySQL Link ID to check. Defaults to default link.
        * @return Boolean Whether the link is active.
        */
        private function isLinkOpen($link = NULL){
            $link = $link? $link : $this->mysql_link;
            return ($this->mysql_link == NULL)? false : true;
        }
        
        /**
        * Opens a new link to MySQL. If $setDefault is true and link creation fails, the module is disabled.
        * 
        * @param $setDefault Boolean [optional] Whether to make the new link the default link. Defaults to true.
        * @return int MySQL Link ID that was just opened.
        */
        private function openLink($setDefault = true){
            try{
                $link = mysql_connect($this->config->db_host, $this->config->db_user, $this->config->db_pass);
                mysql_select_db($this->config->db_name);
                if($setDefault){
                    $this->mysql_link = $link;
                }
                return $link;
            }catch(Exception $e){
                if($setDefault){
                    self::$isModuleEnabled = false;
                }
                throw $e;
            }
        }
        
        /**
        * Returns the default link. If it's not open, opens it then returns the link.
        * 
        * @return int The default MySQL Link ID.
        */
        private function getLink(){
            if($this->isLinkOpen()){
                return $this->mysql_link;
            }else{
                return $this->openLink();
            }
        }
        
        /////////////////////////////
        //actual public mysql stuff//
        /////////////////////////////
        
        /**
        * Sanitize a string to be safe for use in MySQL queries.
        * TODO: add even better sanitization
        * 
        * @param $value String Value to sanitize.
        * @param $link int [optional] MySQL Link ID to use. Defaults to default link. Opens new default if necessary.
        * @return String Sanitized version of the input string.
        */
        public function sanitize($value, $link = NULL){
            $link = $link? $link : $this->getLink();
            return mysql_real_escape_string($value, $link);
        }

        /**
        * Get the MySQL LIMIT clause to use for paginating a query.
        * 
        * @param $rows int Number of rows/values per page.
        * @param $page int Page number to get values for.
        * @return String MySQL LIMIT clause.
        */
        public function paginator($howMany, $page){
            return " LIMIT " . ($howMany * ($page - 1)) . ", " . $howMany;
        }
        
        /**
        * Create a string of comma separated field names suitable for MySQL SELECT based
        * on a given Array of string fields. Takes an optional Array of allowed field 
        * strings to filter input against.
        * 
        * @param $fields Array String fields to create fieldstring from.
        * @param $allowedFields Array [optional] List of only String fields allowed in output.
        * @return String MySQL LIMIT clause.
        */
        public function getFieldString($fields, $allowedFields = false){
            if($allowedFields && !empty($allowedFields)){
                if(!empty($fields)){
                    $fields = array_filter($fields, function ($var) use ($allowedFields){
                        return in_array($var, $allowedFields);
                    });
                }
            }
            if(empty($fields)){
                $fieldstring = "*";
            }else{
                $fieldstring = implode(", ", $fields);
            }
        
            return $fieldstring;
        }
        
        /**
        * Parse a given MySQL Resource ID into an associative array of the given type.
        * 
        * @param $result int MySQL Resource ID to parse.
        * @param $result_type int [optional] One of: MYSQL_ASSOC, MYSQL_NUM, or MYSQL_BOTH (default).
        * @return Array Result data parsed into an associative array.
        */
        public function parseResult($result, $result_type = MYSQL_BOTH){
           $done = array();
            if($result){
                while($row = mysql_fetch_array($result, $result_type)){
                    $done[] = $row;
                }
            }
            if(count($done) == 1)
                $done = $done[0];
            return $done;
        }
        
        //SELECT val FROM table WHERE cond
        //val is just one field, and you only get the first result
        ////default link can be overridden
        public function getVal($val, $table, $cond = null, $link = NULL){
            $link = $link? $link : $this->getLink();
            if(stripos($val, "function:") === 0){
                $val = substr($val, 9); //function: ends at 9, lol.
                $query = "SELECT " . $val . " FROM `" . $table . "`";
            }else
                $query = "SELECT `" . $val . "` FROM `" . $table . "`";
            if($cond)
                $query .= " WHERE " . $cond;
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            
            if($result && mysql_num_rows($result) > 0){
                $row = mysql_fetch_array($result, MYSQL_NUM);
                $final = stripslashes($row[0]);
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        //SELECT vals FROM table WHERE cond
        ////default link can be overridden
        public function getRowVals($vals, $table, $cond, $result_type = MYSQL_BOTH, $link = NULL){
            $link = $link? $link : $this->getLink();
            $query = "SELECT $vals FROM `" . $table . "` WHERE " . $cond;
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            $row = mysql_fetch_array($result, $result_type);
            
            if($result && mysql_num_rows($result) > 0){
                $final = array_map("stripslashes", $row);
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        //SELECT * FROM table WHERE cond
        ////default link can be overridden
        public function getRow($table, $cond, $result_type = MYSQL_BOTH, $link = NULL){
            $link = $link? $link : $this->getLink();
            $query = "SELECT * FROM `" . $table . "` WHERE " . $cond;
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            
            if($result && mysql_num_rows($result) > 0){
                $row = mysql_fetch_array($result, $result_type);
                $final = array_map("stripslashes", $row);
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        //SELECT vals FROM table WHERE cond
        //vals is an array of field names
        //returns an array of vals
        ////default link can be overridden
        public function getAllVals($vals, $table, $cond, $link = NULL){
            $link = $link? $link : $this->getLink();
            if(is_array($vals)){
                $query = "SELECT " . implode(",", $vals) . " FROM `" . $table . "` WHERE " . $cond;
            }else{
                $query = "SELECT * FROM `" . $table . "` WHERE " . $cond;
            }
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            if($result && mysql_num_rows($result) > 0){
                $final = array();
                if(is_array($vals)){
                    while($row = mysql_fetch_array($result, MYSQL_ASSOC)){		
                        $newrow = array();
                        foreach($vals as $fieldname){
                            $newrow[$fieldname] = stripslashes($row[$fieldname]);
                        }
                        $final[] = $newrow;
                    }
                }else{
                    while($row = mysql_fetch_array($result, MYSQL_ASSOC)){		
                        $newrow = array();
                        foreach($row as $fieldname => $value){
                            $newrow[$fieldname] = stripslashes($row[$fieldname]);
                        }
                        $final[] = $newrow;
                    }
                }
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        //SELECT * FROM table WHERE cond
        ////default link can be overridden
        ////returns array of all rows
        public function getRows($table, $cond = NULL, $link = NULL){
            $link = $link? $link : $this->getLink();
            $cond = $cond? $cond : '1';
            $query = "SELECT * FROM `" . $table . "` WHERE " . $cond;
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            
            if($result && mysql_num_rows($result) > 0){
                $final = array();
                while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
                    $final[] = $row;
                }
                mysql_free_result($result);
            }else
                $final = false;
            
            return $final;
        }
        
        //SELECT * FROM table WHERE cond
        ////default link can be overridden
        ////returns the result
        public function getResult($table, $cond, $link = NULL){
            $link = $link? $link : $this->getLink();
            $query = "SELECT * FROM `" . $table . "` WHERE " . $cond;
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            
            if($result && mysql_num_rows($result) > 0){
                $final = $result;
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        //make does do a query!
        public function query($query, $link = NULL){
            $link = $link? $link : $this->getLink();
            JACKED::debug_dump($query);
            return mysql_query($query, $link);
        }
        
        //INSERT INTO table (fields) VALUES (values)
        ///$data is an associative array where $field=>$value
        ////default link can be overridden
        ////returns bool whether it worked
        public function insertValues($table, $data, $link = NULL){
            $link = $link? $link : $this->getLink();
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                $fields[] = $this->sanitize($field, $link);
                $values[] = $this->sanitize($value, $link);
            }
            $query = "INSERT INTO $table (`" . implode($fields, '`, `') . "`) VALUES ('" . implode($values, '\', \'') . "')";
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            if($result){
                $done = mysql_insert_id($link);
            }else{
                $done = false;
            }
            return $done;
        }
        
        //UPDATE table SET field1 = value1, ... fieldn = value1 WHERE cond
        ///$data is an associative array where $field=>$value
        ////default link can be overridden
        ////returns bool whether it worked
        public function update($table, $data, $cond, $link = NULL){
            $link = $link? $link : $this->getLink();
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                if(stripos($value, "literal:") === 0){
                    $pairs[] = "`" . $this->sanitize($field, $link) . "` = " . $this->sanitize(substr($value, 8), $link) . "";
                }else{
                    $pairs[] = "`" . $this->sanitize($field, $link) . "` = '" . $this->sanitize($value, $link) . "'";
                }
            }
            
            $query = "UPDATE $table SET " . implode($pairs, ', ') . " WHERE " . $cond;
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            return $result;
        }
        
        //REPLACE INTO table (field1, ... fieldn) VALUES (value1, ... value1)
        ///$data is an associative array where $field=>$value
        ////default link can be overridden
        ////returns bool whether it worked
        public function replace($table, $data, $link = NULL){
            $link = $link? $link : $this->getLink();
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                $fields[] = $this->sanitize($field, $link);
                $values[] = $this->sanitize($value, $link);
            }
            
            $query = "REPLACE INTO $table (`" . implode($fields, '`, `') . "`) VALUES ('" . implode($values, '\', \'') . "')";
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            return $result;
        }
        
        //DELETE FROM table WHERE cond
        ////default link can be overridden
        ////returns bool whether it worked
        public function delete($table, $cond, $link = NULL){
            $link = $link? $link : $this->getLink();
            $query = 'DELETE FROM ' . $this->sanitize($table) . ' WHERE ' . $cond;
            JACKED::debug_dump($query);
            $result = mysql_query($query, $link);
            return $result;
        }
        
        //get the last MySQL error
        public function getError($link = NULL){
            $link = $link? $link : $this->getLink();
            return mysql_error($link);
        }
    }

?>
