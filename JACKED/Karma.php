<?php

    class Karma extends JACKEDModule{
        /*
            This isn't reddit

            Vote on shit
        */
            
        const moduleName = 'Karma';
        const moduleVersion = 1.0;
        public static $dependencies = array('MySQL', 'Flock');
        

        /**
        * Save a Vote on a given content item with a given weight
        * 
        * @param $guid String The GUID of the content item to vote on
        * @param $score int The weight this vote will carry (signed int). Ex: 1, -3, 22, etc
        * @return String The GUID of the new vote, false if unsuccessful
        */
        public function vote($guid, $score){
            if($this->getMyVote($guid)){
                throw new AlreadyVotedException();
            }

            $new_guid = $this->JACKED->Util->uuid4();
            $done = $this->JACKED->MySQL->insert(
                $this->config->dbt_votes,
                array(
                    'guid' => $new_guid,
                    'target' => $guid,
                    'Source' => $this->JACKED->Flock->getSource(),
                    'weight' => $score,
                    'timestamp' => time()
                )
            );
            if($done){
                return $new_guid;
            }else{
                return false;
            }
        }

        /**
        * Saves a Vote of +1 to a given content item. Simple wrapper for a vote() call.
        * 
        * @param $guid String The GUID of the content item to vote on
        * @return String The GUID of the new vote, false if unsuccessful
        */
        public function upvote($guid){
            return $this->vote($guid, 1);
        }

        /**
        * Saves a Vote of -1 to a given content item. Simple wrapper for a vote() call.
        * 
        * @param $guid String The GUID of the content item to vote on
        * @return String The GUID of the new vote, false if unsuccessful
        */
        public function downvote($guid){
            return $this->vote($guid, -1);
        }

        /**
        * Gets the saved vote for a given Source on a given content item
        * 
        * @param $source String The GUID of the Source to get the vote for
        * @param $guid String The GUID of the content item to get vote for
        * @return String The weight of the vote, false if none exists
        */
        public function getSourceVote($source, $guid){
            return $this->JACKED->MySQL->get('weight', $this->config->dbt_votes, 'guid = \'' . $guid . '\'');
        }

        /**
        * Gets the saved vote for the current Source on a given content item
        * 
        * @param $source String The GUID of the Source to get the vote for
        * @return String The weight of the vote, false if none exists
        */
        public function getMyVote($guid){
            return $this->getSourceVote($this->JACKED->Flock->getSource(), $guid);
        }

        /**
        * Gets the saved votes for a given Source
        * 
        * @param $source String The GUID of the Source to get the votes for
        * @return Array Associative array of all existing Votes for this Source (guid, timestamp, 
        * target, Source, weight), false if none exist
        */
        public function getAllVotesForSource($source){
            return $this->MySQL->getRows($this->config->dbt_votes, 'Source = \'' . $source . '\'');
        }

        /**
        * Gets the total saved score for a given content item
        * 
        * @param $guid String The GUID of the content item to get score for
        * @return int Total value of all votes saved for target @guid
        */
        public function getScore($guid){
            $done = $this->MySQL->get(
                "function:SUM(weight) AS score", 
                $this->config->dbt_votes, 
                'target = \'' . $guid . '\''
            );
            if($done){
                return $done;
            }else{
                return 0;
            }
        }

        /**
        * Gets the total saved positive votes for a given content item
        * 
        * @param $guid String The GUID of the content item to get score for
        * @return int Total value of all positive votes saved for target @guid
        */
        public function getUpvotes($guid){
            $done = $this->MySQL->get(
                "function:SUM(weight) AS score", 
                $this->config->dbt_votes, 
                'target = \'' . $guid . '\' AND weight > 0'
            );
            if($done){
                return $done;
            }else{
                return 0;
            }
        }

        /**
        * Gets the total saved negative votes for a given content item
        * 
        * @param $guid String The GUID of the content item to get score for
        * @return int Total value of all negative votes saved for target @guid
        */
        public function getDownvotes($guid){
            $done = $this->MySQL->get(
                "function:SUM(weight) AS score", 
                $this->config->dbt_votes, 
                'target = \'' . $guid . '\' AND weight < 0'
            );
            if($done){
                return $done;
            }else{
                return 0;
            }
        }

        /**
        * Gets the total saved score for a given content item as it was at a given timestamp
        * 
        * @param $guid String The GUID of the content item to get score for
        * @param $timestamp The UNIX epoch timestamp of when to calculate the score
        * @return int Total value of all votes saved for target @guid before @timestamp
        */
        public function getScoreAtTimestamp($guid, $timestamp){
            $done = $this->MySQL->get(
                "function:SUM(weight) AS score", 
                $this->config->dbt_votes, 
                'target = \'' . $guid . '\' AND timestamp <= ' . $timestamp
            );
            if($done){
                return $done;
            }else{
                return 0;
            }
        }

    }

    class AlreadyVotedException extends Exception{
        protected $message = 'Source has already voted on this target.';
    }

?>