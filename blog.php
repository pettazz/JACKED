<?php

    require('jacked_conf.php');
    $JACKED = new JACKED(array("Blag", "EYS"));
    $blog = $JACKED->Blag;
    $eys = $JACKED->EYS;

?>
<!DOCTYPE html>
<html>

<head>
</head>

<body>
	<pre><code>
	<?php
		$eys->setMark('getposts');
		$posts = $blog->getPosts();
		$eys->setMark('getposts_end');
		foreach($posts as $num => $post){
			echo '<h1>' . $post['title'] . '</h1>';
			echo '<h2>' . $post['headline'] . '</h2>';
			echo '<h4>posted by <em>' $post['first_name'] . ' ' . $post['last_name'] '</em> on <em>' . date('r', $post['posted']) . '</em></h4>';
			echo '<p>' . $post['content'] . '</p>';
			echo '<small>' . $post['guid'] . '</small>';
		}
		$timer = $eys->getDelta('getposts', 'getposts_end');
	?>
	</code></pre>
	<h4><?php echo 'took: ' . round(($timer['time'] * 1000), 4) . 'ms and increased allocated memory by ' . round($timer['memory'] / 2048, 4) . ' MB'; ?></h4>
</body>

</html>