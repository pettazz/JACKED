<?php
    $JACKED = new JACKED(array('admin', 'Syrup'));

    if(!$JACKED->admin->checkLogin()){
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }

    $dateCriteria = false;
    $filterCriteria = false;
    $criteria = array();
    if(isset($_GET['dateRange'])){
        $parts = array_map('trim', explode('-', $_GET['dateRange']));
        $startDate = strtotime($parts[0] . ' midnight');
        $endDate = strtotime($parts[1] . ' 11:59:59 PM');

        $dateCriteria['AND'] = array('timestamp >= ?' => $startDate, 'timestamp <= ?' => $endDate);
    }
    if(isset($_GET['filter'])){
        $filterValue = trim($_GET['filter']);
        $filterCriteria['OR'] = array(
            'User.email LIKE ?' => '*' . $filterValue . '*',
            'User.first_name LIKE ?' => '*' . $filterValue . '*',
            'User.last_name LIKE ?' => '*' . $filterValue . '*',
            'ShippingAddress.recipient_name LIKE ?' => '*' . $filterValue . '*',
            'ShippingAddress.line1 LIKE ?' => '*' . $filterValue . '*',
            'ShippingAddress.line2 LIKE ?' => '*' . $filterValue . '*',
            'external_transaction_id LIKE ?' => '*' . $filterValue . '*',
            'tracking LIKE ?' => '*' . $filterValue . '*'
        );
    }

    if($dateCriteria && $filterCriteria){
        $criteria['AND'] = array_merge($dateCriteria, $filterCriteria);
    }else if($dateCriteria){
        $criteria = $dateCriteria;
    }else if($filterCriteria){
        $criteria = $filterCriteria;
    }

    $sales = $JACKED->Syrup->Sale->find($criteria, array('field' => 'timestamp', 'direction' => 'DESC'));

    foreach($sales as $sale){
        $paymentSymbol = ($sale->payment == 'DOGE'? '<strong>Ð</strong>' : '<strong>$</strong>');
        $paymentTotal = ($sale->payment == 'DOGE'? floor($sale->converted_total) : sprintf("%01.2f", ($sale->total / 100.0)));
        if($sale->external_transaction_id){
            if($sale->confirmed){
                $confirmationState = '<span class="label label-success"><i class="icon-white icon-ok-sign"></i></span>';
            }else{
                $confirmationState = '<span class="label label-warning"><i class="icon-white icon-time"></i></span>';
            }
        }else{
            $confirmationState = '<span class="label label-important"><i class="icon-white icon-remove-sign"></i></span>';
        }
        echo '    <tr>';
        echo '            <td> ' . date('m/d/Y', $sale->timestamp) . '</td>';
        echo '            <td> <a href="mailto:' . $sale->User->email . '">' . $sale->User->email . '</a></td>';
        echo '            <td> ' . $sale->quantity . '</td>';
        echo '            <td> ' . $sale->Product->name . '</td>';
        echo '            <td> ' . $confirmationState . ' ' . $paymentSymbol . $paymentTotal . '</td>';
        echo '            <td> <i class="icon-' . ($sale->shipped? 'ok' : 'remove') . '"></i></td>';
        echo '            <td class="actionsrow">
            <div class="btn-group">
              <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
                <i class="icon-tasks icon-white"></i>
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu">
                <li><a data-guid="' . $sale->guid . '" href="#" class="saleDetailsOpen"><i class="icon-eye-open"></i> Show Details</a></li>
                ' . ($sale->Product->tangible && !$sale->shipped? '<li><a href="" class="markShipped" data-guid="' . $sale->guid . '"><i class="icon-plane"></i> Mark as Shipped</a></li>' : '') . '
                <li><a href=""><i class="icon-ban-circle"></i> Cancel/Refund Sale</a></li>
              </ul>
            </div>
        </td>';
        echo '    </tr>';

        echo '    <tr id="detailsRow-' . $sale->guid . '" style="display:none;">';
        echo '      <td colspan="4">';
        echo '
        <dl class="dl-horizontal">
            <dt>JACKED Sale ID</dt>
            <dd class="guid">' . $sale->guid . '</dd>
            
            <dt>User</dt>
            <dd class="user">' . $sale->User->email . '</dd>
            
            <dt>Timestamp</dt>
            <dd class="timestamp">' . date('D m/d/Y H:i:s', $sale->timestamp) . '</dd>
            
            <dt>Product</dt>
            <dd class="product">' . $sale->Product->name . '</dd>
            
            <dt>Quantity</dt>
            <dd class="quantity">' . $sale->quantity . '</dd>
            
            <dt>Total Paid</dt>
            <dd class="total">' . $paymentSymbol . $paymentTotal . '</dd>
            
            <dt>Total Price</dt>
            <dd class="total"> <strong>$</strong>' . sprintf("%01.2f", ($sale->total / 100.0)) . '</dd>
            
            <dt>Payment Confirmation</dt>
            <dd class="confirmed"><i class="icon-' . ($sale->confirmed? 'ok' : 'remove') . '"></i></dd>
            
            <dt>Confirmation Updated</dt>
            <dd>' . ($sale->IPN_timestamp > 0? date('D m/d/Y H:i:s', $sale->IPN_timestamp) : 'N/A') . '</dd>
            
            <dt>Payment Method</dt>
            <dd class="payment">' . $sale->payment . '</dd>
        
            <dt>Shipped</dt>
            <dd class="shipped"><i class="icon-' . ($sale->shipped? 'ok' : 'remove') . '"></i></dd>
            
            <dt>Shipping Tracker</dt>
            <dd class="tracking">' . ($sale->tracking? '<a href="https://www.packagetrackr.com/track/' . $sale->tracking . '" target="_blank">' . $sale->tracking . '</a>' : 'N/A') . '</dd>
            
            <dt>External Transaction ID</dt>
            <dd class="external">' . ($sale->external_transaction_id? $sale->external_transaction_id : 'N/A') . '</dd>';

        if($sale->dimensions){
            foreach(json_decode($sale->dimensions) as $dimKey => $dimValue){
                echo '<dt>' . $dimKey . '</dt>';
                echo '<dd>' . $dimValue . '</dd>';
            }
        }

        echo '
        </dl>
        <a data-guid="' . $sale->guid . '" href="#" class="btn btn-primary saleDetailsClose"><i class="icon-eye-close icon-white"></i> Hide Details</a>
      </td>';
        if($sale->Product->tangible){
            echo '<td colspan="3">';
            echo '
            <dl class="dl-horizontal">
                <dt>Shipping Details</dt>
                <dd><small>' . $sale->User->guid . '</small></dd>

                <dt>Recipient Name</dt>
                <dd>' . $sale->ShippingAddress->recipient_name . '</dd>
                
                <dt>Type</dt>
                <dd>' . $sale->ShippingAddress->type . '</dd>
                
                <dt>Line 1</dt>
                <dd>' . $sale->ShippingAddress->line1 . '</dd>

                <dt>Line 2</dt>
                <dd>' . ($sale->ShippingAddress->line2? $sale->ShippingAddress->line2 : 'N/A') . '</dd>

                <dt>City</dt>
                <dd>' . $sale->ShippingAddress->city . '</dd>

                <dt>State</dt>
                <dd>' . $sale->ShippingAddress->state . '</dd>

                <dt>Postal Code</dt>
                <dd>' . $sale->ShippingAddress->postal_code . '</dd>

                <dt>Country</dt>
                <dd>' . $sale->ShippingAddress->country . '</dd>

                <dt>Phone Number</dt>
                <dd>' . $sale->ShippingAddress->phone . '</dd>

            </dl>
            ';
            echo '</td>';
        }

        echo '    </tr>';
    }

    exit();

?>