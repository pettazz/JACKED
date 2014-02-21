<?php

    include JACKED_LIB_ROOT . 'paypal-vendor/autoload.php';

    use PayPal\Api\Amount;
    use PayPal\Rest\ApiContext;
    use PayPal\Api\Details;
    use PayPal\Api\Item;
    use PayPal\Api\ItemList;
    use PayPal\Auth\OAuthTokenCredential;
    use PayPal\Api\Payer;
    use PayPal\Api\Payment;
    use PayPal\Api\RedirectUrls;
    use PayPal\Api\Transaction;

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

            $this->paypalAPIContext = new ApiContext(
                new OAuthTokenCredential(
                    $this->config->paypal_client_id,
                    $this->config->paypal_client_secret
                )
            );

            $this->paypalAPIContext->setConfig(array(
                'mode' => 'sandbox'
            ));
        }

        /**
        * Create a new Sale internally and begin the transaction with the required API
        * 
        * @param $user String GUID of the Flock User who placed this sale
        * @param $product String GUID of the Product sold
        * @param $total int Total amount of this sale in Ð or USD cents
        * @param $quantity int Total number of Products in this sale
        * @param $method String Payment method for this sale. One of: DOGE, PAYPAL
        * @param $redirectURL String URL to redirect the user to after payment. GET args are appended:
                                success (Boolean payment authorized), guid (string Sale GUID on success=true)
        * @param $description String Description of payment (used only for PayPal)
        * @param $ticket String GUID of a Ticket if used
        * @return Array Sale => Sale model object, url => authorization redirect URL
        */
        public function createSale($user, $product, $total, $quantity, $method, $redirectURL, $description = NULL, $ticket = NULL){
            if(!($method == 'DOGE' || $method == 'PAYPAL')){
                throw new Exception('Unsupported Payment method.');
            }

            $product = $this->JACKED->Syrup->Product->findOne(array('guid' => $product));
            if(!$product){
                throw new Exception('Product not found');
            }

            $sale = $this->JACKED->Syrup->Sale->create();

            $sale->User = $user;
            $sale->Product = $product;
            if($ticket){
                $sale->Ticket = $ticket;
            }
            $sale->timestamp = time();
            $sale->total = $total;
            $sale->quantity = $quantity;
            $sale->payment = $method;
            $sale->save();

            if($method == 'PAYPAL'){
                $payer = new Payer();
                $payer->setPaymentMethod("paypal");

                $item = new Item();
                $item->setName($product->name)
                    ->setCurrency('USD')
                    ->setQuantity($quantity)
                    ->setPrice($product->cost / 100.0);
                $itemList = new ItemList();
                $itemList->setItems(array($item));

                // $details = new Details();
                // $details->setShipping('1.20')
                //     ->setTax('1.30')
                //     ->setSubtotal('17.50');

                $amount = new Amount();
                $amount->setCurrency("USD")
                    ->setTotal($total / 100.0);
                    // ->setDetails($details);

                $transaction = new Transaction();
                $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription($description);

                $redirectUrls = new RedirectUrls();
                $redirectUrls->setReturnUrl("$redirectURL?success=true&guid=" . $sale->guid)
                    ->setCancelUrl("$redirectURL?success=false");

                $payment = new Payment();
                $payment->setIntent("sale")
                    ->setPayer($payer)
                    ->setRedirectUrls($redirectUrls)
                    ->setTransactions(array($transaction));

                try{
                    $payment->create($this->paypalAPIContext);
                }catch(Exception $ex){
                    echo $ex->getData();
                }

                foreach($payment->getLinks() as $link){
                    if($link->getRel() == 'approval_url'){
                        $redirectUrl = $link->getHref();
                        break;
                    }
                }

                $saleobj = $this->JACKED->Syrup->Sale->findOne(array('guid' => $sale->guid));
                $saleobj->external_transaction_id = $payment->getId();
                $saleobj->save();

            }else{
                $params = array(
                    'currency' => 'DOGE',
                    'guid' => $this->config->moolah_guid,
                    'amount' => $total,
                    'product' => $product->name,
                    'ipn' => $this->config->ipn_secret,
                    'return' => "$redirectURL?success=true&guid=" . $sale->guid
                );

                $url = 'https://moolah.ch/api/pay?' . http_build_query($params);
                echo $url;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);

                $result = curl_exec($ch);
                curl_close($ch);
                $decoded = json_decode($result);

                if(is_null($decoded)){
                    throw new Exception('Moolah payment was not authorized successfully');
                }

                $saleobj = $this->JACKED->Syrup->Sale->findOne(array('guid' => $sale->guid));
                $saleobj->external_transaction_id = $decoded->tx;
                $saleobj->save();

                $redirectUrl = $decoded->url;
            }

            return array(
                'Sale' => $sale,
                'url' => $redirectUrl
            );
        }
        
        /**
        * Update a Sale's pending payment status, useful for IPN
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
                        "Reply-To" => $this->JACKED->config->default_reply_email
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