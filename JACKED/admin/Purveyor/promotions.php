<?php

    $JACKED->loadDependencies(array('Syrup'));

?>

<style type="text/css">
    input.promotionnameinput{
        display: none;
    }
    input.promotionvalueinput{
        display: none;
    }
    input.promotionactiveinput{
        display: none;
    }
    .controls.input-prepend{
        margin-left: 20px;
    }
</style>


<script type="text/javascript">
    $(document).ready(function(){
        $('button.promodelete').click(function(eo){
            if(confirm('Delete this promotion? All Tickets will also be deleted. This cannot be undone.')){
                $(this).siblings('input[name="action"]').val('delete');
                $(this).parents('form').submit();
            }
        });

        $('button.catedit').click(function(eo){
            $(this).hide();
            $(this).siblings('button.promotionsave').removeClass('hidden');
            $(this).siblings('input[name="action"]').val('edit');
            
            var namerow = $(this).parents('td.actionsrow').siblings('td.namerow');
            namerow.children('input.promotionnameinput').val(namerow.children('span.promotionname').text());
            namerow.children('span.promotionname').hide();
            namerow.children('input.promotionnameinput').show();
            
            var valuerow = $(this).parents('td.actionsrow').siblings('td.valuerow');
            valuerow.children('input.promotionvalueinput').val(valuerow.children('span.promotionvalue').text());
            valuerow.children('span.promotionvalue').hide();
            valuerow.children('input.promotionvalueinput').show();

            var activerow = $(this).parents('td.actionsrow').siblings('td.activerow');
            //activerow.children('input.promotionactiveinput').val(activerow.children('span.promotionactive').text());
            activerow.children('span.promotionactive').hide();
            activerow.children('input.promotionactiveinput').show();

            $(this).siblings('button.promotionsave').click(function(eo){
                var newname = $(this).parents('td.actionsrow').siblings('td.namerow').children('input.promotionnameinput').val();
                var newvalue = $(this).parents('td.actionsrow').siblings('td.valuerow').children('input.promotionvalueinput').val();
                var newactive = $(this).parents('td.actionsrow').siblings('td.activerow').children('input.promotionactiveinput').is(':checked');
                if(newname && newvalue){
                    $(this).siblings('input[name="newname"]').val(newname);
                    $(this).siblings('input[name="newvalue"]').val(newvalue);
                    $(this).siblings('input[name="newactive"]').val(newactive);
                    $(this).parent('form').submit();
                }else{
                    $(this).parents('td.actionsrow').siblings('td.namerow').children('input.promotionnameinput').focus();
                    return false;
                }
            });

            return false;
        });
    });
</script>


<h2>Manage Promotions</h2>

<h3>Add New</h3>
<?php
    if($JACKED->Sessions->check('admin.error.addpromotion')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.addpromotion') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.addpromotion');
    }

    if($JACKED->Sessions->check('admin.success.addpromotion')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.addpromotion') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.addpromotion');
    }

?>
<form class="form-horizontal" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="inputName">Name</label>
            <div class="controls">
                <input type="text" class="input-large" id="inputName" name="inputName" placeholder="Thingy Sale" required="true" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputValue">Value</label>
            <div class="controls input-prepend">
                <span class="add-on">$</span>
                <input type="text" class="input-mini" id="inputValue" name="inputValue" placeholder="1.99" required="true" />
            </div>
        </div>

        <div class="form-actions pull-right span9">
            <input type="hidden" name="manage_handler" value="promotions-add-handler" />
            <button id="savecategory" type="submit" class="btn btn-success pull-right" style="margin-left:10px">Save</button>
        </div>
    </fieldset>
</form>

<h3>Existing Promotions</h3>

<?php
    if($JACKED->Sessions->check('admin.error.editcategory')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.editcategory') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.editcategory');
    }

    if($JACKED->Sessions->check('admin.success.editcategory')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.editcategory') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.editcategory');
    }

?>

<table class="table">
    <thead>
        <tr>
            <th>id</th>
            <th>Name</th>
            <th>Value</th>
            <th>Active</th>
            <th>Tickets</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    
    $promotions = $JACKED->Syrup->Promotion->find();
    foreach($promotions as $promotion){
        $tickets = $JACKED->Syrup->Ticket->find(array('Promotion' => $promotion->guid));
        echo '    <tr>';
        echo '    <td><small>' . $promotion->guid . '<small></td>';
        echo '            <td class="namerow"> <span class="promotionname">' . $promotion->name . '</span> <input type="text" required class="input-large promotionnameinput" /> </td>';
        echo '            <td class="valuerow"> $<span class="promotionvalue">' . ($promotion->value / 100.0) . '</span> <input type="text" required class="input-mini promotionvalueinput" /> </td>';
        echo '            <td class="activerow"> <span class="promotionactive">' . ($promotion->active? 'Yes' : 'No') . '</span> <input type="checkbox" value="True" class="promotionactiveinput" ' . ($promotion->active? 'checked' : '') . ' /> </td>';
        echo '    <td>' . count($tickets) . '</td>';
        echo '            <td class="actionsrow">
        <form method="POST" action="' . $JACKED->admin->config->entry_point . 'module/Purveyor">
            <input type="hidden" name="manage_handler" value="promotions-edit-handler" />
            <input type="hidden" name="action" />
            <input type="hidden" name="newname" />
            <input type="hidden" name="newvalue" />
            <input type="hidden" name="newactive" />
            <input type="hidden" name="guid" value="' . $promotion->guid . '" />
            <button class="btn btn-warning catedit"><i class="icon-edit"></i></button> 
            <button class="btn hidden btn-success promotionsave"><i class="icon-ok"></i></button> 
            <button class="btn btn-danger promodelete"><i class="icon-trash"></i></button>
        </form></td>';
        echo '    </tr>';
    }
?>
    </tbody>
</table>