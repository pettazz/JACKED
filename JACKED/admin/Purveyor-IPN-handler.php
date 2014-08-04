<?php
     
    require('../../jacked_conf.php');
    $JACKED = new JACKED(array('Syrup', 'Purveyor'));

    if((isset($_POST['apiSecret']) &&
         isset($_POST['guid']) && 
         isset($_POST['status']) && 
         isset($_POST['timestamp']) && 
         isset($_POST['tx'])
        )){

        try{

            if($_POST['apiSecret'] !== $JACKED->Purveyor->config->moolah_api_key_secret){
                header('HTTP/1.1 401 Unauthorized');
                exit();
            }

            $JACKED->Purveyor->updatePaymentStatus($_POST['status'], $_POST['timestamp'], $_POST['guid']);

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