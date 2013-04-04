<?php

    class Blag extends JACKEDModule{
        /*
                http://xkcd.com/148/

                Superseriously straightforward blog stuff.
        */
    
        const moduleName = 'Blag';
        const moduleVersion = 1.0;
        public static $dependencies = array('MySQL', 'Flock', 'Syrup');
        

        /**
        * Get all the data for a post by GUID
        * 
        * @param $guid String GUID of the post to get
        * @param $only_active Boolean Whether to only get posts that have not been deactivated
        * @return Array List of all post data, false if GUID not found
        */
        public function getPost($guid, $only_active = true){
            $where = array('guid' => $guid);
            if($only_active){
                $where['alive'] = 1;
            }
            return $this->JACKED->Syrup->Blag->findOne($where);
            /*$fields1 = array('guid', 'posted', 'title', 'headline', 'content');
            $fields2 = false;
            $cond = $only_active? ' AND `' . $this->config->dbt_posts . '`.`alive` = 1' : '';
            switch($this->config->author_name_type){
                case 'full':
                    $fields2 = array('last_name', 'first_name');
                    break;
                case 'first':
                    $fields2 = array('first_name');
                    break;
                case 'user':
                    $fields2 = array('username');
                    break;
                default:
                    $fields2 = array('first_name');
                    break;
            }
            $result = $this->JACKED->MySQL->getJoin(
                $fields1, $fields2, 'INNER', 
                $this->config->dbt_posts,
                $this->JACKED->Flock->config->dbt_users,
                'author', 'guid',
                '`' . $this->config->dbt_posts . '`.`guid` = \'' . $guid . '\'' .  $cond
            );
            return $result? $result[0] : false;*/
        }

        /**
        * Get all the data for a number of posts
        * 
        * @param $count int [optional] Number of posts to get. 0 will return all. Defaults to 10
        * @param $paged int [optional] Which page of posts to retrieve for paginated results. Defaults to 1
        * @param $cond String [optional] A WHERE clause to filter posts by. Ex: "`table`.`guid` = 'lol-123'"
        * @param $only_active Boolean Whether to only get posts that have not been deactivated. Defaults to true
        * @param $order String [optional] Order by date ascending or descending. One of: 'asc', 'desc'. Defaults to desc  
        * @return Array List of Arrays of data for each post found
        */
        public function getPosts($count = 10, $paged = 1, $cond = false, $only_active = true, $order = 'desc'){
            $fields1 = array('guid', 'posted', 'title', 'headline', 'content');
            $fields2 = false;
            $cond = $cond? $cond . ' AND ': '';
            $cond .= $only_active? '`' . $this->config->dbt_posts . '`.`alive` = 1' : '';
            $cond .= ' ORDER BY \'posted\' ' . (($order == 'asc')? 'ASC' : 'DESC');
            if($count > 0){
                $cond .= $this->JACKED->MySQL->paginator($count, $paged);
            }
            switch($this->config->author_name_type){
                case 'full':
                    $fields2 = array('last_name', 'first_name');
                    break;
                case 'first':
                    $fields2 = array('first_name');
                    break;
                case 'user':
                    $fields2 = array('username');
                    break;
                default:
                    $fields2 = array('first_name');
                    break;
            }
            return $this->JACKED->MySQL->getJoin(
                $fields1, $fields2, 'INNER', 
                $this->config->dbt_posts,
                $this->JACKED->Flock->config->dbt_users,
                'author', 'guid',
                $cond
            );
        }

        /**
        * Get all the data for a number of posts by a given author
        * 
        * @param $author_guid String The Flock User GUID of the author.
        * @param $count int [optional] Number of posts to get. 0 will return all. Defaults to 10
        * @param $paged int [optional] Which page of posts to retrieve for paginated results. Defaults to 1
        * @param $only_active Boolean Whether to only get posts that have not been deactivated. Defaults to true
        * @param $order String [optional] Order by date ascending or descending. One of: 'asc', 'desc'. Defaults to desc  
        * @return Array List of Arrays of data for each post found
        */
        public function getPostsByAuthor($author_guid, $count = 10, $paged = 1, $only_active = true, $order = 'desc'){
            $cond = '`' . $this->config->dbt_posts . '`.`author` = \'' . $author_guid . '\'';
            return $this->getPosts($count, $paged, $cond, $only_active, $order);
        }

        /**
        * Get all the data for a number of posts within a given time frame
        * 
        * @param $time_oldest int Oldest timestamp to get.
        * @param $time_newest int [optional] Newest timestamp to get. Defaults to now.
        * @param $count int [optional] Number of posts to get. 0 will return all. Defaults to 10
        * @param $paged int [optional] Which page of posts to retrieve for paginated results. Defaults to 1
        * @param $only_active Boolean Whether to only get posts that have not been deactivated. Defaults to true
        * @param $order String [optional] Order by date ascending or descending. One of: 'asc', 'desc'. Defaults to desc  
        * @return Array List of Arrays of data for each post found
        */
        public function getPostsByTimeRange($time_oldest, $time_newest = false, $count = 10, $paged = 1, $only_active = true, $order = 'desc'){
            $time_newest = $time_newest? $time_newest : time();
            $cond = '`' . $this->config->dbt_posts . '`.`posted` >= \'' . $time_oldest . '\' ';
            $cond .= 'AND `' . $this->config->dbt_posts . '`.`posted` <= \'' . $time_newest . '\'';
            return $this->getPosts($count, $paged, $cond, $only_active, $order);
        }
    }

?>