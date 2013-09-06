<?php

    class MySQL extends JACKEDModule{
        const moduleName = 'MySQL';
        const moduleVersion = 2.7;
        public static $dependencies = array(); //array('Memcacher' => array('required' => false));
        
        private $_mysqli_obj_internal = NULL;
        
        public function __destruct(){
            if($this->isLinkOpen()){
                try{
                    $this->_mysqli_obj_internal->close();
                }catch(Exception $e){}
                $this->_mysqli_obj_internal = NULL;
            }
        }

        public function __get($key){
            if($key == '_mysqli_obj'){
                return $this->getLink();
            }else{
                return $this->$key;
            }
        }

        /**
        * Checks if the MySQL link is open.
        * 
        * @return Boolean Whether the link is active.
        */
        private function isLinkOpen(){
            return ($this->_mysqli_obj_internal == NULL)? false : true;
        }
        
        /**
        * Opens a new link to MySQL. If $setDefault is true and link creation fails, the module is disabled.
        * 
        * @param $setDefault Boolean [optional] Whether to make the new object the default. Defaults to true.
        * @return Object MySQLi instance that was just created.
        */
        private function openLink($setDefault = true){
            try{
                $obj = new mysqli($this->config->db_host, $this->config->db_user, $this->config->db_pass, $this->config->db_name);
                $obj->autocommit(true);
                if($setDefault){
                    $this->_mysqli_obj_internal = $obj;
                }
                return $obj;
            }catch(Exception $e){
                if($setDefault){
                    $this->isModuleEnabled = false;
                }
                $this->JACKED->Logr->write('Unable to create the connection to MySQL: ' . $e->getMessage(), Logr::LEVEL_FATAL, $e->getTrace(), 'MySQL');
            }
            if($this->_mysqli_obj_internal->connect_errno > 0){
                if($setDefault){
                    $this->isModuleEnabled = false;
                }
                throw new Exception($this->_mysqli_obj->connect_error);
            }
        }
        
        /**
        * Returns the default object. If it's not open, opens it then returns the new one.
        * 
        * @return int The default MySQLi object.
        */
        private function getLink(){
            if($this->isLinkOpen()){
                return $this->_mysqli_obj_internal;
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
        * @return String Sanitized version of the input string.
        */
        public function sanitize($value){
            if($this->_mysqli_obj == NULL){
                echo 'wtf';
            }
            return $this->_mysqli_obj->real_escape_string(stripslashes($value));
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
        * @param $result MySQLi Result Object to parse.
        * @param $result_type int [optional] One of: MYSQLI_ASSOC (default), MYSQLI_NUM, or MYSQLI_BOTH.
        * @return Array Result data parsed into an associative array.
        */
        public function parseResult($result, $result_type = MYSQLI_ASSOC){
           $done = array();
            if($result){
                while($row = $this->_mysqli_obj->fetch_array($result_type)){
                    $done[] = $row;
                }
            }
            if(count($done) == 1)
                $done = $done[0];
            return $done;
        }

        /**
        * Get the string value of the last MySQL error.
        * 
        * @return String Last error message from the database connection identified by the link
        */
        public function getError(){
            return $this->_mysqli_obj->error;
        }

        /**
        * Perform a MySQL query on the given database connection, and return the result identifier. If memcache is 
        * enabled and $use_memcache is True, it will try to use a cached value of the query, or add/set the results.
        * For internal use only, doesn't handle any sanitization.
        * 
        * @param $query String query to perform
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @param $result_type int [optional] One of: MYSQLI_ASSOC (default), MYSQLI_NUM, or MYSQLI_BOTH.
        * @return Array List of all rows returned by @query, or false if none were returned or an error occurred.
        */
        private function mysqlQuery($query, $use_memcache = false, $result_type = MYSQLI_ASSOC){
            if($this->config->use_memcache && $use_memcache){
                if(!$this->JACKED->Memcacher->isModuleEnabled){
                    //make sure memcache is still up, if it's dead disable it
                    $this->config->offsetSet('use_memcache', false);
                }else{
                    $key = md5($query);
                    $value = $this->JACKED->Memcacher->get($key);
                    if($value){
                        $this->JACKED->Logr->write("Memcached hit $key; Returned cached value.", Logr::LEVEL_NOTICE, NULL, 'MySQL');
                        return $value;
                    }
                }
            }
            $this->JACKED->Logr->write($query, Logr::LEVEL_NOTICE, NULL, 'MySQL');
            $result = $this->_mysqli_obj->query($query);
            if($result === true){
                $value = true;
            }else if($result === false){
                $value = false;
            }else if($result->num_rows > 0){
                $value = array();
                while($row = $result->fetch_array($result_type)){
                    $value[] = array_map("stripslashes", $row);
                }
                $result->free();
            }else{
                $value = false;
            }
            if($this->config->use_memcache && $use_memcache){
                $key = md5($query);
                $this->JACKED->Logr->write("Memcached miss $key; Stored.", Logr::LEVEL_NOTICE, NULL, 'MySQL');
                $this->JACKED->Memcacher->set($key, $value);
            }

            if($value === false){
                $err = $this->getError();
                if($err){
                    $this->JACKED->Logr->write($this->getError(), Logr::LEVEL_WARNING, NULL, 'MySQL');
                }
            }
            return $value;
        }

        /**
        * Perform a MySQL query on the given database connection, and return the result identifier. If memcache is 
        * enabled and $use_memcache is True, it will try to use a cached value of the query, or add/set the results.
        * 
        * @param $query String query to perform
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array List of all rows returned by @query, or false if none were returned or an error occurred.
        */
        public function query($query, $use_memcache = false){
            // $query = $this->sanitize($query);
            return $this->mysqlQuery($query, $use_memcache);
        }
        
        /**
        * Get a single value from a given MySQL field that matches a condition. If the condition matches
        * more than one row, the value from first row will be returned.
        * 
        * 
        * @param $field string Field name to get value of. Accepts strings beginning with "function:" as MySQL function calls.
        * @param $table string Table name to search
        * @param $cond string [optional] Condition to use for query: "WHERE @$cond". Default will return first row retreived from db.
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Mixed Result data from @$field matching @$cond
        */
        public function get($field, $table, $cond = null, $use_memcache = true){
            $field = $this->sanitize($field);
            $table = $this->sanitize($table);
            $cond = $cond;
            if(stripos($field, "function:") === 0){
                $field = substr($field, 9); //"function:" ends at 9, lol.
                $query = "SELECT " . $field . " FROM `" . $table . "`";
            }else
                $query = "SELECT `" . $field . "` FROM `" . $table . "`";
            if($cond)
                $query .= " WHERE " . $cond;
            $result = $this->mysqlQuery($query, $use_memcache, MYSQL_BOTH);
            if($result){
                $result = $result[0][0];
            }
            
            return $result;
        }
        
        /**
        * Get all rows of specific values from given MySQL fields that match a condition. 
        * SELECT @$fields FROM @$table WHERE $@cond
        * 
        * @param $fields string/Array Field names to get value of. String of comma separated field names or array of string field names.
        * @param $table string Table name to search
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array Result data from @$fields matching @$cond
        */
        public function getAll($fields, $table, $cond, $use_memcache = true){
            $table = $this->sanitize($table);
            $cond = $cond;
            if(is_array($fields)){
                $query = "SELECT " . $this->sanitize(implode(",", $fields)) . " FROM `" . $table . "` WHERE " . $cond;
            }else if(is_string($fields)){
                $query = "SELECT " . $this->sanitize($fields) . " FROM `" . $table . "` WHERE " . $cond;
            }else{
                $query = "SELECT * FROM `" . $table . "` WHERE " . $cond;
            }

            return $this->mysqlQuery($query, $use_memcache);
        }
        
        /**
        * Get an entire row from the given table matching the given cond. If the condition matches more than one row, 
        * returns the first in retrieved data.
        * SELECT * FROM @$table WHERE $@cond LIMIT 1
        * 
        * @param $table string Table name to search
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array Result data from @$field matching @$cond
        */
        public function getRow($table, $cond, $use_memcache = true){
            $table = $this->sanitize($table);
            $cond = $cond;
            $query = "SELECT * FROM `" . $table . "` WHERE " . $cond . " LIMIT 1";
            
            $result = $this->mysqlQuery($query, $use_memcache);
            return $result[0];
        }
        
        /**
        * Get all entire rows from the given table matching the given cond. 
        * SELECT * FROM @$table WHERE $@cond
        * 
        * @param $table string Table name to search
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array Array of rows from @$field matching @$cond
        */
        public function getRows($table, $cond = NULL, $use_memcache = true){
            $cond = $cond? $cond : '1';
            $query = "SELECT * FROM `" . $this->sanitize($table) . "` WHERE " . $cond;

            return $this->mysqlQuery($query, $use_memcache);
        }

        /**
        * Select from a simple JOIN of two tables
        * SELECT @fields FROM @table1 @join_type JOIN @table2 ON @table1.@join1 = @table2.@join2
        *
        * @param $fields1 Array Field names to get value of from @table1. Array of string field names.
        * @param $fields2 Array Field names to get value of from @table2. Array of string field names.
        * @param $join_type string One of: INNER, OUTER, LEFT, RIGHT
        * @param $table1 string Name of the left Table
        * @param $table2 string Name of the right Table
        * @param $join1 string Field name to join on from the left table
        * @param $join2 string Field name to join on from the right table
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @param $use_memcache Boolean [optional] Whether to attempt to use get the value from memcache and/or store the value of the query
        * @return Array Result data from @$fields 
        */
        public function getJoin($fields1 = false, $fields2 = false, $join_type, $table1, $table2, $join1, $join2, $cond = false, $use_memcache = true){
            $table1 = $this->sanitize($table1);
            $table2 = $this->sanitize($table2);
            $join1 = $this->sanitize($join1);
            $join2 = $this->sanitize($join2);
            $join_type = $this->sanitize($join_type);
            $query = 'SELECT ';
            if($fields1 || $fields2){
                if(is_array($fields1)){
                    $query .= '`' . $table1 . '`.`' . $this->sanitize(implode('`, `' . $table1 . '`.`', $fields1)) . '`';
                    if(is_array($fields2)){
                        $query .= ', ';
                    }
                }
                if(is_array($fields2)){
                    $query .= '`' . $table2 . '`.`' . $this->sanitize(implode('`, `' . $table2 . '`.`', $fields2)) . '`';
                }
            }else{
                $query .= '*';
            }

            $query .= ' FROM ' . $table1 . ' ' . $join_type . ' JOIN ' . $table2 . ' ON `' . $table1 . '`.`' . $join1 . '` = `' . $table2 . '`.`' . $join2 . '`';

            if($cond){
                $query .= ' WHERE ' . $cond;
            }

            return $this->mysqlQuery($query, $use_memcache);
        }

        /**
        * Insert data into the database.
        * INSERT INTO @$table @$fields
        * 
        * @param $table string Table name to insert data into
        * @param $data Array Associative array of data as ('field name' => 'value') to insert.
        * @return int The id of the newly inserted row if successful, false on failure
        */
        public function insert($table, $data){
            $table = $this->sanitize($table);
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                $fields[] = $this->sanitize($field);
                $values[] = $this->sanitize($value);
            }
            $query = "INSERT INTO $table (`" . implode($fields, '`, `') . "`) VALUES ('" . implode($values, '\', \'') . "')";
            $result = $this->mysqlQuery($query, false);
            if($result){
                $done = $this->_mysqli_obj->insert_id;
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
        * @return Boolean Whether the update was successful
        */
        public function update($table, $data, $cond){
            $table = $this->sanitize($table);
            $cond = $cond;
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                $field = $this->sanitize($field);
                $value = $this->sanitize($value);
                if(stripos($value, "function:") === 0){
                    $pairs[] = "`" . $field . "` = " . substr($value, 9) . "";
                }else{
                    $pairs[] = "`" . $field . "` = '" . $value . "'";
                }
            }
            
            $query = "UPDATE $table SET " . implode($pairs, ', ') . " WHERE " . $cond;
            $result = $this->mysqlQuery($query, false);
            return $result;
        }
        
        /**
        * Replace data in the database with new values.
        * REPLACE INTO @$table @$fields
        * 
        * @param $table string Table name to update
        * @param $data Array Associative array of ('field name' => 'value') to update. 
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @return Boolean Whether the replace was successful
        */
        public function replace($table, $data){
            $table = $this->sanitize($table);
            $fields = array();
            $values = array();
            foreach($data as $field => $value){
                $fields[] = $this->sanitize($field);
                $values[] = $this->sanitize($value);
            }
            
            $query = "REPLACE INTO $table (`" . implode($fields, '`, `') . "`) VALUES ('" . implode($values, '\', \'') . "')";
            $result = $this->mysqlQuery($query, false);
            return $result;
        }
        
        /**
        * Delete data from a given table that matches a given condition
        * DELETE FROM @$table WHERE @$cond
        * 
        * @param $table string Table name to update
        * @param $cond string Condition to use for query: "WHERE @$cond"
        * @return Boolean Whether the replace was successful
        */
        public function delete($table, $cond){
            $query = 'DELETE FROM ' . $this->sanitize($table) . ' WHERE ' . $cond;
            $result = $this->mysqlQuery($query, false);
            return $result;
        }
    }

?>
