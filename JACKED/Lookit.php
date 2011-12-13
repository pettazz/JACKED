<?php

    class Lookit extends JACKEDModule{
        const moduleName = 'Lookit';
        const moduleVersion = 1.0;
        const dependencies = 'MySQL, Flock, Sessions';
        const optionalDependencies = '';
        
        ///////////////
        // Accessing Achievements  //
                        /////////////
              
        /**
        * Get a an achievement's data by ID
        * 
        * @param $id int The ID of the achievement to get 
        * @throws NotLoggedInException If no user is given and the user is not logged in
        * @return Array List of all the user's achievements
        */
        public function getAchievementByID($id){
            return $this->JACKED->MySQL->getRow(
                $this->config->dbt_unlocks,
                'id = ' . $id
            );
        } 

        /**
        * Gets a list of all the available achievements
        * 
        * @return Array List of all the available achievements
        */
        public function getAchievements(){           
            return $this->JACKED->MySQL->getRows(
                $this->config->dbt_unlocks,
                '1'
            );
        } 

        /**
        * Get a list of all the user's achievements
        * 
        * @param $user int [optional] The ID of the user whose achievements to get
        * @throws NotLoggedInException If no user is given and the user is not logged in
        * @return Array List of all the user's achievements
        */
        public function getUserAchievements($user = false){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }

            $unlocks = $this->JACKED->MySQL->getRows(
                $this->config->dbt_user_unlocks,
                'user_id = ' . $user . '
                ORDER BY datetime_achieved DESC'
            );
            if($unlocks && count($unlocks) > 0){
                foreach($unlocks as $key => $unlock){
                    $unlocks[$key]['achievement_details'] = $this->getAchievementByID($unlock['achievement_id']);
                }
            }
            return $unlocks;
        } 

        /**
        * Check if the user has an achievement
        * 
        * @param $achievement The ID of the achievement to check if the user has
        * @param $user int [optional] The ID of the user whose achievements to get
        * @throws NotLoggedInException If no user is given and the user is not logged in
        * @return Array List of all the user's achievements
        */
        public function checkAchievement($achievement, $user = false){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }

            return (bool) $this->JACKED->MySQL->getRow(
                $this->config->dbt_user_unlocks,
                'user_id = ' . $user . ' AND
                achievement_id = ' . $achievement
            );
        }

        /**
        * Award the user an achievement
        * 
        * @param $achievement int The ID of the achievement to award the user
        * @param $user int [optional] The ID of the user whose achievements to get
        * @param int $time [optional] The actual unix timestamp the achievement was unlocked. Defaults to now.
        * @throws AchievementAlreadyAwardedException If the user already has the exception
        * @throws NotLoggedInException If no user is given and the user is not logged in
        * @return Boolean Whether the schievement was successfully awarded
        */
        public function awardAchievement($achievement, $user = false, $time = false){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }

            if($this->checkAchievement($achievement, $user)){
                throw new AchievementAlreadyAwardedException();
            }

            return $this->JACKED->MySQL->insertValues(
                $this->config->dbt_user_unlocks,
                array(
                    'user_id' => $user,
                    'achievement_id' => $achievement,
                    'datetime_achieved' => $time ?: time()
                )
            );
        }
        
        /**
        * Pop an unawarded but waiting achievement for this user from the queue. 
        * The achievement will be deleted from the queue immediately.
        * 
        * @throws NotLoggedInException If no user is given and the user is not logged in
        * @return Array Contains the user id, achievement id, datetime achieved, and an array of the achievement info
        */
        public function queuePop(){
            $this->JACKED->Flock->requireLogin();
            $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));

            $row = $this->JACKED->MySQL->getRow(
                $this->config->dbt_unlock_queue,
                'user_id = ' . $user . '
                ORDER BY id ASC
                LIMIT 0, 1'
            );
            if($row){
                $row['achievement_details'] = $this->getAchievementByID($row['achievement_id']);
                
                $this->JACKED->MySQL->delete(
                    $this->config->dbt_unlock_queue,
                    'id = ' . $row['id']
                );
            }
            
            return $row;
        }
        
        /**
        * Pop an unawarded but waiting achievement for this user from the queue, and award it immediately.
        * 
        * @throws NotLoggedInException If no user is given and the user is not logged in
        * @return Array Contains the user id, achievement id, datetime achieved, and an array of the achievement info
        */
        public function queuePopAndAward(){
            $this->JACKED->Flock->requireLogin();
            $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $unlock = $this->queuePop();
            if($unlock){
                $achievement = $unlock['achievement_id'];
                try{
                    $this->awardAchievement($unlock['achievement_id'], $user, $unlock['datetime_achieved']);
                }catch(AchievementAlreadyAwardedException $e){
                    return $this->queuePopAndAward();
                }
            }
            return $unlock;
        }
        
        /**
        * Push an unawarded but waiting achievement for this user into the queue. 
        * The achievement will be deleted from the queue immediately.
        * 
        * @param int $achievement The ID of the achievement to award
        * @param int $time [optional] The actual unix timestamp the achievement was unlocked. Defaults to now.
        * @throws NotLoggedInException If no user is given and the user is not logged in
        * @return Boolean Whether the push was completed successfully
        */
        public function queuePush($achievement, $time = false){
            $this->JACKED->Flock->requireLogin();
            $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            if($this->checkAchievement($achievement, $user)){
                throw new AchievementAlreadyAwardedException();
            }
            
            return $this->JACKED->MySQL->insertValues(
                $this->config->dbt_unlock_queue,
                array(
                    'user_id' => $user,
                    'achievement_id' => $achievement,
                    'datetime_achieved' => $time ?: time()
                )
            );
        }
    }

    class AchievementAlreadyAwardedException extends Exception{
        protected $message = "The user has already been awarded this achievement.";
    }
?>