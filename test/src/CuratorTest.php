<?php
    require_once('test/jacked_test_conf.php');
     
    class CuratorTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Curator, Testur, MySQL");
            
            $this->JACKED->MySQL->config->db_host = 'localhost';
            $this->JACKED->MySQL->config->db_user = 'root';
        }

        public function tearDown(){
            $this->JACKED->MySQL->query('DELETE FROM Curator WHERE 1');
            $this->JACKED->MySQL->query('DELETE FROM CuratorRelation WHERE 1');
        }


        public function test_assignTagByNameSingle(){
            $target = 'hats';

            $this->JACKED->Curator->assignTagByName($target, 'butts, lol');

            $check_tags = $this->JACKED->MySQL->getRows('Curator');
            
            $this->assertTrue($check_tags[0]['name'] == 'butts, lol');
            $this->assertTrue($check_tags[0]['usage'] == 1);

            $check_tagrels = $this->JACKED->MySQL->getRows('CuratorRelation');

            $this->assertTrue($check_tagrels[0]['Curator'] == $check_tags[0]['guid']);
            $this->assertTrue($check_tagrels[0]['target'] == 'hats');
        }

        

    }
?>