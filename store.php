<?php

    require('jacked_conf.php');
    $JACKED = new JACKED('Purveyor');

    $products = $JACKED->Syrup->Product->find(array('active' => 1));

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Buying Stuff: a demo</title>

        <script src="http://code.jquery.com/jquery-1.10.2.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                $("#inputProduct").change(function(){
                    if($("#inputProduct option:selected").attr('data-tangible') == 'true'){
                        $("#shippingAddress").show();
                    }else{
                        $("#shippingAddress").hide();
                    }
                });
            });
        </script>
    </head>
    <body>

        <h1>Buy A Thing</h1>

        <form action="checkout.php" method="POST">
            <p>
                <label for="inputProduct">Choose a Thing</label><br />
                <select id="inputProduct" name="product" required="true">
                    <option value="" disabled="disabled"></option>
                    <?php
                        foreach($products as $product){
                            echo '
                    <option data-tangible="' . ($product->tangible? 'true' : 'false') . '" value="' . $product->guid . '">' . $product->name . ' - $' . sprintf("%01.2f", ($product->cost / 100.0)) . '</option>';
                        }
                    ?>
                </select>
            </p>

            <p>
                <label for="inputEmail">Your Email</label><br />
                <input type="text" required="true" id="inputEmail" name="email" placeholder="please.spam@me.com" />
            </p>

            <p>
                <label for="inputQuantity">Quantity</label><br />
                <input type="text" required="true" id="inputQuantity" name="quantity" placeholder="1000000" />
            </p>

            <p>
                <label for="inputTickets">Tickets comma separated (optional)</label><br />
                <textarea id="inputTickets" name="tickets" rows="5" cols="40"></textarea>
            </p>

            <p>
                <label for="inputPayment">Pay with:</label><br />
                <select id="inputPayment" name="payment">
                    <option value="PAYPAL">PayPal Account or Credit Card</option>
                    <option value="DOGE">Such ÐOGE</option>
                </select>
            </p>

            <div id="shippingAddress" style="display: none;">
                <h3>Shipping Address</h3>
                <p>
                    <label for="inputName">Recipient Name</label><br />
                    <input type="text" id="inputName" name="recipient_name" placeholder="William Pendleton III" />
                </p>                
                
                <p>
                    <label for="inputLine1">Address Line 1</label><br />
                    <input type="text" id="inputLine1" name="line1" placeholder="123 Fake St" />
                </p>                
                
                <p>
                    <label for="inputLine2">Address Line 2</label><br />
                    <input type="text" id="inputLine2" name="line2" placeholder="Apt #47" />
                </p>                
                
                <p>
                    <label for="inputCity">City</label><br />
                    <input type="text" id="inputCity" name="city" placeholder="Steveton" />
                </p>                

                <p>
                    <label for="inputPostalCode">Zip Code</label><br />
                    <input type="text" id="inputPostalCode" name="postal_code" placeholder="20102" />
                </p>

                <p>
                    <label for="inputState">State</label><br />
                    <input type="text" id="inputState" name="state" placeholder="PS" />
                </p>

                <p>
                    <label for="inputPhone">Phone Number</label><br />
                    <input type="text" id="inputPhone" name="phone" placeholder="4135962244" />
                </p>

            </div>

            <button type="submit">PAY ME</button>
        </form>

    </body>
</body>