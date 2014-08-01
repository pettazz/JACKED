<?php
     
    require('../../jacked_conf.php');
    $JACKED = new JACKED(array('Syrup', 'Purveyor'));

    if((isset($_GET['apiSecret']) &&
         isset($_GET['guid']) && 
         isset($_GET['status']) && 
         isset($_GET['timestamp']) && 
         isset($_GET['tx'])
        )){

        try{

            if($_GET['apiSecret'] !== $JACKED->Purveyor->config->moolah_api_key_secret){
                header('HTTP/1.1 401 Unauthorized');
                exit();
            }

            $JACKED->Purveyor->updatePaymentStatus($_GET['status'], $_GET['timestamp'], $_GET['guid']);

        }catch(Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            echo $e->getMessage();
            error_log('JACKED IPN Handler error: ' . $e->getMessage());
            exit();
        }
        header('HTTP/1.1 200 OK');
    }else{
        header('HTTP/1.1 400 Bad Request');
    }
?>