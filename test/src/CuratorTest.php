<?php
    require_once('test/jacked_test_conf.php');
     
    class CuratorTest extends PHPUnit_Framework_TestCase{
        public function setUp(){
            $this->JACKED = new JACKED("Curator, Testur, MySQL");
            
            $this->JACKED->MySQL->config->db_host = 'localhost';
            $this->JACKED->MySQL->config->db_user = 'root';
            $this->JACKED->MySQL->config->db_name = 'jacked_test';
        }

        public function tearDown(){
            $this->JACKED->MySQL->query('DELETE FROM Curator WHERE 1');
            $this->JACKED->MySQL->query('DELETE FROM CuratorRelation WHERE 1');
        }


        public function test_assignNewTagByNameSingle(){
            $target = 'hats';

            $this->JACKED->Curator->assignTagByName($target, 'butts, lol');

            $check_tags = $this->JACKED->MySQL->getRows('Curator');
            
            $this->assertTrue($check_tags[0]['name'] == 'butts, lol');
            $this->assertTrue($check_tags[0]['canonicalName'] == 'butts-lol');
            $this->assertTrue($check_tags[0]['usage'] == 1);

            $check_tagrels = $this->JACKED->MySQL->getRows('CuratorRelation');

            $this->assertTrue($check_tagrels[0]['Curator'] == $check_tags[0]['guid']);
            $this->assertTrue($check_tagrels[0]['target'] == 'hats');
        }

        public function test_removeTagByName(){
            $target = 'hats';

            $this->JACKED->Curator->assignTagByName($target, 'butts, lol');
            $this->JACKED->Curator->removeTagByName($target, 'butts, lol');

            $check_tags = $this->JACKED->MySQL->getRows('Curator');
            
            $this->assertTrue($check_tags[0]['name'] == 'butts, lol');
            $this->assertTrue($check_tags[0]['canonicalName'] == 'butts-lol');
            $this->assertTrue($check_tags[0]['usage'] == 0);

            $check_tagrels = $this->JACKED->MySQL->getRows('CuratorRelation');

            $this->assertFalse($check_tagrels);
        }

        public function test_assignNewTagsByName(){
            $target1 = 'hats';
            $target2 = 'shirts';

            $tag_set1 = array('butts, lol', 'banana', 'hammock');
            $tag_set2 = array('butts, lol', 'waffle', 'hammock');

            $this->JACKED->Curator->assignTagByName($target1, $tag_set1);
            $this->JACKED->Curator->assignTagByName($target2, $tag_set2);


            $check_tag = $this->JACKED->MySQL->getRows('Curator', 'name = "butts, lol"');
            $this->assertTrue($check_tag[0]['name'] == 'butts, lol');
            $this->assertTrue($check_tag[0]['canonicalName'] == 'butts-lol');
            $this->assertTrue($check_tag[0]['usage'] == 2);

            $check_tagrel = $this->JACKED->MySQL->getRows('CuratorRelation', 'Curator = "' . $check_tag[0]['guid'] . '"');
            $check_guids = array($check_tagrel[0]['target'], $check_tagrel[1]['target']);
            $compare_guids = array($target1, $target2);
            $this->assertTrue($check_guids == $compare_guids);
        }

        public function test_getTagByCanonicalName(){
            $target = 'hats';

            $this->JACKED->Curator->assignTagByName($target, 'butts, lol');

            $check_tags = $this->JACKED->MySQL->getRows('Curator');
            
            $this->assertTrue($check_tags[0]['name'] == 'butts, lol');
            $this->assertTrue($check_tags[0]['canonicalName'] == 'butts-lol');
            $this->assertTrue($check_tags[0]['usage'] == 1);
        }

        public function test_getTagsForTarget(){
            $target1 = 'hats';
            $target2 = 'shirts';

            $tag_set1 = array('butts, lol', 'banana', 'hammock');
            $tag_set2 = array('butts, lol', 'waffle', 'hammock');

            $this->JACKED->Curator->assignTagByName($target1, $tag_set1);
            $this->JACKED->Curator->assignTagByName($target2, $tag_set2);

            $tags1_check = $this->JACKED->Curator->getTagsForTarget($target1);
            foreach($tag_set1 as $tagname){
                $this->assertTrue(
                    ($tags1_check[0]['name'] == $tagname) ||
                    ($tags1_check[1]['name'] == $tagname) ||
                    ($tags1_check[2]['name'] == $tagname)
                );
            }

            $tags2_check = $this->JACKED->Curator->getTagsForTarget($target2);
            foreach($tag_set2 as $tagname){
                $this->assertTrue(
                    ($tags2_check[0]['name'] == $tagname) ||
                    ($tags2_check[1]['name'] == $tagname) ||
                    ($tags2_check[2]['name'] == $tagname)
                );
            }
        }

    }
?>