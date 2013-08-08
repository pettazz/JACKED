<?php

    /**
     * Syrup Driver for MySQL
     */

    class SyrupDriver extends SyrupDriverInterface{

        protected $_mysqli_obj_internal = NULL;
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
        protected function isLinkOpen(){
            return ($this->_mysqli_obj_internal == NULL)? false : true;
        }
        
        /**
        * Opens a new link to MySQL.
        * 
        * @param $setDefault Boolean [optional] Whether to make the new link the default link. Defaults to true.
        * @return int The default MySQLi object.
        */
        protected function openLink($setDefault = true){
            try{
                $obj = new mysqli($this->_config['db_host'], $this->_config['db_user'], $this->_config['db_pass'], $this->_config['db_name']);
                $obj->autocommit(true);
                if($setDefault){
                    $this->_mysqli_obj_internal = $obj;
                }
                return $obj;
            }catch(Exception $e){
                if($setDefault){
                    $this->isModuleEnabled = false;
                }
                throw $e;
            }
            if($this->_mysqli_obj_internal->connect_errno > 0){
                if($setDefault){
                    $this->isModuleEnabled = false;
                }
                throw new Exception($this->_mysqli_obj_internal->connect_error);
            }
        }
        
        /**
        * Returns the default object. If it's not open, opens it then returns the new one.
        * 
        * @return int The default MySQLi object.
        */
        protected function getLink(){
            if($this->isLinkOpen()){
                return $this->_mysqli_obj_internal;
            }else{
                return $this->openLink();
            }
        }

        /**
        * Sanitize a string to be safe for use in MySQL queries.
        * TODO: add even better sanitization
        * 
        * @param $value String Value to sanitize.
        * @return String Sanitized version of the input string.
        */
        protected function sanitize($value){
            return $this->_mysqli_obj->real_escape_string(stripslashes($value));
        }

        /**
        * Get the MySQL LIMIT clause to use for paginating a query.
        * 
        * @param $rows int Number of rows/values per page.
        * @param $page int Page number to get values for.
        * @return String MySQL LIMIT clause.
        */
        protected static function paginator($howMany, $page){
            return " LIMIT " . ($howMany * ($page - 1)) . ", " . $howMany;
        }

        /**
        * Helper for getWhereClause to recursively parse criteria data into a string.
        * 
        * @param $criteria Array String field/value pairs.
        * @param $tableName String Name of the table referred to by criteria fields. 
        * @return String Representation of @criteria as a String usable in a MySQL WHERE clause.
        */
        protected function parseWhereCriteria($criteria, $tableName = false){
            $result = "";
            $relations = $this->getRelations();
            foreach($criteria as $key => $value){
                if(array_key_exists(trim($key), $relations)){
                    $relationData = $relations[trim($key)];
                    $rel = explode('.', $relationData['field']);
                    $relTable = $rel[0];
                    $relModel = $relTable . 'Model';
                    switch($relationData['type']){
                        case 'hasOne':
                            $result = ($tableName? $tableName . '.' : '') . "$key = '" . trim($value) . "' ";
                            break;
                        case 'hasOneForeign':
                            //$result = $relationData['target'] . " = " . $relTable . ".target AND " . $relTable . "." . $relTable . " = '" . trim($value) . "'";
                            throw new Exception("findby hasOneForeign type is not yet supported.");
                            break;
                        case 'hasManyForeign':
                            $result = $relationData['target'] . " = " . $relModel::relationTable . ".target AND " . $relModel::relationTable . "." . $relTable . " = '" . trim($value) . "'";
                            break;

                        default:
                            throw new Exception("Unknown relation type.");
                            break;
                    }
                }else if(trim($key) == "OR" || trim($key) == "AND"){
                    $result .= trim($key) . " (" . $this->parseWhereCriteria($value) . ") ";
                }else if(is_array($value)){
                    $results = array();
                    foreach($value as $innerkey => $innerval){
                        $results[] = $this->parseWhereCriteria(array($innerkey => $innerval));
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
        protected function getWhereClause($criteria, $tableName = false){
            if(empty($criteria)){
                return '';
            }

            //accept JSON formatted criteria
            if(is_string($criteria)){
                $criteria = json_decode($criteria);
            }

            return "WHERE " . $this->parseWhereCriteria($criteria, $tableName);
        }

        /**
        * Get the string value of the last MySQL error.
        * 
        * @return String Last error message from the database connection identified by the link
        */
        protected function getError(){
            return $this->_mysqli_obj->error;
        }

        /**
        * Perform a MySQL query on the given database connection, and return the result object. 
        * For internal use only, doesn't handle any sanitization.
        * 
        * @param $query String query to perform
        * @return Array List of all rows returned by @query, or false if none were returned or an error occurred.
        */
        protected function mysqlQuery($query){
            $this->_logr->write($query, Logr::LEVEL_NOTICE, NULL);
            $result = $this->_mysqli_obj->query($query);
            if($result === true){
                $value = true;
            }else if($result === false){
                $value = false;
            }else if($result->num_rows > 0){
                $value = array();
                while($row = $result->fetch_array(MYSQLI_ASSOC)){
                    $value[] = array_map("stripslashes", $row);
                }
                $result->free();
            }else{
                $value = false;
                $err = $this->getError();
                if($err){
                    $this->_logr->write($this->getError(), Logr::LEVEL_WARNING, NULL);
                }
            }
            
            return $value;
        }

        /**
        * Perform a MySQL query on the given database connection, and return the result identifier. 
        * 
        * @param $query String query to perform
        * @return Array List of all rows returned by @query, or false if none were returned or an error occurred.
        */
        protected function query($query){
            //$query = $this->sanitize($query);
            return $this->mysqlQuery($query);
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
                $tables = array();
                $fields = array();
                $subqueries = array();
                $joinClause = '';
                foreach($this->getRelations() as $localField => $relationData){
                    $allowedRelations = array('hasOne', 'hasOneForeign', 'hasManyForeign');
                    if(in_array($relationData['type'], $allowedRelations)){
                        $rel = explode('.', $relationData['field']);
                        $relTable = $rel[0];
                        $relModel = $relTable . 'Model';

                        if($relationData['type'] == 'hasOne'){
                            foreach($relModel::getFieldNames() as $field){
                                $fields[] = $relTable . '.' . $field;
                            }
                            $joinClause .= " LEFT JOIN $relTable ON " . $relationData['field'] . " = " . $this->_tableName . '.' . $localField . ' ';
                        }elseif($relationData['type'] == 'hasOneForeign'){
                            foreach($relModel::getFieldNames() as $field){
                                $fields[] = $relTable . '.' . $field;
                            }
                            $joinClause .= " LEFT JOIN $relTable ON " . $relationData['field'] . " = " . $this->_tableName . '.guid ';
                        }elseif($relationData['type'] == 'hasManyForeign'){
                            $query = 'SELECT ';
                            foreach($relModel::getFieldNames() as $fieldName){
                                $query .= $relTable . '.' . $fieldName . ' AS \'' . $fieldName . '\', ';
                            }
                            $tables[] = $relModel::relationTable;
                            $query = rtrim($query, ', ');
                            $query .= ' FROM ' . $relTable . ', ' . $relModel::relationTable;
                            $query .= ' WHERE ' . $relTable . '.guid = ' . $relModel::relationTable . '.' . $relTable;
                            $query .= ' AND ' . $relModel::relationTable . '.target = \'{!relational_target_UUID}\'';
                            $subqueries[$relTable] = $query;
                        }
                    }else{
                        $this->_logr->write('Only hasOne relations in the Model are supported right now.', Logr::LEVEL_WARNING, NULL);
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
                $query .= ' FROM ' . implode(', ', $tables) . ', ' . $this->_tableName . $joinClause;
            }else{
                $query = "SELECT * FROM " . $this->_tableName;
            }
            $query .= " " . $this->getWhereClause($criteria, $this->_tableName);
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
                            if(!($relationData['type'] == 'hasManyForeign')){
                                $relationTableName = substr($relationData['field'], 0, strpos($relationData['field'], '.'));
                                $obj->$localField = $foreignDataObjects[$relationTableName];
                            }
                        }
                        // replace any placeholders with the results from their subqueries
                        foreach($subqueries as $relationName => $query){
                            $relationData = $this->getRelations($relationName);
                            $query = str_replace('{!relational_target_UUID}', $row[$relationData['target']], $query);
                            $data = $this->query($query);
                            $subresult = array();
                            if($data){
                                $modelName = $relationName . 'Model';
                                foreach($data as $row){
                                    $subresult[] = new $modelName($this->_config, $this->_logr, $this->_util, $row, false);
                                }
                            }
                            $obj->$relationName = $subresult;
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
                $query .= " " . $this->getWhereClause($criteria);
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
                        if(is_object($this->$field) && is_subclass_of($this->$field, 'SyrupModel', false)){
                            //add the relation key to this field and save the related object
                            $insertFields[] = $field;
                            $relData = $this->getRelations($field);
                            $relField = substr($relData['field'], strpos($relData['field'], '.') + 1);
                            $insertValues[] = $this->sanitize($this->$field->$relField->getValue());
                            $this->$field->save();
                        }else if(is_array($this->$field)){
                            if(!empty($this->$field)){
                                foreach($this->$field as $relItem){
                                    if($relItem->_isDirty){
                                        $relItem->save();
                                    }
                                }
                            }
                        }else{
                            if(array_key_exists($field, $this->getRelations()) && empty($this->$field) || $this->$field == SyrupField::REL_PLACEHOLDER){
                                //this field is a relation that hasn't been set, so we ignore it   
                            }else{
                                $insertFields[] = $field;
                                $insertValues[] = $this->sanitize($this->$field->getValue());
                            }
                        }
                    }
                    $query = "INSERT INTO " . $this->_tableName . " (`" . implode('`, `', $insertFields) . "`) VALUES ('" . implode("', '", $insertValues) . "')";
                }else{
                    $query = "UPDATE " . $this->_tableName . " SET ";
                    $sets = array();
                    foreach($this->getFields() as $field){
                        if(is_object($this->$field) && is_subclass_of($this->$field, 'SyrupModel', false)){
                            $this->$field->save();
                        }else if(is_array($this->$field)){
                            if(!empty($this->$field)){
                                foreach($this->$field as $relItem){
                                    if($relItem->_isDirty){
                                        $relItem->save();
                                    }
                                }
                            }
                        }else{
                            if(array_key_exists($field, $this->getRelations()) && empty($this->$field) || $this->$field == SyrupField::REL_PLACEHOLDER){
                                //this field is a relation that hasn't been set, so we ignore it   
                            }else{
                                $sets[] = "`$field` = '" . $this->$field->getValue() . "'";
                            }
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