<?php

    class Purveyor extends JACKEDModule{
        /*
            Sell stuff
        */
    
        const moduleName = 'Purveyor';
        const moduleVersion = 1.0;
        public static $dependencies = array('Syrup', 'Flock');
        
        /**
        * Update a pending payment's status, usually called via IPN
        * Moolah: https://crypto.zendesk.com/hc/en-gb/articles/200282041-How-do-I-create-a-transaction-
        * 
        * @param $status String New status of the transaction. One of: cancelled|pending|created|complete|part_paid
        * @param $timestamp int Timestamp of this update
        * @param $tx String External Transaction ID of the payment
        * @return Boolean Whether the Payment status update has been accepted
        */
        public function updatePaymentStatus($status, $timestamp, $tx){
            $sale = $this->JACKED->Syrup->Sale->find(array('external_transaction_id' => $tx));

            if(!$sale){
                throw new Exception('No Sale with the given External Transaction ID was found.');
            }

            $sale = $sale[0];

            if($sale->IPN_timestamp > $timestamp){
                throw new Exception('This Sale has already received a more recent IPN update.');
            }

            if($status == 'complete'){
                $sale->confirmed = 1;
            }
            $sale->IPN_timestamp = $timestamp;

            $sale->save();

            return True;
        }


    }

?>