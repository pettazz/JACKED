<?php
    require_once('test/jacked_test_conf.php');
     
    class SyrupTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Syrup, Testur, MySQL, Karma");
            
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
            $this->JACKED->MySQL->query('DELETE FROM BlagCategory WHERE 1');
            $this->JACKED->MySQL->query('DELETE FROM User WHERE 1');
        }


        public function test_find(){
            $data = $this->JACKED->Testur->createPost();
            $posts = $this->JACKED->Syrup->Blag->find(array('alive' => 1));
            $this->assertEquals(1, count($posts));
            $this->assertFalse(!$posts);
            foreach($data as $key => $val){
                $post = $posts[0];
                if($key !== 'author'){
                    if($key == 'category'){
                        $this->assertEquals($val, $post->category->guid);
                    }else{
                        $this->assertEquals($val, $post->$key);
                    }
                }else{
                    unset($val['password']); //TODO: test that password isnt returned
                    foreach($val as $aKey => $aVal){
                        $this->assertEquals($aVal, $post->$key->$aKey);
                    }
                }
            }
        }

        public function test_findOne(){
            $data = $this->JACKED->Testur->createPost(1);
            $data2 = $this->JACKED->Testur->createPost(2);
            $data3 = $this->JACKED->Testur->createPost(3);

            $post = $this->JACKED->Syrup->Blag->findOne(array('alive' => 1), array('field' => 'posted', 'direction' => 'DESC'));
            $this->assertEquals(1, count($post));

            $this->assertEquals($post->guid, $data3['guid']);
        }

        public function test_findBy(){
            $data = $this->JACKED->Testur->createPost();
            $results = $this->JACKED->Syrup->Blag->findByguid($data['guid']);
            $result = $results[0];

            foreach($data as $key => $val){
                if($key !== 'author'){
                    if($key == 'category'){
                        $this->assertEquals($data[$key], $result->$key->guid);
                    }else{
                        $this->assertEquals($data[$key], $result->$key);
                    }
                }
            }

            $data2 = $this->JACKED->Testur->createPost();
            $result2 = $this->JACKED->Syrup->Blag->findByalive(1);
            $this->assertEquals(2, count($result2));

            $result3 = $this->JACKED->Syrup->Blag->findByAliveAndGuid(1, $data['guid']);
            $result3 = $result3[0];

            foreach($data as $key => $val){
                if($key !== 'author'){
                    if($key == 'category'){
                        $this->assertEquals($data[$key], $result3->$key->guid);
                    }else{
                        $this->assertEquals($data[$key], $result3->$key);
                    }
                }
            }
        }

        public function test_count(){
            $this->assertEquals(0, $this->JACKED->Syrup->Blag->count(array('alive' => '1')));

            $data = $this->JACKED->Testur->createPost();
            $this->assertEquals(1, $this->JACKED->Syrup->Blag->count(array('alive' => '1')));

            $data = $this->JACKED->Testur->createPost();
            $data = $this->JACKED->Testur->createPost();
            $this->assertEquals(3, $this->JACKED->Syrup->Blag->count());
        }

        public function test_saveNewNonRelational(){
            //without relational data
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

            $rows = $this->JACKED->MySQL->getRows('Blag');
            $row = $rows[0];

            $this->assertEquals($row['guid'], $newpost->guid);
            $this->assertEquals($row['author'], $author['guid']);
            $this->assertEquals($row['title'], "JOE BIDEN");
            $this->assertEquals($row['headline'], "MATH GENUIS");
            $this->assertEquals($row['posted'], $timestamp);
            $this->assertEquals($row['content'], "HEY EVERYONE!\n\n\nLOL!");
        }

        public function test_saveNewRelational(){
            //with Author as relational data
            $newpost = $this->JACKED->Syrup->Blag->create();
            $author = $this->JACKED->Syrup->User->create();

            $authorData = $this->JACKED->Testur->generateFlockUser('lol', false);

            $author->email = $authorData['email'];
            $author->password = $authorData['password'];
            $author->username = $authorData['username'];
            $author->first_name = $authorData['first_name'];
            $author->last_name = $authorData['last_name'];

            $newpost->author = $author;
            $newpost->title = "JOE BIDEN";
            $newpost->headline = "MATH GENUIS";
            $timestamp = time();
            $newpost->posted = $timestamp;
            $newpost->content = "HEY EVERYONE!\n\n\nLOL!";

            $newpost->save();

            $rows = $this->JACKED->MySQL->getRows('Blag');
            $row = $rows[0];

            $this->assertEquals($row['guid'], $newpost->guid);
            $this->assertEquals($row['author'], $author->guid);
            $this->assertEquals($row['title'], "JOE BIDEN");
            $this->assertEquals($row['headline'], "MATH GENUIS");
            $this->assertEquals($row['posted'], $timestamp);
            $this->assertEquals($row['content'], "HEY EVERYONE!\n\n\nLOL!");

            $rows = $this->JACKED->MySQL->getRows('User');
            $row = $rows[0];

            $this->assertEquals($row['guid'], $author->guid);
            $this->assertEquals($row['email'], $authorData['email']);
            $this->assertEquals($row['username'], $authorData['username']);
            $this->assertEquals($row['first_name'], $authorData['first_name']);
            $this->assertEquals($row['last_name'], $authorData['last_name']);
        }

        public function test_saveExistingWithoutRelation(){
            $data = $this->JACKED->Testur->createPost();
            $post = $this->JACKED->Syrup->Blag->findOne(array('guid' => $data['guid']), NULL, false);

            $data['title'] = 'HEY GUISE!'; 
            $post->title = 'HEY GUISE!';
            $post->save();

            $rows = $this->JACKED->MySQL->getRows('Blag');
            $row = $rows[0];

            foreach($data as $key => $val){
                if($key !== 'author'){
                    $this->assertEquals($data[$key], $row[$key]);
                }
            }
        }

        public function test_saveExistingWithRelation(){
            $data = $this->JACKED->Testur->createPost();
            $post = $this->JACKED->Syrup->Blag->findOne(array('guid' => $data['guid']), NULL);

            $data['title'] = 'HEY GUISE!'; 
            $post->title = 'HEY GUISE!';
            $post->author->username = 'pooplol';
            $data['author']['username'] = 'pooplol';
            $post->save();

            $rows = $this->JACKED->MySQL->getRows('Blag');
            $row = $rows[0];

            foreach($data as $key => $val){
                if($key !== 'author'){
                    $this->assertEquals($data[$key], $row[$key]);
                }
            }

            $rows = $this->JACKED->MySQL->getRows('User');
            $row = $rows[0];

            unset($data['author']['password']); //TODO: test that password isnt returned
            foreach($data['author'] as $key => $val){
                $this->assertEquals($data['author'][$key], $row[$key]);
            }
        }

        public function test_delete(){
            $data = $this->JACKED->Testur->createPost();
            $post = $this->JACKED->Syrup->Blag->findOne(array('guid' => $data['guid']), NULL, false);
            $post->delete();

            $rows = $this->JACKED->MySQL->getRows('Blag');
            $returned_rows = (is_array($rows))? count($rows) : 0;
            $this->assertTrue($returned_rows == 0);
        }
    }
?>