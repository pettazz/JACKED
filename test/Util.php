<?php
    require_once 'PHPUnit/Autoload.php';

    require('test/jacked_test_conf.php');
     
    class UtilTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED();
        }

        public function test_validateEmail(){
            $validEmail = array(
                'pope12.Lol@some-mail.webs.com',
                'pettazz@gmail.com',
                'pope+lol@gmail.web.org',
                '5768980@4635716287921.net'
            );
            $invalidEmail = array(
                'poop', 
                '',
                'noway.webs',
                'test@reddit.com/r/spaceclop',
                '@lol',
                'shenan[]igans@crap.com'
            );
     
            foreach($validEmail as $email){
                $this->assertTrue($this->JACKED->Util->validateEmail($email));
            }

            foreach($invalidEmail as $email){
                $this->assertFalse($this->JACKED->Util->validateEmail($email));
            }
            
        }

        public function test_array_key_exists_recursive(){
            $fixture = array(
                'test' => 3,
                'hats' => 'banana',
                'crap' => array(
                    'hahaha' => 'oh wow',
                    'two' => 2,
                    'three' => array('teststuff', 532),
                10 => 'ten'
                )
            );

            $this->assertTrue($this->JACKED->Util->array_key_exists_recursive('test', $fixture));
            $this->assertTrue($this->JACKED->Util->array_key_exists_recursive('hahaha', $fixture));
            $this->assertTrue($this->JACKED->Util->array_key_exists_recursive(10, $fixture));

            $this->assertFalse($this->JACKED->Util->array_key_exists_recursive('teststuff', $fixture));
            $this->assertFalse($this->JACKED->Util->array_key_exists_recursive(3, $fixture));
            $this->assertFalse($this->JACKED->Util->array_key_exists_recursive('oh wow', $fixture));
        }
    }
?>