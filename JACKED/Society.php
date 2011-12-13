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
            if($userid == $user){
                $result = true;
            }else{
                $result = $this->JACKED->MySQL->query(
                    "SELECT id FROM 
                    " . $this->config->dbt_friends . " f1 
                    WHERE
                        user_id = " . $userid . " AND
                        friend_id = " . $user . " AND
                        pending != 1"
                );
            }
            return (bool)$result;
        }

        /**
        * Gets a list of all confirmed friendships
        * 
        * @return Array List of all friendships
        */
        private function getAllFriendships(){
            $result = $this->JACKED->MySQL->query(
                "SELECT id FROM 
                " . $this->config->dbt_friends . " f1 
                INNER JOIN 
                " . $this->config->dbt_friends . " f2 
                ON 
                    f1.user_id = f2.friend_id AND
                    f1.friend_id = f2.user_id"
            );
            return $this->JACKED->MySQL->parseResult($result);
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
        * @return Array of user IDs and request IDs pending requests to the user
        */
        public function getFriendRequests(){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return $this->JACKED->MySQL->getAllVals(
                array('id', 'user_id'),
                $this->config->dbt_friends,
                'pending = 1 AND friend_id = ' . $userid
            );
        }
        
        /**
        * Gets a list of the ids of the user's friends
        * 
        * @param int $user [optional] ID of the user to get. Defaults to logged in user.
        * @throws NotLoggedInException if no user id is given and the user is not logged in
        * @return Array List of friend ids
        */
        public function getFriends($user = false){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }
            
            $result = $this->JACKED->MySQL->query(
                'SELECT friend_id FROM ' .
                $this->config->dbt_friends .
                ' WHERE pending = 0 AND user_id = ' . $user
            );
            $friends = $this->JACKED->MySQL->parseResult($result);
            if(count($friends) > 0 && !is_array($friends[0]))
                $friends = array(0 => $friends);
            $done = array();
            foreach($friends as $val){
                $done[] = $val['friend_id'];
            }
            return $done;
        }
        
        /**
        * Gets a list of the ids of the user's pending friends
        * 
        * @param int $user [optional] ID of the user to get. Defaults to logged in user.
        * @throws NotLoggedInException if no user id is given and the user is not logged in
        * @return Array List of friend ids
        */
        public function getPendingFriends($user = false){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }
            
            $result = $this->JACKED->MySQL->query(
                'SELECT friend_id FROM ' .
                $this->config->dbt_friends .
                ' WHERE pending = 1 AND user_id = ' . $user
            );
            $friends = $this->JACKED->MySQL->parseResult($result);
            if(count($friends) > 0 && !is_array($friends[0]))
                $friends = array(0 => $friends);
            $done = array();
            foreach($friends as $val){
                $done[] = $val['friend_id'];
            }
            return $done;
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
            $email = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.username'));
            $userData = $this->JACKED->Flock->getUser($email);
            $friendData = $this->JACKED->Flock->getUserByID($user);
            $userid = $userData['id'];
            
            if($this->config->send_email_notifications){
                try{
                    $data = array('user_id' => $userid, 'user_name' => $userData['given_name'] . ' ' . $userData['family_name']);
                    $subject = $this->config->request_email_template_subject;
                    $body = $this->config->request_email_template_body;
                    foreach($data as $key => $value){
                        $subject = str_replace('{'.$key.'}', $value, $subject);
                        $body = str_replace('{'.$key.'}', $value, $body);
                    }
                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    mail($friendData['email'], 
                         $subject, 
                         $body,
                         $headers);
                }catch(Exception $e){echo "something went wrong";}
            }

            return $this->JACKED->MySQL->insertValues(
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
            if($this->checkFriendship($ids['user_id'])){
                throw new AlreadyFriendsException();
            }

            return (
                $this->JACKED->MySQL->insertValues(
                    $this->config->dbt_friends,
                    array(
                        'user_id' => $ids['friend_id'],
                        'friend_id' => $ids['user_id']
                    )
                ) &&
                $this->JACKED->MySQL->update(
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
            $user = $this->JACKED->MySQL->getVal(
                'user_id',
                $this->config->dbt_friends,
                'id = ' . $id
            );
            if($this->checkFriendship($user)){
                throw new AlreadyFriendsException();
            }

            return $this->JACKED->MySQL->delete(
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
            $comments = $this->JACKED->MySQL->parseResult($result);
            if(count($comments) > 0 && !is_array($comments[0]))
                $comments = array(0 => $comments);
            return $comments;
        }
        
        /**
        * Gets a given user's profile feed
        * 
        * @param $user int [optional] ID of the user, defaults to the logged in user
        * @param $count int [optional] Number of posts to get, defaults to all
        * @param $page int [optional] Page of posts to get (only useful if $count is specified). Defaults to 1.
        * @return Array All of the given user's own posts
        */
        public function getProfileFeed($user = false, $count = false, $page = 1){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }
            
            $pagination = $count? $this->JACKED->MySQL->paginator($count, $page ?: 1) : '';

            $query = 'SELECT id, user_id, poster_id, comment, datetime_posted
                FROM ' . $this->config->dbt_posts .
                ' WHERE user_id = ' . $user . '
                ORDER BY datetime_posted DESC
                ' . $pagination;

            $result = $this->JACKED->MySQL->query($query);
            $feed = $this->JACKED->MySQL->parseResult($result);
            if(count($feed) > 0 && !is_array($feed[0]))
                $feed = array(0 => $feed);
            foreach($feed as $key => $item){
                $feed[$key]['comments'] = $this->getComments($item['id']);
            }

            return $feed;
        }
        
        /**
        * Gets the user's dashboard feed
        * 
        * @throws NotLoggedInException if the user is not logged in
        * @param $count int [optional] Number of posts to get, defaults to all
        * @param $page int [optional] Page of posts to get (only useful if $count is specified). Defaults to 1.
        * @return Array All of the posts and comments for the user's dashboard feed
        */
        public function getDashboardFeed($count = false, $page = 1){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $pagination = $count? $this->JACKED->MySQL->paginator($count, $page ?: 1) : '';
            
            $result = $this->JACKED->MySQL->query(
                'SELECT DISTINCT P.id, P.user_id, P.poster_id, P.comment, P.datetime_posted
                FROM ' . $this->config->dbt_posts . ' P,
                ' . $this->config->dbt_friends . ' F
                WHERE 
                    P.user_id = ' . $userid . ' OR (
                        F.pending = 0 AND
                        F.user_id = ' . $userid . ' AND
                        P.user_id = F.friend_id
                    )
                ORDER BY datetime_posted DESC
                ' . $pagination
            );
            $feed = $this->JACKED->MySQL->parseResult($result);
            if(count($feed) > 0 && !is_array($feed[0]))
                $feed = array(0 => $feed);
            foreach($feed as $key => $item){
                $feed[$key]['comments'] = $this->getComments($item['id']);
            }

            return $feed;
        }
        
        /**
        * Posts to the feed of a given user, defaults to the current user
        * 
        * @param $text String Text of the post 
        * @param $user int [optional] User's feed to post to, defaults to the logged in user's
        * @param $asSelf Boolean [optional] If true, post as the logged in user, otherwise (default)post as API
        * @throws NotFriendsException if the user is not friends with the given user
        * @throws Exception If a required used id to post to is missing
        * @return int The ID of the new post
        */
        public function post($text, $asSelf = false, $user = false){
            if(!$asSelf){
                //post as API
                if(!$user){
                    //post to user's own feed
                    try{
                        $this->JACKED->Flock->requireLogin();
                        $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
                    }catch(NotLoggedInException $e){
                        throw new Exception("Post via API requires a user id to post to or user to be logged in.");
                    }
                }
                $userid = NULL;
            }else{
                //post as logged in user
                $this->JACKED->Flock->requireLogin();
                $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
                if(!$user){
                    //posting to my own wall
                    $user = $userid;
                }else{
                    //make sure we're friends or ourselves
                    if($user != $userid)
                        $this->requireFriendship($user);
                }
            }
            $values = array(
                'user_id' => $user,
                'comment' => $text,
                'datetime_posted' => time()
            );
            if($userid)
                $values['poster_id'] = $userid;
            return $this->JACKED->MySQL->insertValues(
                $this->config->dbt_posts,
                $values
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

            $postData = $this->getPost($post);
            if($postData['user_id'] != $userid)
                $this->requireFriendship($postData['user_id']);

            return $this->JACKED->MySQL->insertValues(
                $this->config->dbt_comments,
                array(
                    'user_post_id' => $post,
                    'user_id' => $userid,
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