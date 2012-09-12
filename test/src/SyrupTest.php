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
                $content .= $this->JACKED->Testur->generateSentence();
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
            $posts = $this->JACKED->Syrup->Blag->find(array('alive' => 1));
            $this->assertEquals(1, count($posts));
            $this->assertFalse(!$posts);
            foreach($data as $key => $val){
                $post = $posts[0];
                if($key !== 'author'){
                    $this->assertEquals($val, $post->$key);
                }
            }
        }

        public function test_findOne(){
            $data = $this->createPost();
            $data2 = $this->createPost();
            $data3 = $this->createPost();

            $post = $this->JACKED->Syrup->Blag->findOne(array('alive' => 1), array('field' => 'posted', 'direction' => 'DESC'));
            $this->assertEquals(1, count($post));

            $this->assertEquals($post->guid, $data3['guid']);
        }

        public function test_findBy(){
            $data = $this->createPost();
            $result = $this->JACKED->Syrup->Blag->findByguid($data['guid']);
            $result = $result[0];

            foreach($data as $key => $val){
                if($key !== 'author'){
                    $this->assertEquals($data[$key], $result->$key);
                }
            }

            $data2 = $this->createPost();
            $result2 = $this->JACKED->Syrup->Blag->findByalive(1);
            $this->assertEquals(2, count($result2));

            $result3 = $this->JACKED->Syrup->Blag->findByAliveAndGuid(1, $data['guid']);
            $result3 = $result3[0];

            foreach($data as $key => $val){
                if($key !== 'author'){
                    $this->assertEquals($data[$key], $result3->$key);
                }
            }
        }

        public function test_count(){
            $this->assertEquals(0, $this->JACKED->Syrup->Blag->count(array('alive' => '1')));

            $data = $this->createPost();
            $this->assertEquals(1, $this->JACKED->Syrup->Blag->count(array('alive' => '1')));

            $data = $this->createPost();
            $data = $this->createPost();
            $this->assertEquals(3, $this->JACKED->Syrup->Blag->count());
        }

        public function test_saveNew(){
            $newpost = $this->JACKED->Syrup->Blag->create();
            $author = $this->JACKED->Testur->generateFlockUser();
            $newpost->author = $author['guid'];
            $newpost->title = "JOE BIDEN";
            $newpost->headline = "MATH GENUIS";
            $timestamp = time();
            $newpost->posted = $timestamp;
            $newpost->content = "HEY EVERYONE!\n\n\nLOL!";
            $newpost->save();

            $this->assertNotNull($newpost->guid);

            $rows = $this->JACKED->MySQL->getRows('BlagPost');
            $row = $rows[0];

            $this->assertEquals($row['guid'], $newpost->guid);
            $this->assertEquals($row['author'], $author['guid']);
            $this->assertEquals($row['title'], "JOE BIDEN");
            $this->assertEquals($row['headline'], "MATH GENUIS");
            $this->assertEquals($row['posted'], $timestamp);
            $this->assertEquals($row['content'], "HEY EVERYONE!\n\n\nLOL!");
        }

        public function test_saveExisting(){
            $this->assertTrue(true);
        }

        public function test_delete(){
            $this->assertTrue(true);
        }
    }
?>