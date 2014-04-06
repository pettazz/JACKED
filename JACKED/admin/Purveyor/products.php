<?php

    $JACKED->loadDependencies(array('Syrup'));

?>

<style type="text/css">
    input.productnameinput{
        display: none;
    }
    input.productcostinput{
        display: none;
    }
    input.producttangibleinput{
        display: none;
    }
    .controls.input-prepend{
        margin-left: 20px;
    }
</style>


<script type="text/javascript">
    $(document).ready(function(){
        $('button.catdelete').click(function(eo){
            $(this).siblings('input[name="action"]').val('delete');
            $(this).parents('form').submit();
        });

        $('button.catedit').click(function(eo){
            $(this).hide();
            $(this).siblings('button.productsave').removeClass('hidden');
            $(this).siblings('input[name="action"]').val('edit');
            
            var namerow = $(this).parents('td.actionsrow').siblings('td.namerow');
            namerow.children('input.productnameinput').val(namerow.children('span.productname').text());
            namerow.children('span.productname').hide();
            namerow.children('input.productnameinput').show();
            
            var costrow = $(this).parents('td.actionsrow').siblings('td.costrow');
            costrow.children('input.productcostinput').val(costrow.children('span.productcost').text());
            costrow.children('span.productcost').hide();
            costrow.children('input.productcostinput').show();

            var tangiblerow = $(this).parents('td.actionsrow').siblings('td.tangiblerow');
            //tangiblerow.children('input.producttangibleinput').val(tangiblerow.children('span.producttangible').text());
            tangiblerow.children('span.producttangible').hide();
            tangiblerow.children('input.producttangibleinput').show();

            $(this).siblings('button.productsave').click(function(eo){
                var newname = $(this).parents('td.actionsrow').siblings('td.namerow').children('input.productnameinput').val();
                var newcost = $(this).parents('td.actionsrow').siblings('td.costrow').children('input.productcostinput').val();
                var newtangible = $(this).parents('td.actionsrow').siblings('td.tangiblerow').children('input.producttangibleinput').is(':checked');
                if(newname && newcost){
                    $(this).siblings('input[name="newname"]').val(newname);
                    $(this).siblings('input[name="newcost"]').val(newcost);
                    $(this).siblings('input[name="newtangible"]').val(newtangible);
                    $(this).parent('form').submit();
                }else{
                    $(this).parents('td.actionsrow').siblings('td.namerow').children('input.productnameinput').focus();
                    return false;
                }
            });

            return false;
        });
    });
</script>


<h2>Manage Products</h2>

<h3>Add New</h3>
<?php
    if($JACKED->Sessions->check('admin.error.addproduct')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.addproduct') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.addproduct');
    }

    if($JACKED->Sessions->check('admin.success.addproduct')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.addproduct') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.addproduct');
    }

?>
<form class="form-horizontal" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="inputName">Name</label>
            <div class="controls">
                <input type="text" class="input-large" id="inputName" name="inputName" placeholder="Thingy" required="true" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputCost">Cost</label>
            <div class="controls input-prepend">
                <span class="add-on">$</span>
                <input type="text" class="input-mini" id="inputCost" name="inputCost" placeholder="29.99" required="true" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputTangible">Tangible</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox" id="inputTangible" name="inputTangible" value="True" /> This product physically exists and requires shipping.
                </label>
            </div>
        </div>

        <div class="form-actions pull-right span9">
            <input type="hidden" name="manage_handler" value="products-add-handler" />
            <button id="savecategory" type="submit" class="btn btn-success pull-right" style="margin-left:10px">Save</button>
        </div>
    </fieldset>
</form>

<h3>Existing Products</h3>

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
            <th>Cost</th>
            <th>Tangible</th>
            <th>Quantity Sold</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    
    $products = $JACKED->Syrup->Product->find();
    foreach($products as $product){
        $sales = $JACKED->Syrup->Sale->find(array('AND' => array('Product' => $product->guid, 'confirmed' => 1)));
        $quantity = 0;
        if($sales){
            foreach($sales as $sale){
                $quantity += $sale->quantity;
            }
        }
        echo '    <tr>';
        echo '    <td><small>' . $product->guid . '<small></td>';
        echo '            <td class="namerow"> <span class="productname">' . $product->name . '</span> <input type="text" required class="input-large productnameinput" /> </td>';
        echo '            <td class="costrow"> $<span class="productcost">' . ($product->cost / 100.0) . '</span> <input type="text" required class="input-mini productcostinput" /> </td>';
        echo '            <td class="tangiblerow"> <span class="producttangible">' . ($product->tangible? 'Yes' : 'No') . '</span> <input type="checkbox" value="True" class="producttangibleinput" ' . ($product->tangible? 'checked' : '') . ' /> </td>';
        echo '            <td class="salesrow"> <span class="productsales">' . $quantity . '</span> <input type="text" required class="input-large productnameinput" /> </td>';
        echo '            <td class="actionsrow">
        <form method="POST" action="' . $JACKED->admin->config->entry_point . 'module/Purveyor">
            <input type="hidden" name="manage_handler" value="products-edit-handler" />
            <input type="hidden" name="action" />
            <input type="hidden" name="newname" />
            <input type="hidden" name="newcost" />
            <input type="hidden" name="newtangible" />
            <input type="hidden" name="guid" value="' . $product->guid . '" />
            <button class="btn btn-warning catedit"><i class="icon-edit"></i></button> 
            <button class="btn hidden btn-success productsave"><i class="icon-ok"></i></button> 
            <button class="btn btn-danger catdelete"><i class="icon-trash"></i></button>
        </form></td>';
        echo '    </tr>';
    }
?>
    </tbody>
</table>