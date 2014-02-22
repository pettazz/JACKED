<?php

    require('jacked_conf.php');
    $JACKED = new JACKED('Purveyor', 'Syrup');

    $sale = $JACKED->Syrup->Sale->findOne(array('guid' => $_GET['guid']));
    if(!$sale){
        throw new Exception('Purveyor Sale ID not found. This redirect is highly suspect at best.');
    }

    if($sale->payment == 'PAYPAL'){

        $tid = $sale->external_transaction_id;

        try{

            $JACKED->Purveyor->executePayPalPayment($tid, $_GET['PayerID']);

            echo '<h1>We did it reddit!</h1>';
            echo '<p>Your payment has been accepted. You will receive an email receipt shortly.</p>';
        }catch(Exception $e){
            echo '<h1>A Fuck Happened.</h1>';
            echo '<h4>look at it:</h4>';
            echo '<font color="red">' . $e->getMessage() . '</font>';
            echo '<pre>' . print_r($e->getTrace(), true) . '</pre>';
            exit(1);
        }
    }else{
        echo '<h1>good shibe</h1>';
        echo '<p>Once your transaction has reached at least 4 confirmations, your payment will be approved and you will receive an email notification.</p>';
    }


?>