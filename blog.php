<?php

    require('jacked_conf.php');
    $JACKED = new JACKED("Blag");
    $blog = $JACKED->Blag;

?>
<!DOCTYPE html>
<html>

<head>
</head>

<body>

<?php
	print_r($blog->getPost('123-test'));	
?>

</body>

</html>