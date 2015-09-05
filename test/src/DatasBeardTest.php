<?php
    require_once('test/jacked_test_conf.php');

    // fixtures

    $testSchema = '{
        "title": "DatasBeard Test Schema",
        "type": "object",
        "properties": {
            "firstName": {
                "type": "string"
            },
            "lastName": {
                "type": "string"
            },
            "age": {
                "description": "Age in years",
                "type": "integer",
                "minimum": 0
            },
            "hats": {
                "type": "array",
                "items": {
                    "type": "string"
                },
                "minItems": 1,
                "uniqueItems": true
            }
        },
        "additionalProperties": false,
        "required": ["firstName", "lastName"]
    }';

    $contentValid = array(
        1 => '{
            "firstName": "Steve",
            "lastName": "Rogers",
            "age": 96,
            "hats": ["Cap\'s Helmet"]
        }',
        2 => '{
            "firstName": "Tony",
            "lastName": "Stark",
            "hats": ["Iron Man Mask"]
        }',
        3 => '{
            "firstName": "Clint",
            "lastName": "Barton",
            "age": 44
        }'
    );
    $contentInvalid = array(
        1 => '{
            "firstName": "Bruce",
            "lastName": "Banner",
            "color": "green"
        }',
        2 => '{
            "lastName": "Fury"
        }',
        3 => '{
            "firstname": "Phil",
            "lastName": "Coulson",
            "hats": []
        }'
    );
     
    class DatasBeardTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("DatasBeard, Testur, MySQL, Syrup");
            
            $this->JACKED->MySQL->config->db_host = '127.0.0.1';
            $this->JACKED->MySQL->config->db_user = 'root';
            $this->JACKED->MySQL->config->db_name = 'jacked_test';

            $syrupDConf = $this->JACKED->Syrup->config->driverConfig;
            $syrupDConf['db_host'] = '127.0.0.1';
            $syrupDConf['db_user'] = 'root';
            $syrupDConf['db_pass'] = '';
            $syrupDConf['db_name'] = 'jacked_test';
            $syrupDConf['model_root'] = 'Syrup/models/';
            $this->JACKED->Syrup->config->driverConfig = $syrupDConf;
        }

        public function tearDown(){
            $this->JACKED->MySQL->query('DELETE FROM DatasBeardRow WHERE 1');
            $this->JACKED->MySQL->query('DELETE FROM DatasBeardTable WHERE 1');
        }

        // helper methods

        /**
        * Creates a randomly generated Data's Beard Table directly in MySQL, circumventing the actual method.
        * 
        * @param $timestamp int [optional] The timestamp with which to create the table. Defaults to the current timestamp.
        * @param $name String [optional] Name of the Table to create. Defaults to randomly generated.
        * @param $schema String [optional] Schema to add to the Table. Defaults to none.
        * @return Array All details of new Table
        */
        public function createTestTable($timestamp = NULL, $name = NULL, $schema = NULL){
            $name = $name? $name : ucfirst($this->JACKED->Testur->generateSentence(false));
            $created = $timestamp? $timestamp : rand(1022967819, time());
            $schema = $schema? $schema : NULL;

            $details = array(
                'uuid' => $this->JACKED->Util->uuid4(),
                'name' => $name,
                'created' => $created,
                'alive' => true,
                'schema' => $schema
            );
            $this->JACKED->MySQL->insert('DatasBeardTable', $details);

            return $details;
        }

        /**
        * Creates a randomly generated Data's Beard Row directly in MySQL, circumventing the actual method.
        * 
        * @param $content String JSON content to be added for the new row
        * @param $table String UUID of Table to add row to
        * @return Array All details of new Row
        */
        public function createTestRow($content, $table){
            $details = array(
                'uuid' => $this->JACKED->Util->uuid4(),
                'Table' => $table,
                'edited' => time(),
                'alive' => true,
                'content' => $content
            );
            $this->JACKED->MySQL->insert('DatasBeardRow', $details);

            return $details;
        }


        // actual tests
        public function test_getTables(){
            $fixtures = array();
            for($i = 0; $i < 3; $i++){
                $fixtures[$i] = $this->createTestTable(3 - $i);
            }

            $got_tables = $this->JACKED->DatasBeard->getTables();
            $this->assertFalse(!$got_tables);
            for($i = 0; $i < 3; $i++){
                $test_table = $got_tables[$i];
                $table_fixture = $fixtures[$i];
                foreach($table_fixture as $key => $val){
                    $this->assertEquals($val, $test_table->$key);
                }
            }
        }

        public function test_deleteTable(){
            $fixture = $this->createTestTable();

            $this->JACKED->DatasBeard->deleteTable($fixture['uuid']);

            //verify that we don't have any tables now with a normal getTables()
            $got_tables = $this->JACKED->DatasBeard->getTables();
            $this->assertTrue(!$got_tables);

            //verify that we still get the soft deleted table when we use onlyActive = false
            $got_tables = $this->JACKED->DatasBeard->getTables(false);
            $this->assertFalse(!$got_tables);
            $table = $got_tables[0];
            $this->assertEquals($table->uuid, $fixture['uuid']);
        }

        public function test_getRows(){
            global $testSchema, $contentValid;

            $fixtures = array();
            $table = $this->createTestTable(1, $testSchema);
            for($i = 1; $i < 4; $i++){
                $row = $this->createTestRow($contentValid[$i], $table['uuid']);
                $fixtures[$row['uuid']] = $row;
            }

            $got_rows = $this->JACKED->DatasBeard->getRows($table['uuid']);
            $this->assertFalse(!$got_rows);
            foreach($got_rows as $got_row){
                $row = json_decode($fixtures[$got_row['uuid']]['content']);
                foreach($row as $key => $val){
                    $this->assertEquals($val, $got_row[$key]);
                }
            }       
        }

        public function test_createRowValid(){
            global $testSchema, $contentValid;

            $table = $this->createTestTable(1, 'test', $testSchema);
            $id = $this->JACKED->DatasBeard->createRow($table['uuid'], json_decode($contentValid[1]));

            $got_row = $this->JACKED->DatasBeard->getRow($id);
            $this->assertFalse(!$got_row);
            $row = json_decode($contentValid[1]);
            foreach($row as $key => $val){
                $this->assertEquals($val, $got_row[$key]);
            }
        }

        /**
         * @expectedException Json\ValidationException
         */
        public function test_createRowInvalid(){
            global $testSchema, $contentInvalid;

            $table = $this->createTestTable(1, 'test', $testSchema);
            $id = $this->JACKED->DatasBeard->createRow($table['uuid'], json_decode($contentInvalid[1]));
        }

        public function test_setRowValid(){
            global $testSchema, $contentValid;

            $table = $this->createTestTable(1, 'test', $testSchema);
            $id = $this->JACKED->DatasBeard->createRow($table['uuid'], json_decode($contentValid[2]));

            $this->JACKED->DatasBeard->setRow($id, json_decode($contentValid[3], true));

            $got_row = $this->JACKED->DatasBeard->getRow($id);
            $this->assertFalse(!$got_row);
            $row = json_decode($contentValid[3], true);
            foreach($row as $key => $val){
                $this->assertEquals($val, $got_row[$key]);
            }
        }

        /**
         * @expectedException Json\ValidationException
         */
        public function test_setRowInvalid(){
            global $testSchema, $contentValid, $contentInvalid;

            $table = $this->createTestTable(1, 'test', $testSchema);
            $id = $this->JACKED->DatasBeard->createRow($table['uuid'], json_decode($contentValid[2]));

            $this->JACKED->DatasBeard->setRow($id, json_decode($contentInvalid[3], true));
        }

        public function test_deleteRow(){
            global $testSchema, $contentValid;

            $table = $this->createTestTable(1, 'test', $testSchema);
            $id = $this->JACKED->DatasBeard->createRow($table['uuid'], json_decode($contentValid[1]));

            $this->JACKED->DatasBeard->deleteRow($id);

            // verify the soft deleted row doesnt show up in normal results
            $got_row = $this->JACKED->DatasBeard->getRows($table['uuid']);
            $this->assertTrue(!$got_row);

            // verify we can still see it if we use onlyActive = false
            $got_row = $this->JACKED->DatasBeard->getRow($id, true);
            $this->assertEquals($got_row['uuid'], $id);
        }
    }
?>