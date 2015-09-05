<?php
    require_once('test/jacked_test_conf.php');
     
    class BlagTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Blag, Testur, MySQL");
            
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
            $this->JACKED->MySQL->query('DELETE FROM Blag WHERE 1');
            $this->JACKED->MySQL->query('DELETE FROM User WHERE 1');
        }


        public function test_getPost(){
            $post = $this->JACKED->Testur->createPost();
            $got_post = $this->JACKED->Blag->getPost($post['guid']);
            $this->assertFalse(!$got_post);
            foreach($post as $key => $val){
                if($key == 'author' || $key == 'category'){
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
                    if($key == 'author' || $key == 'category'){
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
                    if($key == 'author' || $key == 'category'){
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
                    if($key == 'author' || $key == 'category'){
                        //$this->assertEquals($val, $got_post[$key]);
                    }else{
                        $this->assertEquals($val, $test_post->$key);
                    }
                }
            }
        }

        

        public function test_getPostsByTimeRange(){
            $fixtures = array();
            for($i = 1; $i < 21; $i++){
                $fixtures[$i] = $this->JACKED->Testur->createPost($i);
            }

            $got_posts = $this->JACKED->Blag->getPostsByTimeRange(3, 12);
            $posts_group = array_slice($fixtures, 2, 10);
            $this->assertEquals(10, count($got_posts));
            for($i = 1; $i < 10; $i++){
                $test_post = $got_posts[9 - $i];
                $post = $posts_group[$i];
                foreach($post as $key => $val){
                    if($key == 'author' || $key == 'category'){
                        //$this->assertEquals($val, $got_post[$key]);
                    }else{
                        $this->assertEquals($val, $test_post->$key);
                    }
                }
            }

            $got_posts = $this->JACKED->Blag->getPostsByTimeRange(5, false, 14);
            $posts_group = array_slice($fixtures, 6, 14);
            $this->assertEquals(14, count($got_posts));
            for($i = 0; $i < 13; $i++){
                $test_post = $got_posts[13 - $i];
                $post = $posts_group[$i];
                foreach($post as $key => $val){
                    if($key == 'author' || $key == 'category'){
                        //$this->assertEquals($val, $got_post[$key]);
                    }else{
                        $this->assertEquals($val, $test_post->$key);
                    }
                }
            }            
        }
    }
?>