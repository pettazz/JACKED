<?php

    class Vitogo extends JACKEDModule{
		const moduleName = 'Vitogo';
		const moduleVersion = 1.0;
		const dependencies = 'MySQL, Flock, Sessions';
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
        * @return Array Associative array of all matching user exercises
        */
		public function getUserExercisesByExerciseID($exerciseID){
		    $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
		    return $this->JACKED->MySQL->getRows(
		        $this->config->dbt_user_exercises, 
		        'exercise_id = "' . $exerciseID . '" AND
		        user_id = "' . $userid . '"'
		    );
		}
		 
        /**
        * Gets all user exercise sets for a user exercise id
        * 
        * @param $exerciseID int User Exercise id
        * @return Array Associative array of all matching user exercise sets
        */
		public function getUserExerciseSetsByUserExerciseID($exerciseID){
		    $this->JACKED->Flock->requireLogin();
            
		    return $this->JACKED->MySQL->getRows(
		        $this->config->dbt_user_exercise_sets, 
		        'user_exercise_id = "' . $exerciseID
		    );
		}
		
		/**
        * Gets all of todays exercises for the user. Does not distinguish between completed/started/new.
        * 
		* @throws NotLoggedInException If the user is not logged in
        * @return Array Associative array of all matching exercises
        */
		public function getTodaysExercises(){
		    $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));

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
                     ' . $this->config->dbt_training_days . ' D,
                     ' . $this->config->dbt_users . ' U,
                     ' . $this->config->dbt_muscle_groups . ' M`
		         WHERE 
		             U.id = ' . $userid . ' AND
		             D.order = U.last_training_day_order + 1 AND
		             S.training_day_id = D.id AND
		             E.id = S.exercise_id AND
		             M.id = E.muscle_group_id
		        '
		    );
		    return $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
        }
        
        /**
        * Gets all completed exercises for this user
        * 
		* @throws NotLoggedInException If the user is not logged in
        * @return Array Associative array of all matching exercises
        */
		public function getAllCompletedExercises(){
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
                      ' .$this->config->dbt_muscle_groups . ' M
                WHERE
                      U.user_id = ' . $userid . ' AND
                      U.datetime_completed IS NOT NULL AND
                      E.id = U.exercise_id AND
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
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $program = $this->getUserTrainingProgram();
            $days = $this->JACKED->MySQL->getRows(
                $this->config->dbt_training_days, 
                'training_program_id = "' . $program['id'] . '"'
            );
            
            if(count($days) > 0){
                foreach($days as $id => $training_day){
                    $exercises = $this->getExercisesByTrainingDay($training_day['id']);
                    $days[$id]['exercises'] = $exercises;
                    if(count($exercises)){
                        $flag = false;
                        foreach($exercises as $eid => $anExercise){
                            $userExs = $this->getUserExercisesByExerciseID($anExercise['id']);
                            $flag = !(is_null($userExs[0]['datetime_completed']));
                            $day[$id]['user_sets'] = $this->getUserExerciseSetsByUserExerciseID($userExs[0]['id']);
                        }
                        $days[$id]['completed'] = $flag;
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
		* Checks if all the exercises for today have been completed
		*
		* @throws NotLoggedInException if the user is not logged in
		* @return Boolean Whether all of today's exercises have been completed
		*/
		private function checkTodayCompleted(){
		    $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $exercises = $this->getTodaysExercises();
            if(count($exercises)){
                $flag = true;
                foreach($exercises as $eid => $anExercise){
                    //a little messy, but short circuits all the hard work if we've already gotten a false
                    $flag = $flag && $userEx = $this->getUserExercisesByExerciseID($anExercise['id']) && is_null($userEx[0]['datetime_completed']);
                }
            }
            return $flag;
		}
			
		/**
		/* Checks if all the exercises for today have been completed, if so 
		/* makes updates to the tracking data in the users table: last_training_day_order.
		/* Potentially dangerous mutating method, so it's private from the API.
		/*
		/* @throws NotLoggedInException if the user is not logged in
		/* @return void
		*/
		private function incrementTrainingDay(){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            $last_day = $this->JACKED->MySQL->getVal('last_training_day_order', $this->config->dbt_users, 'id = ' . $userid);
            
		    if($this->checkTodayCompleted()){
		        if($last_day == 7){
		            $this->JACKED->MySQL->update(
                        $this->config->dbt_users,
                        array(
                            'last_training_day_order' => 0
                        ),
                        'id = ' . $userid
                    );
		        }else if($last_day == 0){
		            $this->JACKED->MySQL->update(
                        $this->config->dbt_users,
                        array(
                            'datetime_week_started' => time(),
                            'last_training_day_order' => 1
                        ),
                        'id = ' . $userid
                    );
		        }else{
		            $this->JACKED->MySQL->update(
		                $this->config->dbt_users, 
		                    array(
                            'last_training_day_order' => 'literal:last_training_day_order + 1'
                        ), 
                        'id = ' . $userid
                    );
		        }
		    }
		}
					
		/**
		* Add a fully completed user exercise
		*
		* @param $exerciseid int The ID of the completed exercise
		* @param $notes String Fulltext of any notes added by the user
		* @param $started int Unix Epoch timestamp for workout start
		* @param $completed int Unix Epoch timestamp for workout completion
		* @throws NotLoggedInException If the user is not logged in
		* @return int ID of the added exercise
		*/	
		public function addExerciseCompleted($exerciseid, $notes, $started, $completed){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return (
                $this->JACKED->MySQL->insert($this->config->dbt_user_exercises, array(
                    "user_id" => $userid,
                    "exercise_id" => $exerciseid,
                    "notes" => $notes,
                    "datetime_started" => $started,
                    "datetime_completed" => $completed
                )) &&
                $this->incrementTrainingDay()
            );
		}
			
		/**
		* Start a user exercise now
		*
		* @param $exerciseid int The ID of the exercise
		* @param $notes String [Optional] Fulltext of any notes added by the user
		* @throws NotLoggedInException If the user is not logged in
		* @return int The ID of the new user exercise
		*/	
		public function startExerciseNow($exerciseid, $notes = ''){
            $this->JACKED->Flock->requireLogin();
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            return (
                $this->JACKED->MySQL->insertValues($this->config->dbt_user_exercises, array(
                    "user_id" => $userid,
                    "exercise_id" => $exerciseid,
                    "notes" => $notes,
                    "datetime_started" => time()
                )) &&
                $this->incrementTrainingDay()
            );
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
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            if($this->JACKED->MySQL->getVal('last_training_day_order', $this->config->dbt_users, 'id = ' . $userid) ==  7){//TODO: FIX THIS
                return (
                    $this->JACKED->MySQL->update($this->config->dbt_user_exercises, array(
                        "datetime_completed" => time()
                    ), 'id = ' . $exerciseid)
                    &&
                    $this->incrementTrainingDay()
                );
            }
		}
			
		/**
		* Update a user's sets after one has been completed
		*
		* @param $exerciseid int The ID of the completed exercise
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
            $userid = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            
            $query = '
                SELECT `set` FROM ' . $this->config->dbt_user_exercise_sets . ' 
                WHERE user_exercise_id = ' . $exerciseid . ' ORDER BY `set` LIMIT 0, 1
            ';
            $result = $this->JACKED->MySQL->query($query);
            $parsed = $this->JACKED->MySQL->parseResult($result);
            $oldset = ($parsed)? $parsed[0]['set'] : 0;
            return $this->JACKED->MySQL->insertValues($this->config->dbt_user_exercise_sets, array(
                    'user_exercise_id' => $exerciseid,
                    'weight' => $weight,
                    'set' => $oldset + 1,
                    'reps' => $reps,
                    'rest' => $rest,
                    'datetime_completed' => $completed
            ));
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
                    SELECT MAX(datetime_measured) FROM ' . $this->config->dbt_measurements . '
                )',
                MYSQL_ASSOC
            );
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
                'user_id = ' . $userid,
                MYSQL_ASSOC
            );
		}
		
		/**
		* Get a specific one of a user's current measurements
		*
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
                    SELECT MAX(datetime_measured) FROM ' . $this->config->dbt_measurements . '
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
		    return $this->JACKED->MySQL->insertValues($this->config->dbt_measurements, $new);
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
            
            $program = selectTrainingProgram($workout_goal_id, $activity_level_id);
        
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
                'runkeeper_id' => $runkeeper_id,
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
                'datetime_week_started' => NULL,
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
            return $done[0];
        }
                
        /**
        * Gets some user stats 
        * TODO: more of this?
        * 
        * @throws NotLoggedInException if no user is logged in
        * @return Array Associative array of all stored user data
        */
        public function getUserStats(){
            $this->JACKED->Flock->requireLogin();
            $id = $this->JACKED->MySQL->sanitize($this->JACKED->Sessions->read('auth.Flock.userid'));
            $query = '
                SELECT 
                    SUM( S.weight ) AS weight, 
                    SUM( S.reps ) AS reps, 
                    COUNT( DISTINCT E.id ) AS exercises
                FROM 
                    ' . $this->config->dbt_user_exercises . ' E
                JOIN 
                    ' . $this->config->dbt_user_exercise_sets . ' S 
                ON 
                    S.user_exercise_id = E.id
                WHERE 
                    E.user_id = ' . $id . ' AND 
                    E.datetime_completed IS NOT NULL 
            ';            
            $result = $this->JACKED->MySQL->query($query);
            return $this->JACKED->MySQL->parseResult($result, MYSQL_ASSOC);
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
                throw new UserNotFoundException();
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
            $vals = $this->JACKED->MySQL->getRowVals('runkeeper_id, loseit_id, facebook_id, twitter', $this->config->dbt_users, 'email = "' . $email . '"');
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
            $vals = $this->JACKED->MySQL->getRowVals('runkeeper_id, loseit_id, facebook_id, twitter', $this->config->dbt_users, 'email = "' . $email . '"');
            if(!$settings)
                throw new UserNotFoundException();
            return $vals;
        }
    }    
 ?>