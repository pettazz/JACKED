<?php

    class Vitogo extends JACKEDModule{
        const moduleName = 'Vitogo';
        const moduleVersion = 1.0;
        const dependencies = 'MySQL, Flock, Sessions, Lookit';
        const optionalDependencies = '';
        
        ///////////////
        //         Accessors       //
                        /////////////
              
        /**
        * Gets all activity levels
        * 
        * @return Array Associative array of all activity levels
        */
        public function getActivityLevels(){
            return $this->JACKED->MySQL->getRows($this->config->dbt_activity_levels);
        }
        
        /**
        * Gets an activity level by ID
        * 
        * @param $id int Activity level ID
        * @return Array Associative array of all activity levels
        */
        public function getActivityLevelByID($id){
            return $this->JACKED->MySQL->getRow($this->config->dbt_activity_levels, 'id = ' . $this->JACKED->MySQL->sanitize($id));
        }
             
        /**
        * Gets all muscle groups
        * 
        * @return Array Associative array of all muscle groups
        */
        public function getMuscleGroups(){
            return $this->JACKED->MySQL->getRows($this->config->dbt_muscle_groups);
        }
             
        /**
        * Gets a muscle group by a given id
        * 
        * @param $id int Muscle group ID
        * @return Array Associative array of the muscle group data
        */
        public function getMuscleGroupByID($id){
            return $this->JACKED->MySQL->getRow($this->config->dbt_muscle_groups, 'id = ' . $this->JACKED->MySQL->sanitize($id));
        }
            
        /**
        * Gets all exercises
        * 
        * @return Array Associative array of all exercises
        */
        public function getExercises(){
            return $this->JACKED->MySQL->getRows($this->config->dbt_exercises);
        }
            
        /**
        * Gets an exercise by ID
        * 
        * @param $id int The Exercise ID 
        * @return Array Associative array of the exercise data
        */
        public function getExerciseByID($id){
            return $this->JACKED->MySQL->getRow($this->config->dbt_exercises, 'id = ' . $this->JACKED->MySQL->sanitize($id));
        }
            
        /**
        * Gets all workout goals
        * 
        * @return Array Associative array of all workout goals
        */
        public function getWorkoutGoals(){
            return $this->JACKED->MySQL->getRows($this->config->dbt_workout_goals);
        }
            
        /**
        * Gets a workout goal by ID
        * 
        * @param $id int Workout goal ID
        * @return Array Associative array of workout goal data
        */
        public function getWorkoutGoalByID($id){
            return $this->JACKED->MySQL->getRow($this->config->dbt_workout_goals, 'id = ' . $this->JACKED->MySQL->sanitize($id));
        }
            
        /**
        * Gets all training programs
        * 
        * @return Array Associative array of all training programs
        */
        public function getTrainingPrograms(){
            return $this->JACKED->MySQL->getRows($this->config->dbt_training_programs);
        }
            
        /**
        * Gets a training program by ID
        * 
        * @param $id int Training program ID
        * @return Array Associative array of training program data
        */
        public function getTrainingProgramByID($id){
            return $this->JACKED->MySQL->getRow($this->config->dbt_training_programs, 'id = ' . $this->JACKED->MySQL->sanitize($id));
        }
                    
        /**
        * Gets a training program ID based on a workout goal and activity level
        * 
        * @param $workoutGoal int Training workout goal ID
        * @param $activityLevel int Training activity level ID
        * @return int Selected training program ID 
        */
        public function selectTrainingProgram($workoutGoal, $activityLevel){
            return $this->JACKED->MySQL->getVal(
                'id',
                $this->config->dbt_training_programs, 
                'workout_goal_id = ' . $this->JACKED->MySQL->sanitize($workoutGoal) . ' AND ' .
                'activity_level_id = ' . $this->JACKED->MySQL->sanitize($activityLevel)
            );
        }

        
        ///////////////
        //         Exercises       //
                        /////////////
            
        /**
        * Gets all exercises by muscle group id
        * 
        * @param $groupID int Muscle group id
        * @return Array Associative array of all matching exercises
        */
        public function getExercisesByMuscleGroup($groupID){
            return $this->JACKED->MySQL->getRows($this->config->dbt_exercises, 'muscle_group_id = "' . $groupID . '"');
        }
         
        /**
        * Gets the muscle group to which an exercise belongs
        * 
        * @param $id int Exercise ID
        * @return String Name of the muscle group 
        */
        public function getMuscleGroupByExercise($id){
            $result = $this->JACKED->MySQL->query(
                'SELECT M.name
                FROM 
                   ' . $this->config->dbt_muscle_groups . ' M, 
                   ' . $this->config->dbt_exercises . ' E
                WHERE
                    E.id = ' . $id . ' AND
                    M.id = E.muscle_group_id
            ');
            
            $done = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            return $done['name'];
        }
         
        /**
        * Gets all exercises by muscle group name
        * 
        * @param $groupName int Muscle group name
        * @return Array Associative array of all exactly matching exercises
        */
        public function getExercisesByMuscleGroupName($groupName){
            $result = $this->JACKED->MySQL->query(
                'SELECT
                    E.*
                FROM    
                    ' . $this->config->dbt_exercises . ' E, 
                    ' . $this->config->dbt_muscle_groups . ' M
                WHERE
                    M.name = "' . $this->JACKED->MySQL->sanitize($groupName) . '" AND
                    E.muscle_group_id = M.id'
            );
            return array($this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC));
        }
         
        /**
        * Gets all user exercises for an exercise id
        * 
        * @param $exerciseID int Exercise id
        * @param $trainingDay int [optional] ID of the training day to to constrain results to.
        * @param $startTime int [optional] Unix timestamp of the oldest to get. Defaults to all.
        * @param $endTime int [optional] Unix timestamp of the newest to get. Defaults to now.
        * @return Array Associative array of all matching user exercises
        */
        public function getUserExercisesByExerciseID($exerciseID, $trainingDay = false, $startTime = false, $endTime = false){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));

            if(!$startTime){
                $startTime = 0;
            }
            
            if(!$endTime){
                $endTime = time();
            }

            if($trainingDay){
                $dayClause = 'training_day_id = ' . $trainingDay . ' AND';
            }else{
                $dayClause = '';
            }

            $done = $this->JACKED->MySQL->getRows(
                $this->config->dbt_user_exercises, 
                'exercise_id = "' . $exerciseID . '" AND
                user_id = "' . $userid . '" AND ' .
                $dayClause .'
                datetime_started >= ' . $startTime . ' AND 
                datetime_started <= ' . $endTime
            );
            return $done;
        }
         
        /**
        * Gets all user exercise sets for a user exercise id
        * 
        * @param $exerciseID int User Exercise id
        * @return Array Associative array of all matching user exercise sets
        */
        public function getUserExerciseSetsByUserExerciseID($exerciseID){
            $this->JACKED->Flock->requireLogin();
            
            $result = $this->JACKED->MySQL->getRows(
                $this->config->dbt_user_exercise_sets, 
                'user_exercise_id = ' . $exerciseID
            );
            
            if($result && !(is_array($result[0]))){
                $result = array(0 => $result);
            }
            return $result;
        }
         
        /**
        * Gets all completed user exercises for a given muscle group id
        * 
        * @param $id int Muscle group id
        * @return Array Associative array of all matching user exercises
        */
        public function getUserExercisesByMuscleGroupID($id){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $result = $this->JACKED->MySQL->query('
                SELECT U.*
                FROM
                    ' . $this->config->dbt_user_exercises . ' U, 
                    ' . $this->config->dbt_exercises . ' E
                WHERE
                    E.muscle_group_id = ' . $id . ' AND
                    U.exercise_id = E.id AND
                    U.datetime_completed IS NOT NULL AND
                    U.datetime_completed != 0 AND
                    U.user_id = ' . $userid . '
            ');
            
            $result = $this->JACKED->MySQL->parseResult($result, MYSQL_BOTH);
            
            if($result && !(is_array($result[0]))){
                $result = array(0 => $result);
            }
            return $result;
        }
        
        /**
        * Gets all of todays exercises for the user. Does not distinguish between completed/started/new.
        * 
        * @throws NotLoggedInException If the user is not logged in
        * @return Array Associative array of all matching exercises
        */
        public function getTodaysExercises(){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();

            $program = $this->getUserTrainingProgram();
            $trainingDayid = $this->JACKED->MySQL->getVal(
                'id',
                $this->config->dbt_training_days, 
                'training_program_id = "' . $program['id'] . '"
                LIMIT ' . $userData['last_training_day_order'] . ', 1'
            );

            return $this->getExercisesByTrainingDay($trainingDayid);
        }
        
        /**
        * Gets all completed exercises for this user
        * 
        * @param sort bool Determines whether or not the list should be sorted by starting time
        * @throws NotLoggedInException If the user is not logged in
        * @return Array Associative array of all matching exercises
        */
        public function getAllCompletedExercises($sort=false){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $result = $this->JACKED->MySQL->query('
                SELECT
                      E.id,
                      E.name, 
                      E.secondary_name, 
                      E.description, 
                      E.photos, 
                      E.videos,
                      U.datetime_started,
                      U.datetime_completed,
                      U.id AS user_exercise_id,
                      M.name as muscle_group_name
                FROM
                      ' .$this->config->dbt_user_exercises . ' U,
                      ' .$this->config->dbt_exercises . ' E,
                      ' .$this->config->dbt_muscle_groups . ' M
                WHERE
                      U.user_id = ' . $userid . ' AND
                      U.datetime_completed IS NOT NULL AND
                      E.id = U.exercise_id AND
                      M.id = E.muscle_group_id
                      ' . ($sort ? ' ORDER BY U.datetime_started ' : '') . '
            ');
            $exercises = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC); 
            if(is_string(key($exercises)))
                $exercises = array(0=>$exercises);
            if(!empty($exercises)){
                foreach($exercises as $key => $exercise){
                    $result2 = $this->JACKED->MySQL->query(
                        'SELECT * FROM ' . $this->config->dbt_user_exercise_sets . ' WHERE user_exercise_id = ' . $exercise['user_exercise_id']
                    );

                    $sets = $this->JACKED->MySQL->parseResult($result2, MYSQL_ASSOC);
                    if(is_array($exercises[$key]))
                        $exercises[$key]['sets'] = $sets;
                        
                        
                }
            }
            
            return $exercises;
        }
        
         /**
        * Gets completed exercises for this user in a given timeframe
        * 
        * @param startTime int Starting bound
        * @param endTime int Ending bound, if empty the current time is used
        * @param sort bool Determines whether or not the list should be sorted by starting time
        * @throws NotLoggedInException If the user is not logged in
        * @return Array Associative array of all matching exercises
        */
        public function getCompletedExercises($startTime, $endTime = NULL, $sort=false){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            if($endTime == NULL)
                $endTime = time();
            
            $startTime = (int) $startTime;
            $endTime = (int) $endTime;
            
            if($startTime > $endTime){
                $tmp = $startTime;
                $startTime = $endTime;
                $endTime = $tmp;
            }
            
            $result = $this->JACKED->MySQL->query('
                SELECT
                      E.id,
                      E.name, 
                      E.secondary_name, 
                      E.description, 
                      E.photos, 
                      E.videos,
                      U.datetime_started,
                      U.datetime_completed,
                      U.id AS user_exercise_id,
                      M.name as muscle_group_name
                FROM
                      ' .$this->config->dbt_user_exercises . ' U,
                      ' .$this->config->dbt_exercises . ' E,
                      ' .$this->config->dbt_muscle_groups . ' M
                WHERE
                      U.user_id = ' . $userid . ' AND
                      U.datetime_completed IS NOT NULL AND
                      U.datetime_completed != 0 AND
                      E.id = U.exercise_id AND
                      M.id = E.muscle_group_id AND
                      U.datetime_started >= '.$startTime.' AND 
                      U.datetime_completed <= '.$endTime.'
                      ' . ($sort ? ' ORDER BY U.datetime_started ' : '') . '
            ');
            
            $exercises = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC); 
            
            if(is_string(key($exercises)))
                $exercises = array(0=>$exercises);
            if(!empty($exercises)){
                foreach($exercises as $key => $exercise){
                    $result2 = $this->JACKED->MySQL->query(
                        'SELECT * FROM ' . $this->config->dbt_user_exercise_sets . ' WHERE user_exercise_id = ' . $exercise['user_exercise_id']
                    );

                    $sets = $this->JACKED->MySQL->parseResult($result2, MYSQL_ASSOC);
                    $exercises[$key]['sets'] = $sets;
                    
                }
            }
            
            return $exercises;
        }
                
        /**
        * Gets all completed exercises for this user for a given training program
        * 
        * @param $id int ID of the training program to use
        * @throws NotLoggedInException If the user is not logged in
        * @return Array Associative array of all matching exercises
        */
        public function getCompletedExercisesByTrainingProgram($id){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $result = $this->JACKED->MySQL->query('
                SELECT
                      E.id,
                      E.name, 
                      E.secondary_name, 
                      E.description, 
                      E.photos, 
                      E.videos,
                      U.id AS user_exercise_id,
                      M.name as muscle_group_name
                FROM
                      ' .$this->config->dbt_user_exercises . ' U,
                      ' .$this->config->dbt_exercises . ' E,
                      ' .$this->config->dbt_muscle_groups . ' M,
                      ' .$this->config->dbt_training_days . ' D,
                      ' .$this->config->dbt_training_exercise_sets . ' T
                WHERE
                      D.training_program_id = ' . $id . ' AND
                      T.training_day_id = D.id AND
                      U.user_id = ' . $userid . ' AND
                      U.datetime_completed IS NOT NULL AND
                      E.id = U.exercise_id AND
                      E.id = T.exercise_id AND
                      M.id = E.muscle_group_id
            ');
            $exercises = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            foreach($exercises as $key => $exercise){
                $result2 = $this->JACKED->MySQL->query(
                    'SELECT * FROM ' . $this->config->dbt_user_exercise_sets . ' WHERE user_exercise_id = ' . $exercise['user_exercise_id']
                );
                $sets = $this->JACKED->MySQL->parseResult($result2, MYSQL_ASSOC);
                $exercises[$key]['sets'] = $sets;
            }
            
            return $exercises; 
        }
        
        /**
        * Gets all exercises by training day
        * 
        * @param $dayID int ID of training day
        * @return Array Associative array of all matching exercises, including sets and muscle group
        */
        public function getExercisesByTrainingDay($dayID){
            $dayid = $this->JACKED->MySQL->sanitize($dayID);
            $result = $this->JACKED->MySQL->query(
                'SELECT 
                      E.id,
                      E.name, 
                      E.secondary_name, 
                      E.description, 
                      E.photos, 
                      E.videos,
                      S.weight,
                      S.sets,
                      S.reps,
                      S.rest,
                      M.name as muscle_group_name
                 FROM 
                     ' . $this->config->dbt_exercises . ' E,
                     ' . $this->config->dbt_training_exercise_sets . ' S,
                     ' . $this->config->dbt_muscle_groups . ' M
                 WHERE 
                     S.training_day_id = ' . $dayID . ' AND
                     E.id = S.exercise_id AND
                     M.id = E.muscle_group_id
                '
            );
            $result = $this->JACKED->MySQL->parseResult($result, MYSQL_BOTH);
            if($result && !(is_array($result[0]))){
                $result = array(0 => $result);
            }
            return $result;
        }

        /**
        * Get the Exercise ID associated with a given User Exercise Set
        *
        * @param $id int ID of the User Exercise Set
        * @return int ID of the Exercise
        */
        public function getExerciseByUserExerciseSetID($id){
            $result = $this->JACKED->MySQL->query(
                'SELECT 
                      E.id,
                      E.name, 
                      E.secondary_name, 
                      E.description, 
                      E.photos, 
                      E.videos,
                      M.name as muscle_group_name
                 FROM 
                     ' . $this->config->dbt_exercises . ' E,
                     ' . $this->config->dbt_user_exercise_sets . ' S,
                     ' . $this->config->dbt_user_exercises . ' X,
                     ' . $this->config->dbt_muscle_groups . ' M
                 WHERE 
                     S.id = ' . $id . ' AND
                     X.id = S.user_exercise_id AND
                     E.id = X.exercise_id AND
                     M.id = E.muscle_group_id
                '
            );
            return $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
        }
        
        /**
        * Get the list of this week's workout days and exercises
        *
        * @throws NotLoggedInException If the user is not logged in
        * @return Array Associative array of arrays containing details of day and their exercises
        */
        public function getWeekDetails(){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();
            $userid = $userData['id'];
            $this->incrementTrainingDay();
            $program = $this->getUserTrainingProgram();
            $days = $this->JACKED->MySQL->getRows(
                $this->config->dbt_training_days, 
                'training_program_id = "' . $program['id'] . '"'
            );
            
            if(count($days) > 0){
                foreach($days as $id => $training_day){
                    $exercises = $this->getExercisesByTrainingDay($training_day['id']);
                    $days[$id]['exercises'] = $exercises;
                    $exCount = count($exercises);
                    if($exCount > 0){
                        $userExs = null;
                        $userSets = array();
                        $exCompleted = 0;
                        foreach($days[$id]['exercises'] as $key => $anExercise){
            			    $counted = false; // over counting, because of nested for loop
                            $userExs = $this->getUserExercisesByExerciseID($anExercise['id'], $training_day['id'], $userData['datetime_week_started']);
                            if(is_array($userExs) && count($userExs) > 0){
                                foreach($userExs as $userEx){
                                    $sets = $this->getUserExerciseSetsByUserExerciseID($userEx['id']);
                                    if($sets){
                                        foreach($sets as $set){
                                            $userSets = array_merge($userSets, $set);
                                        }
                                        if($userEx['datetime_completed'] != 0) {
                    					    if (!$counted) {
                                            	$exCompleted++;
                       					    }
                    					    $counted = true; 
                    					}	
                                        $days[$id]['exercises'][$key]['user_sets'] = $userSets;
                                    }elseif($userEx['exercise_id'] == 7){
                                        //cardio has no sets
                                        if($userEx['datetime_completed'] != 0) {
                    					    if (!$counted) {
                                            	$exCompleted++;
                       					    }
                    					    $counted = true; 
                    					}
                                    }
                                }
                            }
                            $days[$id]['user_sets'] = $userSets;
                        }
                        $days[$id]['completed'] = ($exCompleted == $exCount);
                    }else{
                        //this is a rest day. if it's order is < our current order then we're past it.
                        if($days[$id]['order'] <= $userData['last_training_day_order'])
                            $days[$id]['completed'] = true;
                    }
                }
                if(count($days) == 1){
                    //slightly hacky workaround for Yapp's non single-wrapped array data policy
                    return array($days);
                }else{
                    return $days;
                }
            }else{
              throw new Exception("No exercises found.");
            }
        }
        
        /**
        * Gets the user's current training day id
        * 
        * @throws NotLoggedInException if the user is not logged in
        * @return int ID of the user's current training day
        */
        public function getUserTrainingDayID(){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();
            
            $program = $this->getUserTrainingProgram();
            return $this->JACKED->MySQL->getVal(
                'id',
                $this->config->dbt_training_days, 
                'training_program_id = "' . $program['id'] . '"
                LIMIT ' . $userData['last_training_day_order'] . ', 1'
            );
        }
            
        /**
        * Checks if all the exercises for today have been completed
        *
        * @throws NotLoggedInException if the user is not logged in
        * @return Boolean Whether all of today's exercises have been completed
        */
        public function checkTodayCompleted(){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();
            $userid = $userData['id'];
            
            $program = $this->getUserTrainingProgram();
            $trainingDayID = $this->JACKED->MySQL->getVal(
                'id',
                $this->config->dbt_training_days, 
                'training_program_id = "' . $program['id'] . '"
                LIMIT ' . $userData['last_training_day_order'] . ', 1'
            );
            
            $exercises = $this->getExercisesByTrainingDay($trainingDayID);
            $exCompleted = 0;
            $exCount = count($exercises);
            if($exCount > 0){
                $userExs = null;
                foreach($exercises as $anExercise){
    			    $counted = false; // over counting, because of nested for loop
                    $userExs = $this->getUserExercisesByExerciseID($anExercise['id'], $trainingDayID, $userData['datetime_week_started']);
                    if(is_array($userExs) && count($userExs) > 0){
                        foreach($userExs as $userEx){
                            if(!is_null($userEx['datetime_completed'])) {
        					    if (!$counted) {
                                	$exCompleted++;
           					    }
        					    $counted = true; 
        					}	
                        }
                    }
                }
            }
            return ($exCount == $exCompleted);
        }
            
        /**
        /* Checks if all the exercises for today have been completed, if so 
        /* makes updates to the tracking data in the users table: last_training_day_order.
        /* Also adds +1 to the days_completed column in the user's data
        /* Potentially dangerous mutating method, so it's private from the API.
        /*
        /* @throws NotLoggedInException if the user is not logged in
        /* @return void
        */
        private function incrementTrainingDay(){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();
            $userid = $userData['id'];
            $last_day = $userData['last_training_day_order'];

            if($this->checkTodayCompleted()){
                
                //check for trophies
                try{
                    switch($userData['days_completed']){
                        case 0:
                            $this->JACKED->Lookit->queuePush(1);
                            break;
                        case 4:
                            $this->JACKED->Lookit->queuePush(2);
                            break;
                        case 9:
                            $this->JACKED->Lookit->queuePush(3);
                            break;
                        case 19:
                            $this->JACKED->Lookit->queuePush(4);
                            break;
                        case 34:
                            $this->JACKED->Lookit->queuePush(5);
                            break;
                        case 49:
                            $this->JACKED->Lookit->queuePush(6);
                            break;
                        case 74:
                            $this->JACKED->Lookit->queuePush(7);
                            break;
                        case 99:
                            $this->JACKED->Lookit->queuePush(8);
                            break;
                        case 199:
                            $this->JACKED->Lookit->queuePush(9);
                            break;
                        case 149:
                            $this->JACKED->Lookit->queuePush(10);
                            break;
                        case 249:
                            $this->JACKED->Lookit->queuePush(11);
                            break;
                    }
                }catch(AchievementAlreadyAwardedException $e){}
                
                try{
                    if(date('m') == '1')
                        $this->JACKED->Lookit->queuePush(29);
                    if(date('m') == '6')
                        $this->JACKED->Lookit->queuePush(30);
                }catch(AchievementAlreadyAwardedException $e){}
            
                if(time() - $userData['datetime_last_day_completed'] <= 86400){
                    $csec = 'literal:consecutive_days_completed + 1';
                }else{
                    $csec = 1;
                }
            
                if($last_day == 6){
                    //make sure it's been at least seven days since we started
                    if((time() - $userData['datetime_week_started']) >= 604800){
                        $this->JACKED->MySQL->update(
                            $this->config->dbt_users,
                            array(
                                'days_completed' => 'literal:days_completed + 1',
                                'consecutive_days_completed' => $csec,
                                'datetime_last_day_completed' => time(),
                                'last_training_day_order' => 0,
                                'datetime_week_started' => time()
                            ),
                            'id = ' . $userid
                        );
                    }else{
                        return NULL;
                    }
                }else{
                    $this->JACKED->MySQL->update(
                        $this->config->dbt_users, 
                            array(
                            'days_completed' => 'literal:days_completed + 1',
                            'consecutive_days_completed' => $csec,
                            'datetime_last_day_completed' => time(),
                            'last_training_day_order' => 'literal:last_training_day_order + 1'
                        ), 
                        'id = ' . $userid
                    );
                }
                
                $newUserData = $this->getUser();
                if($newUserData['consecutive_days_completed'] == 5){
                    try{
                        $this->JACKED->Lookit->queuePush(28);
                    }catch(AchievementAlreadyAwardedException $e){}
                }
                if($newUserData['consecutive_days_completed'] == 30){
                    try{
                        $this->JACKED->Lookit->queuePush(32);
                    }catch(AchievementAlreadyAwardedException $e){}
                }
                if($newUserData['consecutive_days_completed'] == 180){
                    try{
                        if($newUserData['gender'] == 'M'){
                            $this->JACKED->Lookit->queuePush(33);
                        }else{
                            $this->JACKED->Lookit->queuePush(34);
                        }
                    }catch(AchievementAlreadyAwardedException $e){}
                }
                //so we automatically skip rest days
                $this->incrementTrainingDay();
            }
        }
                    
        /**
        * Get all the last completed sets for the last completed exercise with the given exercise ID
        *
        * @param $exerciseid int The ID of the exercise
        * @throws NotLoggedInException If the user is not logged in
        * @return Array All of the sets completed in the last exercise
        */  
        public function getLastSetsCompletedByExercise($exerciseid){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $result = $this->JACKED->MySQL->query(
                'SELECT 
                      S.*
                 FROM 
                     ' . $this->config->dbt_user_exercise_sets . ' S,
                     ' . $this->config->dbt_user_exercises . ' X
                 WHERE 
                     X.datetime_completed IS NOT NULL AND
                     X.datetime_completed = (
                         SELECT MAX(datetime_completed) 
                         FROM ' . $this->config->dbt_user_exercises . ' 
                         WHERE user_id = ' . $userid . ' AND
                               exercise_id = ' . $exerciseid .' 
                     ) AND
                     X.user_id = ' . $userid . ' AND
                     S.user_exercise_id = X.id
                '
            );
            return $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
        }
                    
        /**
        * Add a fully completed user exercise
        *
        * @param $exerciseid int The ID of the completed exercise
        * @param $notes String Fulltext of any notes added by the user
        * @param $started int Unix Epoch timestamp for workout start
        * @param $completed int Unix Epoch timestamp for workout completion
        * @param $trainingDayID int [optional] The training day in which the exercise was completed. Defaults to the current day.
        * @throws NotLoggedInException If the user is not logged in
        * @return int ID of the added exercise
        */  
        public function addExerciseCompleted($exerciseid, $notes, $started, $completed, $trainingDayID = false){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();
            $userid = $userData['id'];

            if(!$trainingDayID){
                $program = $this->getUserTrainingProgram();
                $trainingDayID = $this->JACKED->MySQL->getVal(
                    'id',
                    $this->config->dbt_training_days, 
                    'training_program_id = "' . $program['id'] . '"
                    LIMIT ' . $userData['last_training_day_order'] . ', 1'
                );
            }
            
            if(($userData['last_training_day_order'] == 0) && ($userData['datetime_week_started'] == 0)){
                $this->JACKED->MySQL->update(
                    $this->config->dbt_users,
                    array(
                        'datetime_week_started' => $started
                    ),
                    'id = ' . $userid
                );
            }
            
            $this->incrementTrainingDay();
            
            //check for a trophy award
            try{
                //hamstrings
                if(!$this->JACKED->Lookit->checkAchievement(16)){
                    if(count($this->getUserExercisesByMuscleGroupID(8)) >= 15){
                        $this->JACKED->Lookit->queuePush(16);
                    }
                }
            }catch(Exception $e){}
            try{
                //core
                if(!$this->JACKED->Lookit->checkAchievement(17)){
                    if(count($this->getUserExercisesByMuscleGroupID(14)) >= 25){
                        $this->JACKED->Lookit->queuePush(17);
                    }
                }
            }catch(Exception $e){}
            try{
                //glutes
                if(!$this->JACKED->Lookit->checkAchievement(18)){
                    if(count($this->getUserExercisesByMuscleGroupID(15)) >= 15){
                        $this->JACKED->Lookit->queuePush(18);
                    }
                }
            }catch(Exception $e){}
            try{
                //biceps
                if(!$this->JACKED->Lookit->checkAchievement(19)){
                    if(count($this->getUserExercisesByMuscleGroupID(5)) >= 20){
                        $this->JACKED->Lookit->queuePush(19);
                    }
                }
            }catch(Exception $e){}
            try{
                //back
                if(!$this->JACKED->Lookit->checkAchievement(20)){
                    if(count($this->getUserExercisesByMuscleGroupID(12)) >= 25){
                        $this->JACKED->Lookit->queuePush(20);
                    }
                }
            }catch(Exception $e){}
            try{
                //shoulder
                if(!$this->JACKED->Lookit->checkAchievement(21)){
                    if(count($this->getUserExercisesByMuscleGroupID(9)) >= 15){
                        $this->JACKED->Lookit->queuePush(21);
                    }
                }
            }catch(Exception $e){}
            try{
                //trapezius
                if(!$this->JACKED->Lookit->checkAchievement(22)){
                    if(count($this->getUserExercisesByMuscleGroupID(3)) >= 15){
                        $this->JACKED->Lookit->queuePush(2);
                    }
                }
            }catch(Exception $e){}
            try{
                //calves
                if(!$this->JACKED->Lookit->checkAchievement(23)){
                    if(count($this->getUserExercisesByMuscleGroupID(13)) >= 20){
                        $this->JACKED->Lookit->queuePush(23);
                    }
                }
            }catch(Exception $e){}
            try{
                //pectorals
                if(!$this->JACKED->Lookit->checkAchievement(24)){
                    if(count($this->getUserExercisesByMuscleGroupID(16)) >= 20){
                        $this->JACKED->Lookit->queuePush(24);
                    }
                }
            }catch(Exception $e){}
            try{
                //quadricep
                if(!$this->JACKED->Lookit->checkAchievement(25)){
                    if(count($this->getUserExercisesByMuscleGroupID(7)) >= 25){
                        $this->JACKED->Lookit->queuePush(25);
                    }
                }
            }catch(Exception $e){}
            try{
                //triceps
                if(!$this->JACKED->Lookit->checkAchievement(26)){
                    if(count($this->getUserExercisesByMuscleGroupID(4)) >= 15){
                        $this->JACKED->Lookit->queuePush(26);
                    }
                }
            }catch(Exception $e){}
            try{
                //forearm
                if(!$this->JACKED->Lookit->checkAchievement(27)){
                    if(count($this->getUserExercisesByMuscleGroupID(6)) >= 10){
                        $this->JACKED->Lookit->queuePush(27);
                    }
                }
            }catch(Exception $e){}
            
            return $this->JACKED->MySQL->insertValues($this->config->dbt_user_exercises, array(
                    "user_id" => $userid,
                    "exercise_id" => $exerciseid,
                    "notes" => $notes,
                    "training_day_id" => $trainingDayID,
                    "datetime_started" => $started,
                    "datetime_completed" => $completed
                ));
        }
            
        /**
        * Start a user exercise now
        *
        * @param $exerciseid int The ID of the exercise
        * @param $notes String [Optional] Fulltext of any notes added by the user
        * @param $trainingDayID int [optional] The training day in which the exercise was completed. Defaults to the current day.
        * @throws NotLoggedInException If the user is not logged in
        * @return int The ID of the new user exercise
        */  
        public function startExerciseNow($exerciseid, $notes = '', $trainingDayID = false){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();
            $userid = $userData['id'];

            if(!$trainingDayID){
                $program = $this->getUserTrainingProgram();
                $trainingDayID = $this->JACKED->MySQL->getVal(
                    'id',
                    $this->config->dbt_training_days, 
                    'training_program_id = "' . $program['id'] . '"
                    LIMIT ' . $userData['last_training_day_order'] . ', 1'
                );
            }
            
            if(($userData['last_training_day_order'] == 0) && ($userData['datetime_week_started'] == 0)){
                $this->JACKED->MySQL->update(
                    $this->config->dbt_users,
                    array(
                        'datetime_week_started' => time()
                    ),
                    'id = ' . $userid
                );
            }
            
            $newid = $this->JACKED->MySQL->insertValues($this->config->dbt_user_exercises, array(
                "user_id" => $userid,
                "exercise_id" => $exerciseid,
                "notes" => $notes,
                "training_day_id" => $trainingDayID,
                "datetime_started" => time()
            ));
            $this->incrementTrainingDay();

            return $newid;
        }
            
        /**
        * Cancel a user exercise
        *
        * @param $exerciseid int The ID of the exercise
        * @throws NotLoggedInException If the user is not logged in
        * @return boolean If the delete was successful
        */  
        public function cancelExercise($exerciseid){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return $this->JACKED->MySQL->delete($this->config->dbt_user_exercises, 'id = ' . $exerciseid);
        }
            
        /**
        * Update an existing user exercise to completed now
        *
        * @param $exerciseid int The ID of the exercise
        * @throws NotLoggedInException If the user is not logged in
        * @return boolean Whether the update was successfully completed
        */  
        public function completeExerciseNow($exerciseid){
            $this->JACKED->Flock->requireLogin();
            
            $done = $this->JACKED->MySQL->update($this->config->dbt_user_exercises, array(
                "datetime_completed" => time()
            ), 'id = ' . $exerciseid);
        
            $this->incrementTrainingDay();
            return $done;
            
        }
            
        /**
        * Update a user's sets after one has been completed
        *
        * @param $exerciseid int The ID of the user exercise
        * @param $completed int Unix Epoch timestamp for workout completion 
        * @param $weight int The weight used in the exercise
        * @param $set int The set used in the exercise
        * @param $reps int The reps used in the exercise
        * @param $rest int The rest used in the exercise
        * @throws NotLoggedInException If the user is not logged in
        * @return int ID of the added set
        */  
        public function addSetCompleted($exerciseid, $completed, $weight, $reps, $rest){
            $this->JACKED->Flock->requireLogin();
            $userData = $this->getUser();
            $userid = $userData['id'];

            $query = '
                SELECT `set`, `datetime_completed` FROM ' . $this->config->dbt_user_exercise_sets . ' 
                WHERE user_exercise_id = ' . $exerciseid . ' ORDER BY `set` DESC LIMIT 0, 1
            ';
            $result = $this->JACKED->MySQL->query($query);
            $parsed = $this->JACKED->MySQL->parseResult($result);
            $oldset = ($parsed && $parsed['set'])? $parsed['set'] : 0;
            $oldtime = ($parsed && $parsed['datetime_completed'])? $parsed['datetime_completed'] : $this->getUserExerciseStartTime($exerciseid);

            $program = $this->getUserTrainingProgram();
            $trainingDayid = $this->JACKED->MySQL->getVal(
                'id',
                $this->config->dbt_training_days, 
                'training_program_id = "' . $program['id'] . '"
                LIMIT ' . $userData['last_training_day_order'] . ', 1'
            );

            $time = $completed - $oldtime;
            $userWeight = $this->getMeasurement('weight');
            $userWeight = $userWeight? $userWeight : 0;
            $calories = $this->calculateSetCalories($reps, $time, $userWeight);
            $done = $this->JACKED->MySQL->insertValues($this->config->dbt_user_exercise_sets, array(
                    'user_exercise_id' => $exerciseid,
                    'weight' => $weight,
                    'set' => $oldset + 1,
                    'reps' => $reps,
                    'rest' => $rest,
                    'training_day' => $trainingDayid,
                    'calories' => $calories,
                    'datetime_completed' => $completed
            ));
            return $done;
        }
        
        /**
        * Utility method for Calculating calories burned
        *
        * @param int $reps The number of reps in the activity
        * @param int $time Number of seconds of activity
        * @param int $weight The person's weight in kg
        * @return int Number of calories burned
        */
        public function calculateSetCalories($reps, $time, $weight){
            if($reps < 8){
                $coeff = 3;
            }else{
                $coeff = 6;
            }
            return (($coeff * 3.5 * $weight) / 200) * ($time / 60);
        }

        /**
        * Gets the start timestamp of the given user exercise 
        *
        * @param int $id The user exercise id
        * @return int Timestamp the user exercise started
        */
        public function getUserExerciseStartTime($id){
            return $this->JACKED->MySQL->getVal('datetime_started',
                $this->config->dbt_user_exercises, 'id = ' . $id);
        }

        ///////////////
        //           Measurements        //
                              /////////////
        
        /**
        * Get a user's current measurements
        *
        * @throws NotLoggedInException If the user is not logged in
        * @return Array All current measuremenst for this user
        */  
        public function getCurrentMeasurements(){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));   
            return $this->JACKED->MySQL->getRow(
                $this->config->dbt_measurements,
                'user_id = ' . $userid . ' AND 
                datetime_measured = ( 
                    SELECT MAX(datetime_measured) FROM ' . $this->config->dbt_measurements . ' WHERE user_id = ' . $userid . '
                )',
                MYSQL_ASSOC
            );
        }
        
        /**
        * Get the most recent value for a given measurement field at a given timestamp
        *
        * @param String $field The name of the measurement to retrieve.
        * @param string $user [optional] User to get measurement for. Defaults to currently logged in user.
        * @param string $timestamp [optional] Time at which to find the measurement value. Defaults to now.
        * @throws NotLoggedInException If the user is not logged in
        * @return int Value of the measurement at the timestamp, False if none was recorded by that time. 
        */  
        public function getUserMeasurementsRollup($field, $userid = false, $timestamp = false){
            if(!$userid){
                $this->JACKED->Flock->requireLogin();
                $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }
            if(!$timestamp)
                $timestamp = time();
        
            return $this->JACKED->MySQL->getVal(
                $this->JACKED->MySQL->sanitize($field),
                $this->config->dbt_measurements,
                'user_id = ' . $userid . ' AND 
                datetime_measured = ( 
                    SELECT 
                        MAX(datetime_measured) 
                    FROM 
                        ' . $this->config->dbt_measurements . ' 
                    WHERE 
                        user_id = ' . $userid . ' AND 
                        datetime_measured <= ' . $timestamp . ' 
                )'
            );
        }
        
        /**
        * Get the percent net change of a given measurement over a given timeframe
        *
        * @param String $field The name of the measurement to retrieve.
        * @param string $timeframe [optional] Timeframe of activities, one of: week, month, lifetime. Defaults to lifetime.
        * @throws NotLoggedInException If the user is not logged in
        * @return int Signed percent change in field over timeframe. 0 if no data exists. Negative change indicates that the value for the measurement increased, positive change indicates a decrease. 
        */  
        public function getMeasurementChange($field, $timeframe = false){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            switch($timeframe){
                case 'week':
                    $timelimit = time() - 604800;
                    break;
                case 'month':
                    $timelimit = time() - 2629744;
                    break;
                default:
                    $timelimit = 0;
                    break;
            }
            
            $current = $this->getMeasurement($field);
            if(!$current)
                return 0;

            $old = $this->JACKED->MySQL->getVal(
                $this->JACKED->MySQL->sanitize($field),
                $this->config->dbt_measurements,
                'user_id = ' . $userid . ' AND 
                datetime_measured = ( 
                    SELECT 
                        MIN(datetime_measured) 
                    FROM 
                        ' . $this->config->dbt_measurements . ' 
                    WHERE 
                        user_id = ' . $userid . ' AND 
                        datetime_measured > ' . $timelimit . ' 
                )'
            );
  
            //it's impossible to get a $current without getting an $old, so we don't need to check it
            
            $change = $old - $current;
            $pct = ($change / $old) * 100;
            return $pct;
        }
        
        /**
        * Get all of a user's measurements
        *
        * @throws NotLoggedInException If the user is not logged in
        * @return Array All measurements for this user
        */  
        public function getAllMeasurements(){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return $this->JACKED->MySQL->getRows(
                $this->config->dbt_measurements,
                'user_id = ' . $userid
            );
        }
        
        /**
        * Get a specific one of a user's current measurements
        *
        * @param String $field The name of the measurement to retrieve
        * @throws NotLoggedInException If the user is not logged in
        * @return int The value of the measurement requested
        */  
        public function getMeasurement($field){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $retVal = $this->JACKED->MySQL->getVal(
                $this->JACKED->MySQL->sanitize($field),
                $this->config->dbt_measurements,
                'user_id = ' . $userid . ' AND 
                datetime_measured = ( 
                    SELECT MAX(datetime_measured) FROM ' . $this->config->dbt_measurements . ' WHERE user_id = ' . $userid . '
                )'
            );
            if(!$retVal)
                $retVal = false;
            return $retVal;
        }
        
        /**
        * Update a user's measurements
        *
        * @param $measurements Array Associative array of the measurements to update. Any measurements not in this array will be rolled up from older measurement sets.
        * @throws NotLoggedInException If the user is not logged in
        * @return int ID of the added measurement set
        */  
        public function updateMeasurements($measurements){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $current = $this->getCurrentMeasurements();
            if($current){
                unset($current['id']);
                $new = array_merge($current, $measurements);
            }else{
                $new = $measurements;
                $new['user_id'] = $userid;
            }
            $new['datetime_measured'] = time();
            $done = $this->JACKED->MySQL->insertValues($this->config->dbt_measurements, $new);
            
            //check for trophies
            
            if(array_key_exists('weight', $measurements)){
                $change = $this->getMeasurementChange('weight');
                if($change >= 10){
                    try{
                        $this->JACKED->Lookit->queuePush(15);
                        $this->JACKED->Lookit->queuePush(14);
                        $this->JACKED->Lookit->queuePush(13);
                        $this->JACKED->Lookit->queuePush(12);
                    }catch(AchievementAlreadyAwardedException $e){
                        error_log($e->getMessage());
                    }
                }elseif($change >= 5){
                    try{
                        $this->JACKED->Lookit->queuePush(14);
                        $this->JACKED->Lookit->queuePush(13);
                        $this->JACKED->Lookit->queuePush(12);
                    }catch(AchievementAlreadyAwardedException $e){
                        error_log($e->getMessage());
                    }
                }elseif($change >= 3){
                    try{
                        $this->JACKED->Lookit->queuePush(13);
                        $this->JACKED->Lookit->queuePush(12);
                    }catch(AchievementAlreadyAwardedException $e){
                        error_log($e->getMessage());
                    }
                }elseif($change >= 1){
                    try{
                        $this->JACKED->Lookit->queuePush(12);
                    }catch(AchievementAlreadyAwardedException $e){
                        error_log($e->getMessage());
                    }
                }
            }
            
            
            return $done;
        }
        
        
        ///////////////
        //         User Management       //
                              /////////////
                              
        /**
        * Creates a Vitogo user with the given data.
        * 
        * @param string $email The email to log in with
        * @param string $password The user's password 
        * @throws ExistingUserException if the username already exists
        * @return boolean Whether the user was created successfully
        */
        public function createUser($email, $password, $gender, $weight, $height, $birthday, $workout_goal_id, $activity_level_id, $photo=NULL, $given_name = '', $family_name = '', $goal = '', $fat_percent = 0, $runkeeper_id=0, $loseit_id=0, $facebook_id=0, $twitter=''){
            $program = $this->selectTrainingProgram($workout_goal_id, $activity_level_id);
            $done = $this->JACKED->Flock->createUser($email, $password, array(
                'given_name' => $given_name,
                'family_name' => $family_name,
                'gender' => $gender,
                'photo' => $photo,
                'birthday' => $birthday,
                'goal' => $goal,
                'training_program_id' => $program,
                'activity_level_id' => $activity_level_id,
                'workout_goal_id' => $workout_goal_id,
                'runkeeper' => $runkeeper_id,
                'loseit_id' => $loseit_id,
                'facebook_id' => $facebook_id,
                'twitter' => $twitter, 
                'datetime_joined' => time()
            ));
            $done = $done && $this->updateMeasurements(array(
                'height' => $height,
                'weight' => $weight,
                'fat' => $fat_percent,
                'datetime_measured' => time()
            ));
            if(!$done){
                throw new Exception('Couldn\'t create the user. ' . $this->JACKED->MySQL->getError());
            }else{
                return $this->login($email, $password);
            }
        }
                
        /**
        * Updates the logged in Vitogo user with the given data.
        * 
        * @throws NotLoggedInException if the user is not logged in
        * @throws ExistingUserException if the username already exists
        * @return boolean Whether the user was updated successfully
        */
        public function updateUser($details){
            $this->JACKED->Flock->requireLogin();
            $email = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.username'));
            return $this->JACKED->Flock->updateUser($email, $details);
        }
                
        /**
        * Updates the logged in Vitogo user's training program with the given data.
        * 
        * @param $workoutGoal int ID of the new workout goal
        * @param $activityLevel int ID of the new activity level
        * @throws NotLoggedInException if the user is not logged in
        * @throws ExistingUserException if the username already exists
        * @return boolean Whether the user was updated successfully
        */
        public function updateUserTrainingProgram($workoutGoal, $activityLevel){
            $this->JACKED->Flock->requireLogin();
            $email = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.username'));
            
            $training_program_id = $this->selectTrainingProgram($workoutGoal, $activityLevel);
            
            return $this->updateUser(array(
                'workout_goal_id' => $workoutGoal,
                'activity_level_id' => $activityLevel,
                'training_program_id' => $training_program_id,
                'datetime_week_started' => time(),
                'last_training_day_order' => 0
            ));
        }
                
        /**
        * Updates the logged in Vitogo user's login information with the given data.
        * 
        * @param $email String Replace the user's email with this one
        * @param $password String Replace the user's password with this one
        * @throws NotLoggedInException if the user is not logged in
        * @throws ExistingUserException if the new email already exists
        * @return boolean Whether the user was updated successfully
        */
        public function updateUserLogin($email = false, $password = false){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $emaildone = ($email)? $this->JACKED->Flock->updateUserEmail($userid, $email) : true;
            
            $passdone = ($password)? $this->JACKED->Flock->updateUserPassword($userid, $password) : true;
            
            return $emaildone && $passdone;
        }
        
        /**
        * Logs in a Vitogo User
        * 
        * @param string $email The email to log in with
        * @param string $password The user's password
        * @throws UserNotFoundException if the user is not found
        * @throws IncorrectPasswordException if the given password does not match the username's login
        * @return Array The new user session data
        */
        public function login($email, $password){
            if(
                $this->JACKED->Flock->login($email, $password) &&
                $this->JACKED->MySQL->update($this->config->dbt_users, array('datetime_last_login' => time()), 'email = "' . $email . '"')
            ){
                return $this->JACKED->Flock->getUserSession();
            }else{
                return false;
            }
        }
        
        /**
        * Logs in a Vitogo User without hashing their password first
        * 
        * @param string $email The email to log in with
        * @param string $hpassword The user's password hash
        * @throws UserNotFoundException if the user is not found
        * @throws IncorrectPasswordException if the given password does not match the username's login
        * @return Array The new user session data
        */
        public function hashedLogin($email, $hpassword){
            if(
                $this->JACKED->Flock->hashedLogin($email, $hpassword) &&
                $this->JACKED->MySQL->update($this->config->dbt_users, array('datetime_last_login' => time()), 'email = "' . $email . '"')
            ){
                return $this->JACKED->Flock->getUserSession();
            }else{
                return false;
            }
        }
        
        /**
        * Logs in a Vitogo User using their autoLogin token
        * 
        * This is just a stub to allow us to hook it in the Yapp config pre call
        * 
        * @param string $token The email to log in with
        * @throws IncorrectPasswordException if the given password does not match the username's login
        * @return boolean Whether the user is now logged in successfully
        */
        public function autoLogin($token){
            //pay no attention to the method signature, the precall hooks have their own way with it
            $args = func_get_args();
            $autoLoggedIn = $args[1];
            $user_id = $args[2];
            $loggedIn = (isset($autoLoggedIn) && $autoLoggedIn)? $autoLoggedIn : false;
            if($loggedIn){
                $vals = $this->JACKED->MySQL->getRow($this->config->dbt_users, "id='" . $user_id . "'");
                
                if($vals['password']){
                    $userID = $vals['id'];
                    $this->JACKED->Sessions->write("auth.Flock", array(
                        'loggedIn' => true,
                        'username'     => $vals['email'], 
                        'email'     => $vals['email'], 
                        'userid'   => $userID,
                        'sessionID' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])
                    ));
                    $this->JACKED->MySQL->update($this->config->dbt_users, array('datetime_last_login' => time()), 'id = "' . $userID . '"');
                }else{
                    throw new IncorrectPasswordException();
                }
            }else{
                throw new IncorrectPasswordException();
            }
            return $loggedIn;
        }
              
        /**
        * Logs out a Vitogo User
        * 
        * @throws NotLoggedInException if the user is not logged in
        * @return boolean Whether the user is now logged out successfully
        */
        public function logout(){
            $this->JACKED->Flock->requireLogin();
            return $this->JACKED->Flock->logout();
        }
                
        /**
        * Gets all of a given Vitogo user's data
        * 
        * @param string $email The email of the user account to get
        * @throws UserNotFoundException if the user is not found
        * @return Array Associative array of all stored user data
        */
        public function getUserByEmail($email){
            return $this->JACKED->Flock->getUser($email);
        }
              
        /**
        * Gets all of a given Vitogo user's data by id
        * 
        * @param string $id The ID of the user account to get
        * @throws UserNotFoundException if the user is not found
        * @return Array Associative array of all stored user data
        */
        public function getUserByID($id){
            return $this->JACKED->Flock->getUserByID($id);
        }
              
        /**
        * Gets a count of a given Vitogo user's activities by id within a given timeframe. Defaults to lifetime.
        * 
        * @param string $id The ID of the user account to get
        * @param string $timeframe [optional] Timeframe of activities, one of: week, month, lifetime
        * @throws UserNotFoundException if the user is not found
        * @return int Associative array of all stored user data
        */
        public function getUserExerciseCountByID($id, $timeframe='week'){
            switch($timeframe){
                case 'week':
                    $timelimit = time() - 604800;
                    break;
                case 'month':
                    $timelimit = time() - 2629744;
                    break;
                default:
                    $timelimit = 0;
                    break;
            }
            
            $result = $this->JACKED->MySQL->query('
                SELECT
                      COUNT(U.id) AS count
                FROM
                      ' .$this->config->dbt_user_exercises . ' U,
                      ' .$this->config->dbt_exercises . ' E
                WHERE
                      U.user_id = ' . $id . ' AND
                      U.datetime_completed IS NOT NULL AND
                      E.id = U.exercise_id AND (
                          U.datetime_completed IS NOT NULL AND
                          U.datetime_completed > ' . $timelimit . '
                      )
            ');
            $parsed = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            return $parsed['count'];
        }
                
        /**
        * Gets all of the logged in Vitogo user's data
        * 
        * @throws NotLoggedInException if no user is logged in
        * @return Array Associative array of all stored user data
        */
        public function getUser(){
            $this->JACKED->Flock->requireLogin();
            $email = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.username'));
            return $this->JACKED->Flock->getUser($email);
        }
                
        /**
        * Gets the user's training program details
        * 
        * @throws NotLoggedInException if no user is logged in
        * @return Array Associative array of all training program data
        */
        public function getUserTrainingProgram(){
            $this->JACKED->Flock->requireLogin();
            $id = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            $query = '
                SELECT P.* FROM 
                    ' . $this->config->dbt_users . ' U,
                    ' . $this->config->dbt_training_programs . ' P
                WHERE
                    U.id = ' . $id . ' AND
                    P.id = U.training_program_id
                LIMIT 0, 1
            ';
            $result = $this->JACKED->MySQL->query($query);
            $done = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            return $done;
        }
                
        /**
        * Gets some user stats 
        * 
        * @param int $user [optional] The user to get stats for, defaults to logged in user
        * @throws NotLoggedInException if no user is logged in and no user was provided
        * @return Array Associative array of all stored user data
        */
        public function getUserStats($user = false){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $id = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }else{
                $id = $user;
            }
            $query = '
                SELECT 
                    SUM( S.weight ) AS weight, 
                    SUM( S.reps ) AS reps, 
                    COUNT( DISTINCT E.id ) AS exercises,
                    U.days_completed AS days
                FROM 
                    ' . $this->config->dbt_users . ' U,
                    ' . $this->config->dbt_user_exercises . ' E
                JOIN 
                    ' . $this->config->dbt_user_exercise_sets . ' S 
                ON 
                    S.user_exercise_id = E.id
                WHERE 
                    E.user_id = ' . $id . ' AND 
                    E.datetime_completed IS NOT NULL AND
                    U.id = ' . $id . '
            ';            
            $result = $this->JACKED->MySQL->query($query);
            $parsed = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            $parsed['calories'] = $this->getUserCaloriesByTimeFrame($user);
            return $parsed;
        }

        /**
        * Get the number of calories burned by a user over a given timeframe
        *
        * @param int $user [optional] ID of the user to get data for, defaults to logged in user
        * @param string $timeframe [optional] One of: week, month, lifetime. Defaults to lifetime
        * @throws NotLoggedInException If no user is provided and the user is not logged in
        * @return int Number of calories burned for the given timeframe
        */
        public function getUserCaloriesByTimeframe($user = false, $timeframe = 'lifetime'){
            switch($timeframe){
                case 'week':
                    $startTime = time() - 604800;
                    break;
                case 'month':
                    $startTime = time() - 2629744;
                    break;
                default:
                    $startTime = 0;
                    break;
            }
            return $this->getUserCaloriesByTimeRange($startTime, time(), $user);
        }
        
        /**
        * Get the number of calories burned by a user within a specified time range
        *
        * @param int $startTime The timestamp starting the range
        * @param int $endTime [optional] The timestamp ending the range. Defaults to now.
        * @param int $user [optional] ID of the user to get data for, defaults to logged in user
        * @throws NotLoggedInException If no user is provided and the user is not logged in
        * @return int Number of calories burned for the given timeframe
        */
        public function getUserCaloriesByTimeRange($startTime, $endTime = false, $user = false){
            if(!$user){
                $this->JACKED->Flock->requireLogin();
                $user = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            }

            if(!$endTime)
                $endTime = time();

            $result = $this->JACKED->MySQL->query('
                SELECT SUM(calories) AS calorie_total FROM ' .
                    $this->config->dbt_user_exercise_sets . ' S,' .
                    $this->config->dbt_user_exercises . ' E
                WHERE
                    E.user_id = ' . $user . ' AND
                    S.user_exercise_id = E.id AND 
                    S.datetime_completed > ' . $startTime .' AND
                    S.datetime_completed <= ' . $endTime .'
            ');
            $parsed = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            return $parsed['calorie_total'];
        }
              
        /**
        * Updates the logged in Vitogo user's settings json, adds any new nonexisting keys
        * 
        * @param $settings Array 
        * @throws NotLoggedInException if no user is logged in
        * @return Boolean Whether the update was successful
        */
        public function updateSettings($settings){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $currentSettings = json_decode($this->getUserSettings(), true);
            $newSettings = json_encode(array_merge($currentSettings, $settings));
            
            $done = $this->JACKED->MySQL->update(
                $this->config->dbt_users, 
                array(
                    'settings' => $newSettings
                ),
                'id = "' . $userid . '"'
            );
            if(!$done)
                throw new UserNotFoundException();
            return $done;
        }
            
        /**
        * Gets of the logged in Vitogo user's settings json
        * 
        * @throws NotLoggedInException if no user is logged in
        * @throws UserNotFoundException if the user is not found
        * @return string JSON stored in users.settings
        */
        public function getUserSettings(){
            $this->JACKED->Flock->requireLogin();
            $email = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.username'));
            $settings = $this->JACKED->MySQL->getVal('settings', $this->config->dbt_users, 'email = "' . $email . '"');
            if(!$settings)
                $settings = '{}';
            return $settings;
        }
        
        /**
        * Gets all of a given Vitogo user's social connection data
        * 
        * @param string $email The email of the user account to get data for
        * @throws UserNotFoundException if the user is not found
        * @return Array Associative array of all stored user social connection data
        */
        public function getUserSocialByEmail($email){
            $vals = $this->JACKED->MySQL->getRowVals('runkeeper, loseit_id, facebook_id, twitter', $this->config->dbt_users, 'email = "' . $email . '"');
            if(!$settings)
                throw new UserNotFoundException();
            return $vals;
        }
        
        /**
        * Gets all of the logged in Vitogo user's social connection data
        * 
        * @throws NotLoggedInException if no user is logged in
        * @throws UserNotFoundException if the user is not found
        * @return Array Associative array of all stored user social connection data
        */
        public function getUserSocial(){
            $this->JACKED->Flock->requireLogin();
            $email = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.username'));
            $vals = $this->JACKED->MySQL->getRowVals('runkeeper, loseit_id, facebook_id, twitter', $this->config->dbt_users, 'email = "' . $email . '"');
            if(!$settings)
                throw new UserNotFoundException();
            return $vals;
        }
        
        /**
        * Gets all users who are connected to facebook
        * 
        * @return Array Associative array of users
        */
        public function getFacebookUsers(){
            $this->JACKED->Flock->requireLogin();
            $id = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            $query = '
                SELECT U.id, U.facebook_id FROM 
                    ' . $this->config->dbt_users . ' U
                WHERE
                    U.facebook_id IS NOT NULL AND U.facebook_id != 0
            ';
            $result = $this->JACKED->MySQL->query($query);
            $done = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            if(is_string(current($done)))
            	$done = array($done);
            return $done;
            
        }
        
        /**
        * Gets all users who are connected to twitter
        * 
        * @return Array Associative array of users
        */
        public function getTwitterUsers(){
            $this->JACKED->Flock->requireLogin();
            $id = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            $query = '
                SELECT U.id, U.twitter FROM 
                    ' . $this->config->dbt_users . ' U
                WHERE
                    U.twitter IS NOT NULL AND U.twitter != 0
            ';
            $result = $this->JACKED->MySQL->query($query);
            $done = $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
            if(is_string(current($done)))
            	$done = array($done);
            return $done;
            
        }
    }    
 ?>
