<?php

	if($_GET['go'] == 'nailedit'){


	    require('jacked_conf.php');
	    $JACKED = new JACKED(array("Blag", "Flock", "EYS"));
	    $JACKED->config->offsetSet('debug', 0);
	    $JACKED->importLib('MarkovGenerator');

		ini_set('memory_limit', '50M'); // only needed to create the table

		$markov = new MarkovLetterChain( 6 );
		$markov->feed( file_get_contents( 'JACKED/lib/source.txt' ) );
		$markov->root(2);

		function generateSentence($markov){
			$punct = array(0 => ".", 1 => "?", 2 => "!", 3 => "?!");
		    $lol = ucfirst($markov->generate(1, 20));
		    for($i = 0; $i < rand(2, 30); $i++){
		        $lol .= " " . $markov->generate(1, 20);
		    }
		    $lol .= $punct[rand(0, 3)];
		    
		    return $lol;
		}
		
		function generateParagraph($markov){
		    $lol = generateSentence($markov);
		    for($i = 0; $i < rand(0, 9); $i++){
		        $lol .= " " . generateSentence($markov);
		    }
		    
		    return $lol;
		}

		if($_GET['type'] = 'authors'){
			echo "<h3>adding 200 authors.</h3>";
			for($i = 1; $i <= 200; $i++){
				$details = array(
					'email' => $markov->generate(10, 30) . '@gmail.com',
					'first_name' => $markov->generate(7, 20),
					'last_name' => $markov->generate(8, 30)
				);
				$newguid = $JACKED->Flock->createUser($markov->generate(7, 30), 'lol', $details);
				echo 'added User guid: <strong>' . $newguid . '</strong> - ' . $details['first_name'] . ' . ' . $details['last_name'] . '<br />';
			}
		}
		

	}else{
		die('no.');
	}

?>