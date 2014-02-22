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
                    <option value="' . $product->guid . '">' . $product->name . '</option>';
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

            <button type="submit">PAY ME</button>
        </form>

    </body>
</body>