<?php
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Sat, 17 Jan 1998 05:00:00 GMT");

    require('jacked_conf.php');
    $JACKED = new JACKED('Purveyor', 'Syrup');

    try{
        $product = $_POST['product'];
        $quantity = $_POST['quantity'];
        $tickets = $_POST['tickets'];
        $method = $_POST['payment'];
        $email = $_POST['email'];

        if($tickets){
            $tickets = explode(",", $tickets);
        }

        $user = $JACKED->Syrup->User->findOne(array('email' => $email));
        if(!$user){
            $user = $JACKED->Syrup->User->create();
            $user->email = $email;
            $user->save();    
        }

        $productobj = $JACKED->Syrup->Product->findOne(array('guid' => $product));
        if(!$productobj){
            throw new Exception('Product not found');
        }

        if($productobj->tangible){
            $recipient_name = $_POST['recipient_name'];
            $line1 = $_POST['line1'];
            $line2 = $_POST['line2'];
            $city = $_POST['city'];
            $postal_code = $_POST['postal_code'];
            $state = $_POST['state'];
            $phone = $_POST['phone'];

            $newAddr = $JACKED->Syrup->ShippingAddress->create();

            $newAddr->User = $user->guid;
            $newAddr->recipient_name = $recipient_name;
            $newAddr->line1 = $line1;
            $newAddr->line2 = $line2;
            $newAddr->city = $city;
            $newAddr->postal_code = $postal_code;
            $newAddr->state = $state;
            $newAddr->phone = $phone;

            $newAddr->save();

            $shippingAddr = $newAddr->guid;
        }else{
            $shippingAddr = NULL;
        }

        $result = $JACKED->Purveyor->createSale($user->guid, $product, $quantity, $method, $JACKED->config->base_url . 'JACKED/tomhanks.php', $shippingAddr, 'LOL, ETC.', $tickets);

        header('Location: ' . $result['url']);

    }catch(Exception $e){
        echo '<h1>A Fuck Happened.</h1>';
        echo '<h4>look at it:</h4>';
        echo '<font color="red">' . $e->getMessage() . '</font>';
        echo '<pre>' . print_r($e->getTrace(), true) . '</pre>';
        exit(1);
    }
?>