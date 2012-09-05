<?php
    require_once('test/jacked_test_conf.php');

    require_once('PHPUnit/Autoload.php');
     
    class SyrupTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Syrup");
        }

        public function test_nothing(){
            $this->assertTrue(true);
        }
    }
?>