<?php

    class Purveyor extends JACKEDModule{
        /*
            Sell stuff
        */
    
        const moduleName = 'Purveyor';
        const moduleVersion = 1.0;
        public static $dependencies = array('Syrup', 'Flock');

        public function __construct($JACKED){
            JACKEDModule::__construct($JACKED);

            $JACKED->loadLibrary('Mindrill');
            $this->mailer = new Mindrill($JACKED->config->apikey_mandrill);
        }
        
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

        /**
        * Send an email through Mandrill. Simple wrapper for Mindrill.
        * https://mandrillapp.com/api/docs/messages.JSON.html
        * 
        * @param $toEmail String Email address to send mail to
        * @param $toName String Name to send mail to
        * @param $fromEmail String Email address to send mail from
        * @param $fromName String Name to send mail from
        * @param $subject String Email subject
        * @param $html String HTML content of email
        * @param $text String Plaintext content of email. If NULL, $html is stripped and used
        * @param $params Array Any additional params to add to the Mandrill request
        * @return Boolean Whether the mail was successfully sent
        */
        private function sendMail($toEmail, $toName, $fromEmail, $fromName, $subject, $html, $text = NULL, $params = array()){
            if(!$text){
                $text = strip_tags(preg_replace('#<br\s*/?>#i', "\n", $html));
            }
            $baseParams = array(
                "message" => array(
                    "html" => $html,
                    "text" => $text,
                    "subject" => $subject,
                    "from_email" => $fromEmail,
                    "from_name" => $fromName,
                    "to" => array(
                        array(
                            "email" => $toEmail,
                            "name" => $toName,
                            "type" => "to"
                        )
                    ),
                    "headers" => array(
                        "Reply-To" => $JACKED->config->default_reply_email
                    ),
                    "important" => false,
                    "track_opens" => null,
                    "track_clicks" => null,
                    "view_content_link" => false
                )
            );

            if($params){
                $params = array_merge_recursive($baseParams, $params);
            }else{
                $params = $baseParams;
            }

            $this->mailer->call('/messages/send.json', $params);
        }

    }

?>