<?php
    require_once('test/jacked_test_conf.php');
     
    class BlagTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Blag, Testur, MySQL");
            
            $this->JACKED->MySQL->config->db_host = 'localhost';
            $this->JACKED->MySQL->config->db_user = 'root';

            $syrupDConf = $this->JACKED->Syrup->config->driverConfig;
            $syrupDConf['db_host'] = 'localhost';
            $syrupDConf['db_user'] = 'root';
            $syrupDConf['db_pass'] = '';
            $syrupDConf['model_root'] = 'Syrup/models/';
            $this->JACKED->Syrup->config->driverConfig = $syrupDConf;
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
                    $this->assertEquals($val, $got_post->$key);
                }
            }
        }

        

        public function test_getPosts(){
            $fixtures = array();
            for($i = 0; $i < 3; $i++){
                $fixtures[$i] = $this->JACKED->Testur->createPost(3 - $i);
            }

            $got_posts = $this->JACKED->Blag->getPosts();
            $this->assertFalse(!$got_posts);
            for($i = 0; $i < 3; $i++){
                $test_post = $got_posts[$i];
                $post = $fixtures[$i];
                foreach($post as $key => $val){
                    if($key == 'author'){
                        //$this->assertEquals($val, $got_post[$key]);
                    }else{
                        $this->assertEquals($val, $test_post->$key);
                    }
                }
            }
        }

        

        public function test_getPostsByAuthor(){
            $author_fixtures = array();
            for($i = 0; $i < 3; $i++){
                $author_fixtures[$i] = $this->JACKED->Testur->generateFlockUser();
            }
            $post_fixtures = array();
            for($i = 0; $i < 3; $i++){
                $post_fixtures[$i] = $this->JACKED->Testur->createPost(3 - $i, $author_fixtures[$i]);
            }
            
            for($i = 0; $i < 3; $i++){
                $got_posts = $this->JACKED->Blag->getPostsByAuthor($author_fixtures[$i]['guid']);
                $this->assertEquals(1, count($got_posts));
                $test_post = $got_posts[0];
                $post = $post_fixtures[$i];
                foreach($post as $key => $val){
                    if($key == 'author'){
                        //$this->assertEquals($val, $got_post[$key]);
                    }else{
                        $this->assertEquals($val, $test_post->$key);
                    }
                }
            }

            $author = $this->JACKED->Testur->generateFlockUser();
            $post_fixtures = array();
            for($i = 0; $i < 3; $i++){
                $post_fixtures[$i] = $this->JACKED->Testur->createPost(3 - $i, $author);
            }

            unset($test_post);
            unset($post);
            $got_posts = $this->JACKED->Blag->getPostsByAuthor($author['guid']);
            $this->assertEquals(3, count($got_posts));
            foreach($post_fixtures as $i => $post){
                $test_post = $got_posts[$i];
                foreach($post as $key => $val){
                    if($key == 'author'){
                        //$this->assertEquals($val, $got_post[$key]);
                    }else{
                        $this->assertEquals($val, $test_post->$key);
                    }
                }
            }
        }

        

        public function test_getPostsByTimeRange(){
            $fixtures = array();
            for($i = 0; $i < 3; $i++){
                $fixtures[$i] = $this->JACKED->Testur->createPost(3 - $i);
            }

            $got_posts = $this->JACKED->Blag->getPosts();
            $this->assertFalse(!$got_posts);
            for($i = 0; $i < 3; $i++){
                $test_post = $got_posts[$i];
                $post = $fixtures[$i];
                foreach($post as $key => $val){
                    if($key == 'author'){
                        //$this->assertEquals($val, $got_post[$key]);
                    }else{
                        $this->assertEquals($val, $test_post->$key);
                    }
                }
            }
        }
    }
?>