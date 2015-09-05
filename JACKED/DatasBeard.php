<?php

    include JACKED_LIB_ROOT . 'php-json-schema/Json/Validator.php';

    use Json\Validator;

    class DatasBeard extends JACKEDModule{
        /*
                http://en.memory-alpha.org/wiki/Beard
                Simplistic admin-manageable persistent data storage.
                Uses JSON Schema: http://json-schema.org/ 
        */
    
        const moduleName = 'DatasBeard';
        const moduleVersion = 1.0;
        public static $dependencies = array('Syrup');

        /**
        * Get all tables that exist
        * 
        * @param $onlyActive Boolean Whether to only get tables that have not been deactivated. Default: True
        * @return Array List of all tables as Syrup objects
        */
        public function getTables($onlyActive = true){
            $where = array();
            if($onlyActive){
                $where = array('AND' => array_merge($where, array('alive' => 1)));
            }
            return $this->JACKED->Syrup->DatasBeardTable->find($where);
        }

        /**
        * Get all the data for a given Table
        * 
        * @param $tableId String UUID of the table to get
        * @param $onlyActive Boolean Whether to only get rows that have not been deactivated. Default: False
        * @param $fetchRows Boolean Whether to also include all the Rows in this Table in the returned array. Default: True
        * @return Array containing Table metadata and Rows in key 'rows' if $fetchRows
        * @throws DatasBeardTableNotFoundException if table with UUID does not exist or $onlyActive is true and table is inactive
        */
        public function getTable($tableId, $onlyActive = true, $fetchRows = true){
            $where = array('uuid' => $tableId);
            if($onlyActive){
                $where = array('AND' => array_merge($where, array('alive' => 1)));
            }
            $result = $this->JACKED->Syrup->DatasBeardTable->findOne($where);

            if(!(count($result) > 0)){
                throw new DatasBeardTableNotFoundException($tableId);
            }

            $ret = $result->toArray();
            if($fetchRows){
                $ret['rows'] = $this->getRows($tableId);
            }

            return $ret;
        }

        /**
        * Create a new Table
        * 
        * @param $name String Friendly Name of the new Table
        * @param $schema String JSON Schema that defines the structure of the Table
        * @return UUID of the newly created Table
        */
        public function createTable($name, $schema){
            $table = $this->JACKED->Syrup->DatasBeardTable->create();
            $table->uuid = $this->JACKED->Util->uuid4();
            $table->name = $name;
            $table->created = time();
            $table->alive = true;
            $table->schema = $schema;
            $table->save();

            return $table->uuid;
        }

        /**
        * Delete a Table. Sets active to FALSE, so that this operation can be undone.
        * Permanent delete can be accomplished with Syrup's delete method.
        * 
        * @param $tableId String UUID of the table to be deleted
        * @throws DatasBeardTableNotFoundException if table with UUID does not exist
        */
        public function deleteTable($tableId){
            $table = $this->JACKED->Syrup->DatasBeardTable->findOne(array('uuid' => $tableId));

            if(!$table){
                throw new DatasBeardTableNotFoundException($tableId);
            }
            $table->alive = 0;
            $table->save();
        }

        /**
        * Get all the rows from a given table
        * 
        * @param $tableId String UUID of the table to get data from
        * @param $onlyActive Boolean Whether to only get rows that have not been deactivated. Default: True
        * @return Array List of all rows retrieved as Arrays
        */
        public function getRows($tableId, $onlyActive = true){
            $where = array('Table' => $tableId);
            if($onlyActive){
                $where = array('AND' => array_merge($where, array('alive' => 1)));
            }
            $results = $this->JACKED->Syrup->DatasBeardRow->find($where);

            $ret = array();
            foreach($results as $resObj){
                $ret[$resObj->uuid] = $this->arrayFromBlob($resObj->content);
                $ret[$resObj->uuid]['uuid'] = $resObj->uuid;
            }

            return $ret;
        }

        /**
        * Get a specific row by uuid
        * 
        * @param $uuid String UUID of the row to get
        * @param $onlyActive Boolean Whether to only get the row if it is currently active. Default: False
        * @return Array Row as an associative array if found
        * @throws DatasBeardRowNotFoundException if row with UUID does not exist or $onlyActive is true and Row is inactive
        */
        public function getRow($uuid, $onlyActive = false){
            $where = array('uuid' => $uuid);
            if(!$onlyActive){
                $where = array('AND' => array_merge($where, array('alive' => 1)));
            }
            $result = $this->JACKED->Syrup->DatasBeardRow->findOne($where);
            if(count($result)){
                $ret = $this->arrayFromBlob($result->content);
            }else{
                throw new DatasBeardRowNotFoundException($uuid);
            }

            $ret['uuid'] = $result->uuid;

            return $ret;
        }

        /**
        * Set a specific row by uuid
        * 
        * @param $uuid String UUID of the row to set
        * @param $content Array Row data to be set. Must match Table's schema.
        * @throws DatasBeardRowNotFoundException if row with UUID does not exist
        * @throws Json\ValidationException if validating the row against the table's schema fails
        */
        public function setRow($uuid, $content){
            $newContent = $this->toBlob($content);
            $where = array('uuid' => $uuid);
            $result = $this->JACKED->Syrup->DatasBeardRow->findOne($where);

            if(count($result)){
                $this->validateForTable($result->Table, $newContent);

                $result->content = $newContent;
                $result->edited = time();
                $result->save();
            }else{
                throw new DatasBeardRowNotFoundException($uuid);
            }
        }

        /**
        * Create a new row and add it to a table
        * 
        * @param $tableId String UUID of the Table to add this Row to
        * @param $content Array Row data to be set. Must match Table's schema.
        * @return UUID of the newly created Row
        * @throws DatasBeardTableNotFoundException if Table with UUID tableId does not exist
        * @throws Json\ValidationException if validating the row against the table's schema fails
        */
        public function createRow($tableId, $content){
            $where = array('uuid' => $tableId);
            $result = $this->JACKED->Syrup->DatasBeardTable->findOne($where);

            if(!(count($result)) > 0){
                throw new DatasBeardTableNotFoundException($uuid);
            }

            $newContent = $this->toBlob($content);
            $this->validateForTable($tableId, $newContent);

            $row = $this->JACKED->Syrup->DatasBeardRow->create();
            $row->uuid = $this->JACKED->Util->uuid4();
            $row->Table = $tableId;
            $row->edited = time();
            $row->alive = true;
            $row->content = $newContent;
            $row->save();

            return $row->uuid;
        }

        /**
        * Delete a row
        * 
        * @param $uuid String UUID of the row to be deleted
        * @throws DatasBeardRowNotFoundException if row with UUID does not exist
        */
        public function deleteRow($uuid){
            $row = $this->JACKED->Syrup->DatasBeardRow->findOne(array('uuid' => $uuid));
            if(!$row){
                throw new DatasBeardRowNotFoundException($uuid);
            }

            $row->alive = false;
            $row->save();
        }

        /**
        * Translate a given JSON blob into an array of data
        * 
        * @param $blob String JSON data
        * @return Array Associative array of all JSON data
        */
        private function arrayFromBlob($blob){
            return json_decode($blob, true);
        }

        /**
        * Translate a given JSON blob into an array of data
        * 
        * @param $blob String JSON data
        * @return Array Associative array of all JSON data
        */
        private function objectFromBlob($blob){
            return json_decode($blob);
        }

        /**
        * Translate a given Object into a JSON blob
        * 
        * @param $obj Object of data to be translated to JSON
        * @return String Data from object in JSON 
        */
        private function toBlob($obj){
            return json_encode($obj);
        }

        /**
        * Validate a given JSON row against the schema for a particular table
        * 
        * @param $tableId String UUID of the table for which to validate
        * @param $json String The JSON content to be validated 
        * @return good question
        * @throws Json\ValidationException if validation fails
        */
        public function validateForTable($tableId, $json){
            $validator = new Validator($this->getSchemaForTable($tableId));
            return $validator->validate($this->objectFromBlob($json));
        }

        /**
        * Get the JSON Schema for a given table
        * 
        * @param $tableId String UUID of table for which to get schema
        * @return String JSON schema for the table
        */
        private function getSchemaForTable($tableId){
            $table = $this->JACKED->Syrup->DatasBeardTable->findOne(array('uuid' => $tableId));
            if(count($table) <= 0){
                throw new DatasBeardTableDoesNotExistException($tableId);
            }
            return $table->schema;
        }

    }


    class DatasBeardTableNotFoundException extends Exception{
        public function __construct($uuid, $code = 0, Exception $previous = null){
            $message = "Table with uuid `$uuid` not found.";
            
            parent::__construct($message, $code, $previous);
        }
    }

    class DatasBeardRowNotFoundException extends Exception{
        public function __construct($uuid, $code = 0, Exception $previous = null){
            $message = "Row with uuid `$uuid` not found.";
            
            parent::__construct($message, $code, $previous);
        }
    }
?>
