<?php
    require_once('test/jacked_test_conf.php');
     
    class SyrupTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Syrup, Testur, MySQL");
            
            $this->JACKED->MySQL->config->db_host = 'localhost';
            $this->JACKED->MySQL->config->db_user = 'root';

            $syrupDConf = $this->JACKED->Syrup->config->driverConfig;
            $syrupDConf['db_host'] = 'localhost';
            $syrupDConf['db_user'] = 'root';
            $syrupDConf['db_pass'] = '';
            $this->JACKED->Syrup->config->driverConfig = $syrupDConf;
        }

        public function tearDown(){
            $this->JACKED->MySQL->query('DELETE FROM BlagPost WHERE 1');
            $this->JACKED->MySQL->query('DELETE FROM User WHERE 1');
        }

        private function createPost(){
            $content = '';
            for($x = 0; $x <= rand(0, 5); $x++){
                $content .= $this->JACKED->Testur->generateParagraph();
            }
            $posted = rand(1022967819, time());
            $author = $this->JACKED->Testur->generateFlockUser();
            $details = array(
                'guid' => $this->JACKED->Util->uuid4(),
                'author' => $author['guid'],
                'title' => ucfirst($this->JACKED->Testur->generateSentence(false)),
                'headline' => ucfirst($this->JACKED->Testur->generateSentence(false)),
                'posted' => $posted,
                'content' => $content
            );
            $this->JACKED->MySQL->insert('BlagPost', $details);
            $details['author'] = $author;
            return $details;
        }



        public function test_find(){
            $data = $this->createPost();
            $post = $this->JACKED->Syrup->Blag->find(array('alive' => 1));
            $this->assertFalse(!$post);
            foreach($data as $key => $val){
                print_r($post->$key);
                $this->assertEquals($val, $post->$key);
            }
        }

        public function test_findOne(){
            $this->assertTrue(true);
        }

        public function test_count(){
            $this->assertEquals(0, $this->JACKED->Syrup->Blag->count(array('alive' => '1')));
        }

        public function test_saveNew(){
            $this->assertTrue(true);
        }

        public function test_saveExisting(){
            $this->assertTrue(true);
        }

        public function test_delete(){
            $this->assertTrue(true);
        }
    }
?>