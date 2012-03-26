<?php

    class MySQL extends JACKEDModule{
        const moduleName = 'MySQL';
        const moduleVersion = 2.7;
        const dependencies = '';
        const optionalDependencies = 'Memcacher';
        
        private $mysql_link = NULL;
        
        public function __destruct(){
            if($this->isLinkOpen()){
                mysql_close($this->mysql_link);
                $this->mysql_link = NULL;
            }
        }

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
                    $this->isModuleEnabled = false;
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
            return mysql_real_escape_string(stripslashes($value), $link);
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

        /**
        * Get the string value of the last MySQL error on the given link.
        * 
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @return String Last error message from the database connection identified by the link
        */
        public function getError($link = NULL){
            $link = $link? $link : $this->getLink();
            $err = mysql_error($link);
            JACKED::debug_dump($err);
            return $err;
        }

        /**
        * Perform a MySQL query on the given database connection, and return the result identifier. If memcache is 
        * enabled and $use_memcache is True, it will try to use a cached value of the query, or add/set the results.
        * 
        * @param $query String query to perform
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return MySQL Result Identifier for @$query result
        */
        public function query($query, $link = NULL, $use_memcache = false){
            if($this->config->use_memcache && $use_memcache){
                if(!$this->JACKED->Memcacher->isModuleEnabled){
                    //make sure memcache is still up, if it's dead disable it
                    $this->config->offsetSet('use_memcache', false);
                }else{
                    $key = md5($query);
                    $value = $this->JACKED->Memcacher->get($key);
                    if($value){
                        return $value;
                    }
                }
            }
            $link = $link? $link : $this->getLink();
            $query = $this->sanitize($query);
            JACKED::debug_dump($query);
            $value = mysql_query($query, $link);
            if($this->config->use_memcache && $use_memcache){
                $key = md5($query);
                $this->JACKED->Memcacher->set($key, $value);
            }
            return $value;
        }
        
        /**
        * Get a single value from a given MySQL field that matches a condition. If the condition matches
        * more than one row, the value from first row will be returned.
        * 
        * 
        * @param $field string Field name to get value of. Accepts strings beginning with "function:" as MySQL function calls.
        * @param $table string Table name to search
        * @param $cond string [optional] Condition to use for query: "WHERE @$cond". Default will return first row retreived from db.
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Mixed Result data from @$field matching @$cond
        */
        public function get($field, $table, $cond = null, $link = NULL, $use_memcache = true){
            if(stripos($field, "function:") === 0){
                $val = substr($val, 9); //"function:" ends at 9, lol.
                $query = "SELECT " . $field . " FROM `" . $table . "`";
            }else
                $query = "SELECT `" . $field . "` FROM `" . $table . "`";
            if($cond)
                $query .= " WHERE " . $cond;
            $result = $this->query($query, $link, $use_memcache);
            
            if($result && mysql_num_rows($result) > 0){
                $row = mysql_fetch_array($result, MYSQL_NUM);
                $final = stripslashes($row[0]);
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        /**
        * Get an entire row from the given table matching the given cond. If the condition matches more than one row, 
        * returns the first in retrieved data.
        * SELECT * FROM @$table WHERE $@cond LIMIT 1
        * 
        * @param $table string Table name to search
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $result_type int [optional] One of: MYSQL_ASSOC, MYSQL_NUM, or MYSQL_BOTH (default).
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array Result data from @$field matching @$cond
        */
        public function getRow($table, $cond, $result_type = MYSQL_BOTH, $link = NULL, $use_memcache = true){
            $query = "SELECT * FROM `" . $table . "` WHERE " . $cond . " LIMIT 1";
            $result = $this->query($query, $link, $use_memcache);
            
            if($result && mysql_num_rows($result) > 0){
                $row = mysql_fetch_array($result, $result_type);
                $final = array_map("stripslashes", $row);
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        /**
        * Get all entire rows from the given table matching the given cond. 
        * SELECT * FROM @$table WHERE $@cond
        * 
        * @param $table string Table name to search
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $result_type int [optional] One of: MYSQL_ASSOC, MYSQL_NUM, or MYSQL_BOTH (default).
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array Array of rows from @$field matching @$cond
        */
        public function getRows($table, $cond = NULL, $result_type = MYSQL_BOTH, $link = NULL, $use_memcache = true){
            $cond = $cond? $cond : '1';
            $query = "SELECT * FROM `" . $table . "` WHERE " . $cond;
            $result = $this->query($query, $link, $use_memcache);
            
            if($result && mysql_num_rows($result) == 1){
                $row = mysql_fetch_array($result, $result_type);
                $final = array(0 => array_map("stripslashes", $row));
                mysql_free_result($result);
            }else if($result && mysql_num_rows($result) > 1){
                $final = array();
                while($row = mysql_fetch_array($result, $result_type)){
                    $final[] = $row;
                }
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }
        
        
        /**
        * Get all values from given MySQL fields that match a condition. 
        * SELECT @$fields FROM @$table WHERE $@cond
        * 
        * @param $fields string/Array Field names to get value of. String of comma separated field names or array of string field names.
        * @param $table string Table name to search
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $result_type int [optional] One of: MYSQL_ASSOC, MYSQL_NUM, or MYSQL_BOTH (default).
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array Result data from @$fields matching @$cond
        */
        public function getAll($fields, $table, $cond, $result_type = MYSQL_ASSOC, $link = NULL, $use_memcache = true){
            if(is_array($fields)){
                $query = "SELECT " . implode(",", $fields) . " FROM `" . $table . "` WHERE " . $cond;
            }else{
                $query = "SELECT * FROM `" . $table . "` WHERE " . $cond;
            }
            $result = $this->query($query, $link, $use_memcache);

            if($result && mysql_num_rows($result) == 1){
                $row = mysql_fetch_array($result, $result_type);
                $final = array(0 => array_map("stripslashes", $row));
                mysql_free_result($result);
            }else if($result && mysql_num_rows($result) > 1){
                $final = array();
                while($row = mysql_fetch_array($result, $result_type)){
                    $final[] = array_map("stripslashes", $row);
                }
                mysql_free_result($result);
            }else{
                $final = false;
            }
            
            return $final;
        }

        /**
        * Insert data into the database.
        * INSERT INTO @$table @$fields
        * 
        * @param $table string Table name to insert data into
        * @param $data Array Associative array of data as ('field name' => 'value') to insert.
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @return int The id of the newly inserted row if successful, false on failure
        */
        public function insert($table, $data, $link = NULL){
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                $fields[] = $field;
                $values[] = $value;
            }
            $query = "INSERT INTO $table (`" . implode($fields, '`, `') . "`) VALUES ('" . implode($values, '\', \'') . "')";
            $result = $this->query($query, $link, false);
            if($result){
                $done = mysql_insert_id($link);
            }else{
                $done = false;
            }
            return $done;
        }
        
        /**
        * Update data in the database with new values.
        * UPDATE @$table SET @$fields WHERE @$cond
        * 
        * @param $table string Table name to update
        * @param $data Array Associative array of ('field name' => 'value') to update. If a field name starts with 'function:' the 
        * value is evaluated as a MySQL function rather than a string, like: function:NOW()
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @return Boolean Whether the update was successful
        */
        public function update($table, $data, $cond, $link = NULL){
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                if(stripos($value, "function:") === 0){
                    $pairs[] = "`" . $field . "` = " . substr($value, 9) . "";
                }else{
                    $pairs[] = "`" . $field . "` = '" . $value . "'";
                }
            }
            
            $query = "UPDATE $table SET " . implode($pairs, ', ') . " WHERE " . $cond;
            $result = $this->query($query, $link, false);
            return $result;
        }
        
        /**
        * Replace data in the database with new values.
        * REPLACE INTO @$table @$fields
        * 
        * @param $table string Table name to update
        * @param $data Array Associative array of ('field name' => 'value') to update. 
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @return Boolean Whether the replace was successful
        */
        public function replace($table, $data, $link = NULL){
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                $fields[] = $field;
                $values[] = $value;
            }
            
            $query = "REPLACE INTO $table (`" . implode($fields, '`, `') . "`) VALUES ('" . implode($values, '\', \'') . "')";
            $result = $this->query($query, $link, false);
            return $result;
        }
        
        /**
        * Delete data from a given table that matches a given condition
        * DELETE FROM @$table WHERE @$cond
        * 
        * @param $table string Table name to update
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @return Boolean Whether the replace was successful
        */
        public function delete($table, $cond, $link = NULL){
            $query = 'DELETE FROM ' . $this->sanitize($table) . ' WHERE ' . $cond;
            $result = $this->query($query, $link, false);
            return $result;
        }
    }

?>
