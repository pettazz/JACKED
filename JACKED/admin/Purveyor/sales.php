<?php

    $JACKED->loadDependencies(array('Syrup'));

?>

<script type="text/javascript">
    
    $(document).ready(function(){
        $(".saleDetailsOpen").click(function(eo){
            eo.preventDefault();
            $('#detailsRow-' + $(this).attr('data-guid')).show();
        });

        $(".saleDetailsClose").click(function(eo){
            eo.preventDefault();
            $('#detailsRow-' + $(this).attr('data-guid')).hide();
        });

        $('#collectTrackingModal').on('hidden', function(){
            $("form#shippingUpdate input#saleGuid").val('');
        })

        $(".markShipped").click(function(eo){
            eo.preventDefault();
            $("form#shippingUpdate input#saleGuid").val($(this).attr('data-guid'));
            $('#collectTrackingModal').modal({
                keyboard: true
            });
        });
    });
</script>


<!-- tracking info collection modal -->
<div id="collectTrackingModal" class="modal hide fade">
    <form id="shippingUpdate" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>Mark as Shipped</h3>
        </div>

        <div class="modal-body">
            <p>Marking this sale as shipped will complete the transaction.</p>
            
            <input type="hidden" name="manage_handler" value="sales-edit-handler" />
            <input type="hidden" name="saleGuid" id="saleGuid" value="" />
            <div class="control-group">
                <label class="control-label" for="inputTracking">Tracking Number (FedEx, UPS, etc) if available</label>
                <div class="controls">
                    <input type="text" class="input-large" id="inputTracking" name="inputTracking" />
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            <button type="submit" class="btn btn-primary">Save as Shipped</button>
        </div>
    </form>
</div>
<!-- end tracking info modal -->


<h3>Sales</h3>

<?php
    if($JACKED->Sessions->check('admin.error.editsale')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.editsale') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.editsale');
    }

    if($JACKED->Sessions->check('admin.success.editsale')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.editsale') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.editsale');
    }

?>

<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>User</th>
            <th>Qty</th>
            <th>Product</th>
            <th>Paid</th>
            <th>Shipped</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    
    $sales = $JACKED->Syrup->Sale->find();
    foreach($sales as $sale){
        $paymentSymbol = ($sale->payment == 'DOGE'? '<strong>Ð</strong>' : '<strong>$</strong>');
        $paymentTotal = ($sale->payment == 'DOGE'? floor($sale->total) : sprintf("%01.2f", ($sale->total / 100.0)));
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
        echo '      <td colspan="7">';
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
            
            <dt>Total</dt>
            <dd class="total">' . $paymentSymbol . $paymentTotal . '</dd>
            
            <dt>Payment Confirmation</dt>
            <dd class="confirmed"><i class="icon-' . ($sale->confirmed? 'ok' : 'remove') . '"></i></dd>
            
            <dt>Confirmation Updated</dt>
            <dd>' . ($sale->IPN_timestamp > 0? date('D m/d/Y H:i:s', $sale->IPN_timestamp) : 'N/A') . '</dd>
            
            <dt>Payment Method</dt>
            <dd class="payment">' . $sale->payment . '</dd>
            
            <dt>Shipped</dt>
            <dd class="shipped"><i class="icon-' . ($sale->shipped? 'ok' : 'remove') . '"></i></dd>
            
            <dt>Shipping Tracker</dt>
            <dd class="tracking">' . ($sale->tracking? $sale->tracking : 'N/A') . '</dd>
            
            <dt>External Transaction ID</dt>
            <dd class="external">' . ($sale->external_transaction_id? $sale->external_transaction_id : 'N/A') . '</dd>

        </dl>
        <a data-guid="' . $sale->guid . '" href="#" class="btn btn-primary saleDetailsClose"><i class="icon-eye-close icon-white"></i> Hide Details</a>';
        echo '      </td>';
        echo '    </tr>';
    }
?>
    </tbody>
</table>