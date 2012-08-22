<?php

    require('jacked_conf.php');
    $JACKED = new JACKED(array("Blag", "Karma", "EYS", "Syrup"));
    $blog = $JACKED->Blag;
    $eys = $JACKED->EYS;
    $karma = $JACKED->Karma;

?>
<!DOCTYPE html>
<html>

<head>
</head>

<body>
	<?php
		echo '<h2>This is ' . $blog::getModuleName() . ' version ' . $blog::getModuleVersion() . '</h2>';

		$eys->setMark('getposts');
        $posts = $JACKED->Syrup->Blag->find(array('alive' => 1));
		//$posts = $blog->getPosts();
		$eys->setMark('getposts_end');
		foreach($posts as $num => $post){
			echo '<h1>' . $post['title'] . '</h1>';
			echo '<h2>' . $post['headline'] . '</h2>';
			echo '<h4>posted by <em>' . $post['first_name'] . ' ' . $post['last_name'] . '</em> on <em>' . date('r', $post['posted']) . '</em></h4>';
			echo '<p>' . $post['content'] . '</p>';
			echo '<h4>' . $karma->getScore($post['guid']) . 'points (' . $karma->getUpvotes($post['guid']) . ' up; ' . $karma->getDownvotes($post['guid']) . ' down)</h4>';
			echo '<small>' . $post['guid'] . '</small>';
		}
		$timer = $eys->getDelta('getposts', 'getposts_end');
	?>
	<h4><?php echo 'took: ' . round(($timer['time'] * 1000), 4) . 'ms and increased allocated memory by ' . round($timer['memory'] / 2048, 4) . ' MB'; ?></h4>
</body>

</html>