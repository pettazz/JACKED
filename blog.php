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
		$posts = $blog->getPost('123-test');
		$eys->setMark('getposts_end');
		print_r($posts);	
		$timer = $eys->getDelta('getposts', 'getposts_end');
	?>
	</code></pre>
	<h4><?php echo 'took: ' . round(($timer['time'] * 1000), 4) . 'ms and increased allocated memory by ' . round($timer['memory'] / 2048, 4) . ' MB'; ?></h4>
</body>

</html>