<?php

    require('jacked_conf.php');
    $JACKED = new JACKED(array("Blag", "EYS", "Syrup"));
    $blog = $JACKED->Blag;
    $eys = $JACKED->EYS;

?>
<!DOCTYPE html>
<html>

<head>
</head>

<body>
	<?php
		echo '<h2>This is ' . $blog::getModuleName() . ' version ' . $blog::getModuleVersion() . '</h2>';

		$eys->setMark('getposts');

        $posts = $JACKED->Syrup->Blag->find(array('alive' => True));
        if(!$posts){
            echo '<h2>Ouch</h2>';
        }
		$eys->setMark('getposts_end');

		foreach($posts as $num => $post){
			echo '<h1>' . $post->title . '</h1>';
			echo '<h2>' . $post->headline . '</h2>';
			echo '<h4>posted by <em>' . $post->author->first_name . ' ' . $post->author->last_name . '</em> on <em>' . date('r', $post->posted) . '</em></h4>';
            echo '<h4>posted in <em>' . $post->category->name . '</em></h4>';
			echo '<p>' . $post->content . '</p>';
            if(property_exists($post, 'Curator') && is_array($post->Curator) && !empty($post->Curator)){
                echo "<h4>tagged as: ";
                $tagstrings = array();
                foreach($post->Curator as $tag){
                    $tagstrings[] = $tag->name . "(" . $tag->usage . ")";
                }
                echo implode(", ", $tagstrings);
                echo "</h4>";
            }else{
                echo "<h4>no tags</h4>";
            }
			echo '<small>' . $post->guid . '</small>';
		}
		$timer = $eys->getDelta('getposts', 'getposts_end');
	?>
	<h4><?php echo 'took: ' . round(($timer['time'] * 1000), 4) . 'ms and increased allocated memory by ' . $timer['memory'] / pow(1024, 2) . ' MB'; ?></h4>
    
</body>

</html>