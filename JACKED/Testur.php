<?php

    class Testur extends JACKEDModule{
        const moduleName = 'Testur';
        const moduleVersion = 1.0;
        public static $dependencies = array("Flock", "MySQL");
        
        private $markov;

        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);

            $JACKED->loadLibrary('MarkovGenerator');
            ini_set('memory_limit', '50M'); // only needed to create the table

            $this->markov = new MarkovLetterChain(6);
            $this->markov->feed( file_get_contents('JACKED/lib/source.txt'));
            $this->markov->root(2);
        }

        /**
        * Generate a Markov Chain sentence
        * 
        * @param $use_punct Boolean [optional] Whether to add a random punctuation mark at the end of the sentence. Defaults to true.
        * @return String The generated sentence
        */
        public function generateSentence($use_punct = true){
            $punct = array(0 => ".", 1 => "?", 2 => "!", 3 => "?!");
            $lol = ucfirst($this->markov->generate(1, 20));
            for($i = 0; $i < rand(2, 30); $i++){
                $lol .= " " . $this->markov->generate(1, 20);
            }
            if($use_punct){
                $lol .= $punct[rand(0, 3)];
            }
            
            return $lol;
        }
        
        /**
        * Generate a paragraph of Markov Chain sentences
        * 
        * @param $max_sentences int [optional] Maximum number of random sentences to create. Defaults to 9.
        * @return String The generated sentence
        */
        public function generateParagraph(){
            $lol = $this->generateSentence();
            for($i = 0; $i < rand(0, 9); $i++){
                $lol .= " " . $this->generateSentence();
            }
            
            return $lol;
        }

        /**
        * Creates a randomly generated Flock user
        * 
        * @param $password String [optional] Password for new user. Defaults to 'lol'.
        * @return Array All details of new user 
        */
        public function generateFlockUser($password = NULL){
            $email = $this->markov->generate(10, 30) . '@gmail.com';
            $username = $markov->generate(7, 30);
            $password = ($password)? $password : 'lol';
            $details = array(
                'first_name' => ucfirst($this->markov->generate(7, 20)),
                'last_name' => ucfirst($this->markov->generate(8, 30))
            );
            try{
                $guid = $JACKED->Flock->createUser($email, $username, $password, $details);
                $details['email'] = $email;
                $details['guid'] = $guid;
                $details['password'] = $password;
            }catch(ExistingUserException $e){
                return $this->generateFlockUser($password);
            }

            return $details;
        }
    }

?>