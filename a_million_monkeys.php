<?php

	if($_GET['go'] == 'nailedit'){


	    require('jacked_conf.php');
	    $JACKED = new JACKED(array("Blag", "Flock", "MySQL", "EYS"));
	    
	    $JACKED->importLib('MarkovGenerator');

		ini_set('memory_limit', '50M'); // only needed to create the table

		$markov = new MarkovLetterChain( 6 );
		$markov->feed( file_get_contents( 'JACKED/lib/source.txt' ) );
		$markov->root(2);

		function generateSentence($markov, $use_punct = true){
			$punct = array(0 => ".", 1 => "?", 2 => "!", 3 => "?!");
		    $lol = ucfirst($markov->generate(1, 20));
		    for($i = 0; $i < rand(2, 30); $i++){
		        $lol .= " " . $markov->generate(1, 20);
		    }
		    if($use_punct){
			    $lol .= $punct[rand(0, 3)];
			}
		    
		    return $lol;
		}
		
		function generateParagraph($markov){
		    $lol = generateSentence($markov);
		    for($i = 0; $i < rand(0, 9); $i++){
		        $lol .= " " . generateSentence($markov);
		    }
		    
		    return $lol;
		}


		echo "<h3>adding 200 authors.</h3>";
		$authors = array();
		for($i = 1; $i <= 200; $i++){
			$em = $markov->generate(10, 30);
			$details = array(
				'email' => $em . '@gmail.com',
				'first_name' => ucfirst($markov->generate(7, 20)),
				'last_name' => ucfirst($markov->generate(8, 30))
			);
			try{
				$guid = $JACKED->Flock->createUser($markov->generate(7, 30), 'lol', $details);
				$authors[] = $guid;
				echo 'added User: <strong>' . $details['first_name'] . ' ' . $details['last_name'] . '</strong>(' . $guid . ')<br />';
			}catch(ExistingUserException $e){
				echo 'duplicate username, trying again...<br />';
			}
		}

		echo "<h3>adding 600 posts.</h3>";
		for($i = 1; $i <= 600; $i++){
			$content = '';
			for($x = 0; $x <= rand(0, 5); $x++){
				$content .= generateParagraph($markov);
			}
			$posted = rand(1022967819, time());
			$details = array(
				'guid' => $JACKED->Util->uuid4(),
				'author' => $authors[rand(0, (count($authors) - 1))],
				'title' => ucfirst(generateSentence($markov, false)),
				'headline' => ucfirst(generateSentence($markov, false)),
				'last_name' => ucfirst($markov->generate(8, 30)),
				'posted' => $posted,
				'content' => $content
			);
			try{
				$JACKED->MySQL->insert($JACKED->Blag->config->dbt_posts, $details);
				echo 'added post: <strong>' . $details['title'] . '</strong> (' . $details['guid'] . ')<br />';
			}catch(Exception $e){
				echo 'broke. trying again...<br />';
			}
		}
		

	}else{
		die('no.');
	}

?>