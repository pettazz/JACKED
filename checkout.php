<?php
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 17 Jan 1998 05:00:00 GMT");

    require('jacked_conf.php');
    $JACKED = new JACKED('Purveyor', 'Flock', 'Syrup');

    try{
        $product = $_POST['product'];
        $quantity = $_POST['quantity'];
        $tickets = $_POST['tickets'];
        $method = $_POST['payment'];
        $email = $_POST['email'];

        if($tickets){
            $tickets = explode($tickets, ",");
        }

        try{
            $user = $JACKED->Flock->getUser($email);
            $userid = $user['guid'];
        }catch(UserNotFoundException $e){
            $newUser = $JACKED->Syrup->User->create();
            $newUser->email = $email;
            $newUser->save();
            $userid = $newUser->guid;
        }

        $result = $JACKED->Purveyor->createSale($userid, $product, $quantity, $method, $JACKED->config->base_url . 'JACKED/tomhanks.php', 'LOL, ETC.', $tickets);

        header('Location: ' . $result['url']);

    }catch(Exception $e){
        echo '<h1>A Fuck Happened.</h1>';
        echo '<h4>look at it:</h4>';
        echo '<font color="red">' . $e->getMessage() . '</font>';
        echo '<pre>' . print_r($e->getTrace(), true) . '</pre>';
        exit(1);
    }
?>