<?php

    $JACKED->loadDependencies(array('Syrup'));

?>

<style type="text/css">
    input.ticketactiveinput{
        display: none;
    }
    .controls.input-prepend{
        margin-left: 20px;
    }
</style>


<script type="text/javascript">
    $(document).ready(function(){
        $('button.ticketdelete').click(function(eo){
            if(confirm('Delete this ticket? This cannot be undone.')){
                $(this).siblings('input[name="action"]').val('delete');
                $(this).parents('form').submit();
            }
        });

        $('button.ticketedit').click(function(eo){
            $(this).hide();
            $(this).siblings('button.ticketsave').removeClass('hidden');
            $(this).siblings('input[name="action"]').val('edit');
            
            var activerow = $(this).parents('td.actionsrow').siblings('td.activerow');
            //activerow.children('input.ticketactiveinput').val(activerow.children('span.ticketactive').text());
            activerow.children('span.ticketactive').hide();
            activerow.children('input.ticketactiveinput').show();

            $(this).siblings('button.ticketsave').click(function(eo){
                var newactive = $(this).parents('td.actionsrow').siblings('td.activerow').children('input.ticketactiveinput').is(':checked');
                $(this).siblings('input[name="newactive"]').val(newactive);
                $(this).parent('form').submit();
            });

            return false;
        });
    });
</script>


<h2>Manage Tickets</h2>

<h3>Add New</h3>
<?php
    if($JACKED->Sessions->check('admin.error.addticket')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.addticket') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.addticket');
    }

    if($JACKED->Sessions->check('admin.success.addticket')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.addticket') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.addticket');
    }

?>
<form class="form-horizontal" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="inputEmail">User Email</label>
            <div class="controls input-prepend">
                <span class="add-on">@</span>
                <input type="text" class="input-large" id="inputEmail" name="inputEmail" placeholder="mr@peanut.org" required="true" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputPromotion">Promotion</label>
            <div class="controls">
                <select name="inputPromotion" required="true" id="inputPromotion">
                    <option value="" disabled="disabled"></option>
                    <?php
                        $promos = $JACKED->Syrup->Promotion->find(array('active' => 1));
                        foreach($promos as $promo){
                            echo '<option value="' . $promo->guid . '">' . $promo->name . "</option>\n";
                        }
                    ?>
                </select>
            </div>
        </div>


        <div class="form-actions pull-right span9">
            <input type="hidden" name="manage_handler" value="tickets-add-handler" />
            <button id="savecategory" type="submit" class="btn btn-success pull-right" style="margin-left:10px">Save</button>
        </div>
    </fieldset>
</form>

<h3>Existing Tickets</h3>

<?php
    if($JACKED->Sessions->check('admin.error.editticket')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.editticket') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.editticket');
    }

    if($JACKED->Sessions->check('admin.success.editticket')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.editticket') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.editticket');
    }

?>

<table class="table">
    <thead>
        <tr>
            <th>id</th>
            <th>User</th>
            <th>Promotion</th>
            <th>Valid</th>
            <th>Redeemed</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    
    $tickets = $JACKED->Syrup->Ticket->find();
    foreach($tickets as $ticket){
        echo '    <tr>';
        echo '    <td>' . $ticket->guid . '</td>';
        echo '            <td> <a href="mailto:' . $ticket->User->email . '">' . $ticket->User->email . '</a></td>';
        echo '            <td> ' . $ticket->Promotion->name . '</td>';
        echo '            <td class="activerow"> <span class="ticketactive">' . ($ticket->valid? 'Yes' : 'No') . '</span> <input type="checkbox" value="True" class="ticketactiveinput" ' . ($ticket->valid? 'checked' : '') . ' /> </td>';
        echo '            <td> <i class="icon-star' . ($ticket->redeemed? '' : '-empty') . '"></i></td>';
        echo '            <td class="actionsrow">
        <form method="POST" action="' . $JACKED->admin->config->entry_point . 'module/Purveyor">
            <input type="hidden" name="manage_handler" value="tickets-edit-handler" />
            <input type="hidden" name="action" />
            <input type="hidden" name="newname" />
            <input type="hidden" name="newvalue" />
            <input type="hidden" name="newactive" />
            <input type="hidden" name="guid" value="' . $ticket->guid . '" />
            <button class="btn btn-warning ticketedit"><i class="icon-edit"></i></button> 
            <button class="btn hidden btn-success ticketsave"><i class="icon-ok"></i></button> 
            <button class="btn btn-danger ticketdelete"><i class="icon-trash"></i></button>
        </form></td>';
        echo '    </tr>';
    }
?>
    </tbody>
</table>