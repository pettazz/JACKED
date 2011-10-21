<?php

    class Society extends JACKEDModule{
        const moduleName = 'Society';
        const moduleVersion = 1.0;
        const dependencies = 'MySQL, Flock, Sessions';
        const optionalDependencies = '';
        
        ///////////////////////////////////
        //              FRIENDSHIP!             //
               ///////////////////////////////////
        
        /**
        * Checks if the user is already friends with a given user
        * 
        * @param int $user User ID to check friendship with
        * @throws NotLoggedInException if the user is not logged in
        * @return Boolean Whether the user is friends with the given user 
        */
        public function checkFriendship($user){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $result = $this->JACKED->MySQL->query(
                "SELECT id FROM 
                " . $this->config->dbt_friends . " f1 
                INNER JOIN 
                " . $this->config->dbt_friends . " f2 
                ON 
                    f1.user_id = f2.friend_id AND
                    f1.friend_id = f2.user_id"
            );
            return (bool)$done = $this->JACKED->MySQL->parseResult($result);
        }
        
        /**
        * Checks if the user is already friends with a given user,
        * throws an exception if not
        * 
        * @param int $user User ID to check friendship with
        * @throws NotFriendsException if the users are not friends
        * @return Boolean True
        */
        public function requireFriendship($user){
            if(!$this->checkFriendship($user))
                throw new NotFriendsException();
            return true;
        }
        
        /**
        * Gets a list of any pending friend requests to the user
        * 
        * @throws NotLoggedInException if the user is not logged in
        * @return Array of pending request IDs to the user
        */
        public function getFriendRequests(){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return $this->JACKED->MySQL->getAllVals(
                array('id'),
                $this->config->dbt_friends,
                'pending = 1 AND friend_id = ' . $userid
            );
        }
        
        /**
        * Gets a list of the ids of the user's friends
        * 
        * @throws NotLoggedInException if the user is not logged in
        * @return Array List of friend ids
        */
        public function getFriends(){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return $this->JACKED->MySQL->getAllVals(
                array('friend_id'),
                $this->config->dbt_friends,
                'pending = 0 AND user_id = ' . $userid
            );
        }
        
        /**
        * Sends a friend request to a user
        * 
        * @param int $user User ID to send a friend request to
        * @throws NotLoggedInException if the user is not logged in
        * @throws AlreadyFriendsException If the users are already friends
        * @return int ID of the newly requested friendship
        */
        public function sendRequest($user){
            if($this->checkFriendship($user)){
                throw new AlreadyFriendsException();
            }
            
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));

            return $this->MySQL->insertValues(
                $this->config->dbt_friends,
                array(
                    'user_id' => $userid,
                    'friend_id' => $user,
                    'pending' => 1
                )
            );
        }
        
        /**
        * Accepts a friend request by a given friendship id
        * 
        * @param int $id ID of the friendship request
        * @throws NotLoggedInException if the user is not logged in
        * @throws AlreadyFriendsException If the users are already friends
        * @return int ID of the new friendship
        */
        public function acceptRequest($id){
            $ids = $this->JACKED->MySQL->getRow(
                $this->config->dbt_friends,
                'id = ' . $id
            );
            if($this->checkFriendship($ids['friend_id'])){
                throw new AlreadyFriendsException();
            }

            return (
                $this->MySQL->insertValues(
                    $this->config->dbt_friends,
                    array(
                        'user_id' => $ids['friend_id'],
                        'friend_id' => $ids['user_id']
                    )
                ) &&
                $this->MySQL->update(
                    $this->config->dbt_friends,
                    array('pending' => 0),
                    'id = ' . $id
                )
            );
        }
        
        /**
        * Rejects a friend request by the given friendship ID
        * 
        * @param int $id ID of the friendship request
        * @throws NotLoggedInException if the user is not logged in
        * @throws AlreadyFriendsException If the users are already friends
        * @return Boolean If the rejection was successful
        */
        public function rejectRequest($id){
            $user = $this->MySQL->getVal(
                'friend_id',
                $this->config->dbt_friends,
                'id = ' . $id
            );
            if($this->checkFriendship($user)){
                throw new AlreadyFriendsException();
            }

            return $this->MySQL->delete(
                $this->config->dbt_friends,
                'id = ' . $id
            );
        }
        
        /**
        * Deletes (unsends) a friend request
        * 
        * @param $id int The id of the request to delete
        * @throws NotLoggedInException if the user is not logged in
        * @return Boolean whether the delete was successful
        */
        public function deleteRequest($id){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return $this->JACKED->MySQL->delete(
                $this->config->dbt_friends,
                'pending = 1 AND id = ' . $id
            );
        }
        
        
        ///////////////////////////////////
        //                POSTING!              //
               ///////////////////////////////////
        
        /**
        * Gets the details of a single post
        * 
        * @param $id int ID of the post
        * @return Array Details of the post
        */
        public function getPost($id){
            $post = $this->JACKED->MySQL->getRow(
                $this->config->dbt_posts,
                'id = ' . $id
            );
            $post['comments'] = $this->getComments($id);
            return $post;
        }
        
        /**
        * Gets the comments for a post
        * 
        * @param $id int ID of the post
        * @return Array Comments for the post
        */
        public function getComments($post){
            $result = $this->JACKED->MySQL->query(
                'SELECT user_id, comment, datetime_posted
                FROM ' . $this->config->dbt_comments .
                ' WHERE user_post_id = ' . $post
            );
            return $this->JACKED->MySQL->parseResult($result);
        }
        
        /**
        * Gets a given user's profile feed
        * 
        * @param $user int ID of the user
        * @return Array All of the given user's own posts
        */
        public function getProfileFeed($user){
            $result = $this->JACKED->MySQL->query(
                'SELECT user_id, poster_id, comment, datetime_posted
                FROM ' . $this->config->dbt_posts .
                ' WHERE user_id = ' . $user
            );
            return $this->JACKED->MySQL->parseResult($result);
        }
        
        /**
        * Gets the user's dashboard feed
        * 
        * @throws NotLoggedInException if the user is not logged in
        * @return Array All of the posts and comments for the user's dashboard feed
        */
        /*public function getDashboardFeed(){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            $result = $this->JACKED->MySQL->query(
                'SELECT P.user_id, P.poster_id, P.comment, P.datetime_posted
                FROM ' . $this->config->dbt_posts . ' P,
                ' . $this->config->dbt_friends . ' F,
                WHERE 
                    P.user_id = ' . $user . ' OR
                
                '
            );
            return $this->JACKED->MySQL->parseResult($result);
        }*/
        
        /**
        * Posts to the feed of a given user, defaults to the current user
        * 
        * @param $text String Text of the post 
        * @param $user int [optional] User's feed to post to
        * @throws NotFriendsException if the user is not friends with the given user
        * @throws Exception If a required used id to post to is missing
        * @return int The ID of the new post
        */
        public function post($text, $user = false){
            if($this->JACKED->Flock->checkLogin()){
                $this->requireFriendship($user);
                $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
                if(!$user)
                    $user = $userid;
            }else{
                if(!$user)
                    throw new Exception("Post via API requires a user id to post to.");
            }
            
            $this->JACKED->MySQL->insertValues(
                $this->config->dbt_posts,
                array(
                    'user_id' => $user,
                    'poster_id' => $userid,
                    'comment' => $text,
                    'datetime_posted' => time()
                )
            );
        }
        
        /**
        * Posts to the feed of a given user, defaults to the current user
        * 
        * @param $text String Text of the comment 
        * @param $post int ID of the post to comment on
        * @throws NotFriendsException if the user is not friends with the given user
        * @throws NotLoggedInException if the user is not logged in
        * @return int The ID of the new post
        */
        public function comment($text, $post){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $this->JACKED->MySQL->insertValues(
                $this->config->dbt_comments,
                array(
                    'user_id' => $user,
                    'poster_id' => $userid,
                    'comment' => $text,
                    'datetime_posted' => time()
                )
            );
        }
        
        /**
        * Deletes a given post made by the user. Also deletes any comments made on the post.
        * 
        * @param $id int ID of the post to delete
        * @throws NotLoggedInException if the user is not logged in
        * @throws Exception If the post id does not exist
        * @throws NotOwnedByUserException if the user is not the owner of the post
        * @return Boolean Whether the delete was successful
        */
        public function deletePost($id){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $post = $this->getPost($id);
            if(!$post)
                throw new Exception('Post does not exist.');
            if($post['user_id'] != $userid)
                throw new NotOwnedByUserException();
            
            return (
                $this->JACKED->MySQL->delete(
                    $this->config->dbt_posts,
                    'id = ' . $id
                ) &&
                $this->JACKED->MySQL->delete(
                    $this->config->dbt_comments,
                    'user_post_id = ' . $id
                )
            );
        }
        
        /**
        * Deletes a given comment made by the user.
        * 
        * @param $id int ID of the comment to delete
        * @throws NotLoggedInException if the user is not logged in
        * @throws Exception If the comment id does not exist
        * @throws NotOwnedByUserException if the user is not the owner of the comment
        * @return Boolean Whether the delete was successful
        */
        public function deleteComment($id){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $post = $this->getComment($id);
            if(!$post)
                throw new Exception('Comment does not exist.');
            if($post['user_id'] != $userid)
                throw new NotOwnedByUserException();
            
            return $this->JACKED->MySQL->delete(
                $this->config->dbt_comments,
                'id = ' . $id
            );
        }
    }
    
    class AlreadyFriendsException extends Exception{
        protected $message = 'Users are already friends.';
    }
    class NotFriendsException extends Exception{
        protected $message = 'Users are not friends.';
    }
    class NotOwnedByUserException extends Exception{
        protected $message = 'This object is not owned by the user.';
    }
?>