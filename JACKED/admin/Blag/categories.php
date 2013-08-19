<?php

    $JACKED->loadDependencies(array('Syrup'));

?>

<style type="text/css">
    input.input-large.catnameinput{
        display: none;
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
            $(this).siblings('button.catsave').removeClass('hidden');
            $(this).siblings('input[name="action"]').val('edit');
            var namerow = $(this).parents('td.actionsrow').siblings('td.namerow');
            namerow.children('input.catnameinput').val(namerow.children('span.catname').text());
            namerow.children('span.catname').hide();
            namerow.children('input.catnameinput').show();

            $(this).siblings('button.catsave').click(function(eo){
                var newname = $(this).parents('td.actionsrow').siblings('td.namerow').children('input.catnameinput').val();
                if(newname){
                    $(this).siblings('input[name="newname"]').val(newname);
                    $(this).parent('form').submit();
                }else{
                    $(this).parents('td.actionsrow').siblings('td.namerow').children('input.catnameinput').focus();
                    return false;
                }
            });

            return false;
        });
    });
</script>


<h2>Manage Categories</h2>

<h3>Add New</h3>
<?php
    if($JACKED->Sessions->check('admin.error.addcategory')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.addcategory') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.addcategory');
    }

    if($JACKED->Sessions->check('admin.success.addcategory')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.addcategory') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.addcategory');
    }

?>
<form class="form-horizontal" method="POST" action="/admin/module/Blag">
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="inputName">Name</label>
            <div class="controls">
                    <input type="text" class="input-large" id="inputName" name="inputName" placeholder="New-Category" required="true" />
            </div>
        </div>

        <div class="form-actions pull-right span9">
            <input type="hidden" name="manage_handler" value="categories-add-handler" />
            <button id="savecategory" type="submit" class="btn btn-success pull-right" style="margin-left:10px">Save</button>
        </div>
    </fieldset>
</form>

<h3>Existing Categories</h3>

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
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    
    $categories = $JACKED->Syrup->BlagCategory->find();
    foreach($categories as $cat){
        echo '    <tr>';
        echo '            <td>' . $cat->guid . '</td>';
        echo '            <td class="namerow"> <span class="catname">' . $cat->name . '</span> <input type="text" required class="input-large catnameinput" /> </td>';
        echo '            <td class="actionsrow">
        <form method="POST" action="/admin/module/Blag">
            <input type="hidden" name="manage_handler" value="categories-edit-handler" />
            <input type="hidden" name="action" />
            <input type="hidden" name="newname" />
            <input type="hidden" name="guid" value="' . $cat->guid . '" />
            <button class="btn btn-warning catedit"><i class="icon-edit"></i></button> 
            <button class="btn hidden btn-success catsave"><i class="icon-ok"></i></button> 
            <button class="btn btn-danger catdelete"><i class="icon-trash"></i></button>
        </form></td>';
        echo '    </tr>';
    }
?>
    </tbody>
</table>