<?php
     
    require('../../jacked_conf.php');
    $JACKED = new JACKED(array('Syrup', 'Purveyor'));

    if((isset($_GET['ipn_secret']) &&
         isset($_GET['status']) && 
         isset($_GET['timestamp']) && 
         isset($_GET['tx'])
        )){

        try{

            if($_GET['ipn_secret'] !== $JACKED->Purveyor->config->ipn_secret){
                header('HTTP/1.1 401 Unauthorized');
                exit();
            }

            $JACKED->Purveyor->updatePaymentStatus($_GET['status'], $_GET['timestamp'], $_GET['tx']);

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