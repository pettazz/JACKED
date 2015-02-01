<?php

    class Blag extends JACKEDModule{
        /*
                http://xkcd.com/148/

                Superseriously straightforward blog stuff.
        */
    
        const moduleName = 'Blag';
        const moduleVersion = 1.1;
        public static $dependencies = array('MySQL', 'Syrup');
        

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
        }

        /**
        * Get all the data for a number of posts
        * 
        * @param $count int [optional] Number of posts to get. 0 will return all. Defaults to 10
        * @param $paged int [optional] Which page of posts to retrieve for paginated results. Defaults to 1
        * @param $cond Array [optional] A list of key value pairs to filter posts by. Ex: array('author' => '53', 'title' => 'LOL, BUTTS')
        * @param $only_active Boolean Whether to only get posts that have not been deactivated. Defaults to true
        * @param $order String [optional] Order by date ascending or descending. One of: 'asc', 'desc'. Defaults to desc  
        * @return Array List of Arrays of data for each post found
        */
        public function getPosts($count = 10, $paged = 1, $cond = false, $only_active = true, $order = 'desc'){
            $limit = $count;
            $offset = $count * ($paged - 1);
            $where = $cond;
            $order = array('field' => 'posted', 'direction' => $order);
            if($only_active){
                if($where){
                    $where = array('AND' => array_merge(array('alive' => 1), $where));
                }else{
                    $where = array('alive' => 1);
                }
            }
            
            return $this->JACKED->Syrup->Blag->find($where, $order, $limit, $offset);
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
            $cond = array('author.guid' => $author_guid);
            return $this->getPosts($count, $paged, $cond, $only_active, $order);
        }

        /**
        * Get all the data for a number of posts by a given category
        * 
        * @param $category_guid String The GUID of the category.
        * @param $count int [optional] Number of posts to get. 0 will return all. Defaults to 10
        * @param $paged int [optional] Which page of posts to retrieve for paginated results. Defaults to 1
        * @param $only_active Boolean Whether to only get posts that have not been deactivated. Defaults to true
        * @param $order String [optional] Order by date ascending or descending. One of: 'asc', 'desc'. Defaults to desc  
        * @return Array List of Arrays of data for each post found
        */
        public function getPostsByCategory($category_guid, $count = 10, $paged = 1, $only_active = true, $order = 'desc'){
            $cond = array('category.guid' => $category_guid);
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
            $cond = array(
                'AND' => array('posted >= ?' => $time_oldest,
                               'posted <= ?' => $time_newest)
            );
            return $this->getPosts($count, $paged, $cond, $only_active, $order);
        }
    }

?>