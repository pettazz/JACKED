<?php

    /**
     * Syrup Driver for MySQL
     */

    class SyrupDriver extends SyrupDriverInterface{

        private $_mysql_link = NULL;
        private $_config;
        private $_logr;
        private $_util;

        private $_modelName = '';
        private $_tableName = '';

        public function __construct($config, $logr, $util, $modelName){
            $this->_config = $config;
            $this->_logr = $logr;
            $this->_util = $util;

            $this->_modelName = $modelName;
            $this->_tableName = $modelName::tableName;
        }

        public function __destruct(){
            if($this->isLinkOpen()){
                try{
                    mysql_close($this->_mysql_link);
                }catch(Exception $e){}
                $this->_mysql_link = NULL;
            }
        }

        /**
        * Checks if the MySQL link is open.
        * 
        * @param $link int [optional] MySQL Link ID to check. Defaults to default link.
        * @return Boolean Whether the link is active.
        */
        private function isLinkOpen($link = NULL){
            $link = $link? $link : $this->_mysql_link;
            return ($this->_mysql_link == NULL)? false : true;
        }
        
        /**
        * Opens a new link to MySQL.
        * 
        * @param $setDefault Boolean [optional] Whether to make the new link the default link. Defaults to true.
        * @return int MySQL Link ID that was just opened.
        */
        private function openLink($setDefault = true){
            $link = mysql_connect($this->_config['db_host'], $this->_config['db_user'], $this->_config['db_pass'], true);
            mysql_select_db($this->_config['db_name']);
            if($setDefault){
                $this->_mysql_link = $link;
            }
            return $link;
        }
        
        /**
        * Returns the default link. If it's not open, opens it then returns the link.
        * 
        * @return int The default MySQL Link ID.
        */
        private function getLink(){
            if($this->isLinkOpen()){
                return $this->_mysql_link;
            }else{
                return $this->openLink();
            }
        }

        /**
        * Sanitize a string to be safe for use in MySQL queries.
        * TODO: add even better sanitization
        * 
        * @param $value String Value to sanitize.
        * @param $link int [optional] MySQL Link ID to use. Defaults to default link. Opens new default if necessary.
        * @return String Sanitized version of the input string.
        */
        private function sanitize($value, $link = NULL){
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
        private static function paginator($howMany, $page){
            return " LIMIT " . ($howMany * ($page - 1)) . ", " . $howMany;
        }

        /**
        * Helper for getWhereClause to recursively parse criteria data into a string.
        * 
        * @param $criteria Array String field/value pairs.
        * @param $tableName String Name of the table referred to by criteria fields. 
        * @return String Representation of @criteria as a String usable in a MySQL WHERE clause.
        */
        private static function parseWhereCriteria($criteria, $tableName = false){
            $result = "";
            foreach($criteria as $key => $value){
                if(trim($key) == "OR" || trim($key) == "AND"){
                    $result .= trim($key) . " (" . self::parseWhereCriteria($value) . ") ";
                }else if(is_array($value)){
                    $results = array();
                    foreach($value as $innerkey => $innerval){
                        $results[] = self::parseWhereCriteria(array($innerkey => $innerval));
                    }
                    $result .= " ( " . implode(" AND ", $results) . " ) ";
                }else{
                    if(is_numeric($value)){
                        $value = strval($value);
                    }else if(is_bool($value)){
                        $value = ($value)? '1' : '0';
                    }

                    if(strpos($key, '?') === false){
                        //support shortcuts for key = value notation
                        $result = ($tableName? $tableName . '.' : '') . "$key = '" . trim($value) . "' ";
                    }else{
                        //otherwise use the replace ? in key method
                        $result .= str_replace(array('*', '?'), array('%', str_replace('*', '%', "'" . $value . "'")), trim($key)) . " ";
                    }
                }
            }

            return $result;
        }
        
        /**
        * Generates the WHERE clause of a query based on an array of field/value pairs.
        * 
        * @param $criteria Array String field/value pairs.
        * @param $tableName String Name of the table referred to by criteria fields.
        * @return String MySQL WHERE clause.
        */
        private static function getWhereClause($criteria, $tableName = false){
            //accept JSON formatted criteria
            if(is_string($criteria)){
                $criteria = json_decode($criteria);
            }

            return "WHERE " . self::parseWhereCriteria($criteria, $tableName);
        }

        /**
        * Get the string value of the last MySQL error on the given link.
        * 
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link.
        * @return String Last error message from the database connection identified by the link
        */
        private function getError($link = NULL){
            $link = $link? $link : $this->getLink();
            $err = mysql_error($link);
            return $err;
        }

        /**
        * Perform a MySQL query on the given database connection, and return the result identifier. 
        * For internal use only, doesn't handle any sanitization.
        * 
        * @param $query String query to perform
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link
        * @return Array List of all rows returned by @query, or false if none were returned or an error occurred.
        */
        private function mysqlQuery($query, $link = NULL){
            $link = $link? $link : $this->getLink();
            $this->_logr->write($query, Logr::LEVEL_NOTICE, NULL);
            $result = mysql_query($query, $link);
            if($result === true){
                $value = true;
            }else if($result === false){
                $value = false;
            }else if(mysql_num_rows($result) > 0){
                $value = array();
                while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
                    $value[] = array_map("stripslashes", $row);
                }
                mysql_free_result($result);
            }else{
                $value = false;
                $this->_logr->write($this->getError($link), Logr::LEVEL_WARNING, NULL);
            }
            
            return $value;
        }

        /**
        * Perform a MySQL query on the given database connection, and return the result identifier. 
        * 
        * @param $query String query to perform
        * @param $link int [optional] MySQL Resource ID to identify database connection to use. Defaults to default link
        * @return Array List of all rows returned by @query, or false if none were returned or an error occurred.
        */
        private function query($query, $link = NULL){
            //$query = $this->sanitize($query);
            return $this->mysqlQuery($query, $link);
        }

        /**
        * Find all objects matching the given criteria, with optional ordering, limits, and offset.
        * 
        * @param $criteria Array [optional] Criteria for searching data objects. Defaults to all objects.
        * @param $order Array [optional] Two keys to specify ordering: 'field' field name to order by, 'direction' ASC or DESC. Defaults to none.
        * @param $limit int [optional] Limit results to this number. Defaults to no limit.
        * @param $offset int [optional] Start returning results at this offset. Ex: 5 rows are returned, offset 3 would return rows 3 and 4 (4th and 5th) Defaults to 0.
        * @param $followRelations [optional] Whether to find objects specified by relations. Defaults to true.
        * @return Array|Boolean List of data objects returned from the data source. Empty array for no results. False if an error occurred.
        */
        public function find($criteria = array(), $order = null, $limit = null, $offset = 0, $followRelations = true){
            if($followRelations && $this->getRelations()){
                $tables = array($this->_tableName);
                $fields = array();
                foreach($this->getRelations() as $localField => $relationData){
                    if($relationData['type'] == 'hasOne'){
                        $rel = explode('.', $relationData['field']);
                        $relTable = $rel[0];
                        $tables[] = $relTable;
                        $relModel = $relTable . 'Model';
                        
                        foreach($relModel::getFieldNames() as $field){
                            $fields[] = $relTable . '.' . $field;
                        }

                        $relFieldName = $rel[1];
                        $joinClause = " LEFT JOIN $relTable ON " . $relationData['field'] . " = " . $this->_tableName . '.' . $localField . ' ';
                    }else{
                        $this->_logr->write('Only hasOne relations are supported right now.', Logr::LEVEL_WARNING, NULL);
                    }
                }
                $localModel = $this->_tableName . 'Model';
                foreach($localModel::getFieldNames() as $localField){
                    $fields[] = $this->_tableName . '.' . $localField;
                }

                $query = 'SELECT ';
                foreach($fields as $fieldName){
                    $query .= $fieldName . ' AS \'' . $fieldName . '\', ';
                }
                $query = rtrim($query, ', ');
                $query .= ' FROM ' . $this->_tableName . $joinClause;
            }else{
                $query = "SELECT * FROM " . $this->_tableName;
            }
            $query .= " " . self::getWhereClause($criteria, $this->_tableName);
            if($order){
                $query .= " ORDER BY " . $order['field'] . ' ' . $order['direction'];
            }
            if($limit){
                $query .= " LIMIT " . $limit;
            }
            if($offset){
                $query .= " OFFSET " . $offset;
            }
            $data = $this->query($query);
            $results = array();
            if($data && count($data) > 0){
                foreach($data as $row){
                    if($followRelations){
                        $localObject = array();
                        $foreignObjects = array();
                        foreach($row as $key => $value){
                            $fKeyIndex = 0;
                            $table = substr($key, 0, strpos($key, '.'));
                            $field = substr($key, strpos($key, '.') + 1);
                            if($table == $this->_tableName){
                                $localObject[$field] = $value;
                            }else{
                                //check if this table has been seen, TODO: allow multiple entries per table
                                if(!array_key_exists($table, $foreignObjects)){
                                    $foreignObjects[$table] = array();
                                }
                                $foreignObjects[$table][$field] = $value;
                            }
                        }
                        if($foreignObjects){
                            $foreignDataObjects = array();
                            //generate objects for every foreign table entry
                            foreach($foreignObjects as $table => $data){
                                $modelName = $table . 'Model';
                                $foreignDataObjects[$table] = new $modelName($this->_config, $this->_logr, $this->_util, $data, false);
                            }
                        }   
                        $obj = $this->load($localObject, false);
                        //replace the relation keys with the actual foreign objects
                        foreach($this->getRelations() as $localField => $relationData){
                            $relationTableName = substr($relationData['field'], 0, strpos($relationData['field'], '.'));
                            $obj->$localField = $foreignDataObjects[$relationTableName];
                        }
                        $results[] = $obj;
                    }else{
                        $results[] = $this->load($row, false);
                    }
                }
            }
            return $results;
        }

        /**
        * Counts the number of objects matching @criteria
        * 
        * @param $criteria Array [optional] Criteria for searching data objects. Defaults to all objects.
        * @return int Number of matching objects.
        */
        public function count($criteria = array()){
            $query = "SELECT COUNT(*) AS count FROM " . $this->_tableName;
            if($criteria){
                $query .= " " . self::getWhereClause($criteria);
            }
            $done = $this->query($query);
            return $done[0]['count'];
        }

        /**
        * Creates a new data object instance
        * 
        * @param $data Array [optional] Field values to be set upon creation. Defaults to each field's default value.
        * @return SyrupModel The new instance.
        */
        public function create($data = NULL){
            $modelName = $this->_modelName;
            if($data){
                return $this->load($data, true);
            }else{
                return new $modelName($this->_config, $this->_logr, $this->_util);
            }
        }

        /**
        * Load the given data into a data object, new or existing.
        * 
        * @param $data Array Field values to set.
        * @param $isNew Boolean True = the instance will represent a new data object that has not been saved to the data source, False = loading an existing data object.
        * @return SyrupModel The newly created instance.
        */
        private function load($data, $isNew = false){
            $modelName = $this->_modelName;
            return new $modelName($this->_config, $this->_logr, $this->_util, $data, $isNew);
        }

        /**
        * Saves the existing state of this data object to the data source.
        * 
        * @return Boolean Whether the save was completed successfully.
        */
        public function save(){
            if($this->_isDirty){
                if($this->_isNew){
                    $insertFields = array();
                    $insertValues = array();
                    foreach($this->getFields() as $field){
                        $insertFields[] = $field;
                        if(is_object($this->$field) && is_subclass_of($this->$field, 'SyrupModel', false)){
                            //add the relation key to this field and save the related object
                            $relData = $this->getRelations($field);
                            $relField = substr($relData['field'], strpos($relData['field'], '.') + 1);
                            $insertValues[] = $this->sanitize($this->$field->$relField->getValue());
                            $this->$field->save();
                        }else{
                            $insertValues[] = $this->sanitize($this->$field->getValue());
                        }
                    }
                    $query = "INSERT INTO " . $this->_tableName . " (`" . implode('`, `', $insertFields) . "`) VALUES ('" . implode("', '", $insertValues) . "')";
                }else{
                    $query = "UPDATE " . $this->_tableName . " SET ";
                    $sets = array();
                    foreach($this->getFields() as $field){
                        if(is_object($this->$field) && is_subclass_of($this->$field, 'SyrupModel', false)){
                            $this->$field->save();
                        }else{
                            $sets[] = "`$field` = '" . $this->$field->getValue() . "'";
                        }
                    }
                    $query .= implode(', ', $sets);
                    $pkey = $this->getPrimaryKey();
                    $query .= " WHERE " . $this->getPrimaryKeyName() . " = '" . $pkey->getValue()  . "'";
                }

                $done = $this->query($query);
                $this->_isDirty = false;

                return $done;
            }else{
                return true;
            }
        }

        /**
        * Deletes the data object from the data source.
        * 
        * @return Boolean Whether the delete was completed successfully.
        */
        public function delete(){
            if(!$this->_isNew){
                $pkey = $this->getPrimaryKey();
                $query = "DELETE FROM " . $this->_tableName . " WHERE " . $this->getPrimaryKeyName() . " = '" . $pkey->getValue()  . "'";
                $done = $this->query($query);
            }else{
                $done = true;
            }

            foreach($this->getFields() as $field){
                $this->$field = NULL;
            }

            $this->_isNew = true;

            return $done;
        }

    }

?>