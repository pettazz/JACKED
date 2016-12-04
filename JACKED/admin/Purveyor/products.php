<?php

    $JACKED->loadDependencies(array('Syrup'));

?>

<style type="text/css">
    input.productnameinput{
        display: none;
    }
    textarea.productdescriptioninput{
        display: none;
    }
    button.productimageinputbutton{
        display: none;
    }
    input.productimageinput{
        display: none;
    }
    input.productcostinput{
        display: none;
    }
    input.producttangibleinput{
        display: none;
    }
    .controls.input-prepend, .controls.input-append{
        margin-left: 20px;
    }
    .productImageThumb{
        max-width: 100px;
    }
    #inputDescription{
        width: 400px;
    }
</style>


<script type="text/javascript">
    $(document).ready(function(){
        $('button.catdelete').click(function(eo){
            $(this).siblings('input[name="action"]').val('delete');
            $(this).parents('form').submit();
        });

        $('button.catedit').click(function(eo){
            // GUESS I WROTE THIS BEFORE I KNEW ABOUT DATA ATTRS HUH, UGH
            $(this).hide();
            $(this).siblings('button.productsave').removeClass('hidden');
            $(this).siblings('input[name="action"]').val('edit');
            
            var imagerow = $(this).parents('td.actionsrow').siblings('td.imagerow');
            imagerow.find('input.productimageinput').val(imagerow.find('.productImageThumb').attr('src'));
            imagerow.find('span.productimage').hide();
            imagerow.find('input.productimageinput').show();
            imagerow.find('button.productimageinputbutton').show();
            
            var namerow = $(this).parents('td.actionsrow').siblings('td.namerow');
            namerow.children('input.productnameinput').val(namerow.children('span.productname').text());
            namerow.children('span.productname').hide();
            namerow.children('input.productnameinput').show();
            
            var descriptionrow = $(this).parents('td.actionsrow').siblings('td.descriptionrow');
            descriptionrow.children('textarea.productdescriptioninput').val(descriptionrow.children('span.productdescription').text());
            descriptionrow.children('span.productdescription').hide();
            descriptionrow.children('textarea.productdescriptioninput').show();
            
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
                var newimage = $(this).parents('td.actionsrow').siblings('td.imagerow').find('input.productimageinput').val();
                var newdescription = $(this).parents('td.actionsrow').siblings('td.descriptionrow').children('textarea.productdescriptioninput').val();
                var newcost = $(this).parents('td.actionsrow').siblings('td.costrow').children('input.productcostinput').val();
                var newtangible = $(this).parents('td.actionsrow').siblings('td.tangiblerow').children('input.producttangibleinput').is(':checked');
                if(newname && newimage && newdescription && newcost){
                    $(this).siblings('input[name="newname"]').val(newname);
                    $(this).siblings('input[name="newimage"]').val(newimage);
                    $(this).siblings('input[name="newdescription"]').val(newdescription);
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
            <label class="control-label" for="inputDescription">Description</label>
            <div class="controls">
                <textarea id="inputDescription" name="inputDescription" placeholder="It doesn't taste like a butt!" required="true" rows="3" cols="20"></textarea>
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
            <label class="control-label" for="inputImage">Image</label>
            <div class="controls input-append">
                <input type="text" class="input-xlarge imguploadChooserField" id="inputImage" name="inputImage" required="true" />
                <button class="btn imguploadChooserControl" type="button"><i class="icon-folder-open"></i> Select Uploaded Image</button>
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
    if($JACKED->Sessions->check('admin.error.editproduct')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.editproduct') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.editproduct');
    }

    if($JACKED->Sessions->check('admin.success.editproduct')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.editproduct') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.editproduct');
    }

?>

<table class="table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Description</th>
            <th>Cost</th>
            <th>Tangible</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    
    $products = $JACKED->Syrup->Product->find(array('active' => true));
    foreach($products as $product){
        ?>
        <tr>
            <td class="imagerow"> <span class="productimage"><img class="productImageThumb" src="<?php echo $product->image ? $product->image : ''; ?>" /></span> <span><input type="text" required class="input-large productimageinput imguploadChooserField" /> <button class="btn productimageinputbutton imguploadChooserControl"><i class="icon-folder-open"></i></span> </td>
            <td class="namerow"> <span class="productname"><?php echo $product->name; ?></span> <input type="text" required class="input-large productnameinput" /> </td>
            <td class="descriptionrow"> <span class="productdescription"><?php echo $product->description; ?></span> <textarea rows="4" required class="productdescriptioninput"></textarea> </td>
            <td class="costrow"> $<span class="productcost"><?php echo ($product->cost / 100.0); ?></span> <input type="text" required class="input-mini productcostinput" /> </td>
            <td class="tangiblerow"> <span class="producttangible"><?php echo ($product->tangible? 'Yes' : 'No'); ?></span> <input type="checkbox" value="True" class="producttangibleinput" <?php echo ($product->tangible? 'checked' : ''); ?> /> </td>
            <td class="actionsrow">
                <form method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/Purveyor">
                    <input type="hidden" name="manage_handler" value="products-edit-handler" />
                    <input type="hidden" name="action" />
                    <input type="hidden" name="newname" />
                    <input type="hidden" name="newimage" />
                    <input type="hidden" name="newdescription" />
                    <input type="hidden" name="newcost" />
                    <input type="hidden" name="newtangible" />
                    <input type="hidden" name="guid" value="<?php echo $product->guid; ?>" />
                    <button class="btn btn-warning catedit"><i class="icon-edit"></i></button> 
                    <button class="btn hidden btn-success productsave"><i class="icon-ok"></i></button> 
                    <button class="btn btn-danger catdelete"><i class="icon-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php
    }
?>
    </tbody>
</table>