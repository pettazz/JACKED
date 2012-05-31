<?php

    class Blag extends JACKEDModule{
        /*
                http://xkcd.com/148/

                Superseriously straightforward blog stuff.
        */
    
        const moduleName = 'Blag';
        const moduleVersion = 1.0;
        public static $dependencies = array('MySQL', 'Flock');
        

        /**
        * Get all the data for a post by GUID
        * 
        * @param $guid String GUID of the post to get
        * @param $only_active Boolean Whether to only get posts that have not been deactivated
        * @return Array List of all post data, false if GUID not found
        */
        public function getPost($guid, $only_active = true){
            $fields = array('guid', 'posted', 'title', 'headline', 'content');
            $cond = $only_active? ' AND alive = 1' : '';
            switch($this->config->author_name_type){
                case 'full':
                    $fields[] = 'last_name';
                    $fields[] = 'first_name';
                    break;
                case 'first':
                    $fields[] = 'first_name';
                    break;
                case 'user':
                    $fields[] = 'username';
                    break;
                default:
                    $fields[] = 'first_name';
                    break;
            }
            return $this->JACKED->MySQL->getJoin(
                $fields, 'INNER', 
                $this->config->dbt_posts,
                $this->JACKED->Flock->config->dbt_users,
                'User', 'guid',
                'guid = ' . $guid . $cond
            );
        }

        /*

        getPosts()

        getPostsWithinTimeRange() 
                        add timedelta helpers to Util 

        getPostsByAuthor()

        */
    }

?>