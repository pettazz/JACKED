<?php
    require_once('test/jacked_test_conf.php');
     
    class BlagTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Blag, Testur, MySQL");
            
            $this->JACKED->MySQL->config->db_host = 'localhost';
            $this->JACKED->MySQL->config->db_user = 'root';
        }

        public function tearDown(){
            $this->JACKED->MySQL->query('DELETE FROM Blag WHERE 1');
            $this->JACKED->MySQL->query('DELETE FROM User WHERE 1');
        }


        public function test_getPost(){
            $post = $this->JACKED->Testur->createPost();
            $got_post = $this->JACKED->Blag->getPost($post['guid']);
            $this->assertFalse(!$got_post);
            foreach($post as $key => $val){
                if($key == 'author'){
                    //$this->assertEquals($val, $got_post[$key]);
                }else{
                    $this->assertEquals($val, $got_post[$key]);
                }
            }
        }

        

        // public function test_getPosts(){

        // }

        

        // public function test_getPostsByAuthor(){

        // }

        

        // public function test_getPostsByTimeRange(){

        // }
    }
?>