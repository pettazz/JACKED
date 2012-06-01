<?php

	if($_GET['go'] == 'nailedit'){


	    require('jacked_conf.php');
	    $JACKED = new JACKED(array("Blag", "EYS"));
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


		echo generateParagraph($markov);

	}else{
		die('no.');
	}

?>