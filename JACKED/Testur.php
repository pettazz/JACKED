<?php

    date_default_timezone_set('America/New_York');

    class Testur extends JACKEDModule{
        const moduleName = 'Testur';
        const moduleVersion = 1.0;
        public static $dependencies = array("Flock", "MySQL", "Blag");
        
        private $markov;

        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);

            $JACKED->loadLibrary('MarkovGenerator');
            ini_set('memory_limit', '500M'); // only needed to create the table

            $this->markov = new MarkovLetterChain(6, true);
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
        * @param $store Boolean [optional] Whether to store the new user in the database. Defaults to true.
        * @return Array All details of new user 
        */
        public function generateFlockUser($password = NULL, $store = true){
            $email = $this->markov->generate(10, 30) . '@gmail.com';
            $username = $this->markov->generate(7, 30);
            $password = ($password)? $password : 'lol';
            $details = array(
                'first_name' => ucfirst($this->markov->generate(7, 20)),
                'last_name' => ucfirst($this->markov->generate(8, 30))
            );
            if($store){
                try{
                    $guid = $this->JACKED->Flock->createUser($username, $email, $password, $details);
                    $details['guid'] = $guid;
                }catch(ExistingUserException $e){
                    return $this->generateFlockUser($password);
                }
            }
            $details['email'] = $email;
            $details['username'] = $username;
            $details['password'] = $password;

            return $details;
        }

        /**
        * Creates a randomly generated Blag post.
        * 
        * @param $timestamp int [optional] The timestamp with which to create the post. Defaults to the current timestamp.
        * @param $author Array [optional] Deatils of a Flock User to use as the Author. Defaults to generating a new one.
        * @param $title String [optional] Title of the post to create. Defaults to randomly generated.
        * @return Array All details of new post
        */
        public function createPost($timestamp = NULL, $author = NULL, $title = NULL){
            $content = '';
            for($x = 0; $x <= rand(0, 5); $x++){
                $content .= $this->generateSentence();
            }
            $posted = $timestamp? $timestamp : rand(1022967819, time());
            $author = $author? $author : $this->generateFlockUser();
            $details = array(
                'guid' => $this->JACKED->Util->uuid4(),
                'author' => $author['guid'],
                'title' => ($title? $title : ucfirst($this->generateSentence(false))),
                'headline' => ucfirst($this->generateSentence(false)),
                'posted' => $posted,
                'content' => $content
            );
            $this->JACKED->MySQL->insert('Blag', $details);
            $details['author'] = $author;
            return $details;
        }
    }

?>