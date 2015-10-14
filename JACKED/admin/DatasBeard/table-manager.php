<?php

    $JACKED->loadDependencies(array('DatasBeard'));

    // $table was loaded by the handler
    $schema = json_decode($table['schema'], true);
    $fieldDefs = $schema['properties'];
    $requiredFields = $schema['required'];

    foreach ($fieldDefs as $fieldName => $def){
        $fieldDefs[$fieldName]['required'] = in_array($fieldName, $requiredFields);
    }

    $rows = $JACKED->DatasBeard->getRows($table['uuid']);
?>

<style type="text/css">

</style>

<script type="text/javascript">

    var rows = <?php echo json_encode($rows); ?>;
    var fieldDefinitions = <?php echo json_encode($fieldDefs); ?>;

    $(document).ready(function(){
        $('.row-action').click(function(el, ev){
            var action = $(this).data('row-action');
            var rowId = $(this).data('row-id');

            $('form#bullshitRouterTM [name=row-action]').val(action);
            $('form#bullshitRouterTM [name=row-id]').val(rowId);
                
            if(action === 'delete'){
                $('#deleteConfirmationModal').modal({
                    keyboard: false,
                    backdrop: 'static',
                    show: true
                });
            }else{
                if(action === 'create'){
                    $('#rowEditModal').find('p.lead').html('Add Row');   
                    $('#rowEditModal').find('form input,textarea').val('');
                }else{
                    $('#rowEditModal').find('p.lead').html('Edit Row');
                    $('#rowEditModal').find('form input,textarea').each(function(){
                        var name = $(this).attr('name');
                        var type = fieldDefinitions[name]['type'];

                        if(type.toLowerCase() === 'boolean'){
                            $(this).prop('checked', rows[rowId][name]);
                        }else{
                            $(this).val(rows[rowId][name]);
                        }
                    });
                }

                $('#rowEditModal').modal({
                    keyboard: false,
                    backdrop: 'static',
                    show: true
                });
            }
        });

        $('#confirmDelete').click(function(el, ev){
            $('form#bullshitRouterTM').submit();
        });

        $('#confirmRowSave').click(function(el, ev){
            var newRow = {};

            $('#rowEditModal').find('form input,textarea').each(function(){
                var name = $(this).attr('name');
                var type = fieldDefinitions[name]['type'];

                switch(type.toLowerCase()){
                    case 'boolean':
                        newRow[name] = Boolean($(this).prop('checked'));
                        console.log(Boolean($(this).prop('checked')));
                        break;
                    case 'integer':
                    case 'number':
                        newRow[name] = parseInt($(this).val());
                        break;
                    default:
                        newRow[name] = $(this).val();
                        break;
                }
            });

            //add it to router 
            $('form#bullshitRouterTM [name=row-data]').val(JSON.stringify(newRow));

           $('form#bullshitRouterTM').submit(); 
        });
    });

</script>

<div id="rowEditModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
    <button type="button" style="margin-right:10px;" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <div class="modal-body">
        <p class="lead">Poop</p>
        <form id="rowEditor" class="form-horizontal">
            <fieldset>

                <?php foreach($fieldDefs as $fieldName => $field){ ?>
                <div class="control-group">
                    <label class="control-label" for="beardEdit-<?php echo $fieldName; ?>"><?php echo $fieldName; ?></label>
                    <div class="controls">
                        <?php 
                            switch(strtolower($field['type'])){ 
                                case 'boolean':
                                    echo '<input type="checkbox" id="beardEdit-' . $fieldName . '" name="' . $fieldName . '" ' . ($field['required']? 'required' : '') . ' />';
                                    break;
                                case 'any':
                                    echo '<textarea id="beardEdit-' . $fieldName . '" name="' . $fieldName . '" ' . ($field['required']? 'required' : '') . '></textarea>';
                                    break;
                                default:
                                    echo '<input id="beardEdit-' . $fieldName . '" type="text" name="' . $fieldName . '" ' . ($field['required']? 'required' : '') . ' />';
                                    break;
                            }
                        ?>
                    </div>
                </div>
                <?php } ?>

            </fieldset>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button class="btn btn-primary" id="confirmRowSave">Save</button>
    </div>
</div>

<div id="deleteConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-body">
        <p class="lead">Are you sure you want to delete this row?</p>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button class="btn btn-danger" id="confirmDelete">Delete Row</button>
    </div>
</div>

<form id="bullshitRouterTM" action="<?php echo $JACKED->admin->config->entry_point; ?>module/DatasBeard" method="POST">
    <input type="hidden" name="manage_handler" value="table-manage-handler" />
    <input type="hidden" name="row-action" value="" />
    <input type="hidden" name="table-id" value="<?php echo $table['uuid']; ?>" />
    <input type="hidden" name="row-id" value="" />
    <input type="hidden" name="row-data" value="" />
</form>

<?php
    if($JACKED->Sessions->check('admin.datasbeard.error')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.datasbeard.error') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.datasbeard.error');
    }

    if($JACKED->Sessions->check('admin.datasbeard.success')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.datasbeard.success') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.datasbeard.success');
    }

?>

<h2>Contents of <?php echo $table['name']; ?></h2>

<p class="pull-right">
    <a class="btn btn-mini" href="<?php echo $JACKED->admin->config->entry_point; ?>module/DatasBeard"><i class="icon-chevron-left icon-chevron-left"></i> Back to Tables</a>
    <button class="btn btn-success btn-mini row-action" data-row-action="create"><i class="icon-white icon-plus"></i> Add New Row</button>
</p>

<table class="table table-hover table-stripe table-condensed">
    <thead>
        <tr>
            <?php foreach($fieldDefs as $fieldName => $field){
                echo '            <th>' . $fieldName . '</th>';
            } ?>
            <th width="30px">Actions</th>
        </tr>
    </thead>
    <tbody>
            <?php foreach($rows as $row){
                echo '        <tr class="beardRow" data-row-id="' . $row['uuid'] . '">';
                foreach($row as $fieldName => $rowValue){
                    if($fieldName !== 'uuid'){
                        $fieldType = $fieldDefs[$fieldName]['type'];
                        switch(strtolower($fieldType)){
                            case 'boolean':
                                echo '            <td class="beardField-boolean"><input type="checkbox" disabled="disabled" ' . ($rowValue ? 'checked' : '') . ' /></td>';
                                break;
                            case 'any':
                                echo '            <td class="beardField-any"><code>' . htmlentities($rowValue) . '</code></td>';
                                break;
                            default:
                                echo '            <td class="beardField-' . $fieldType . '">' . $rowValue . '</td>';
                                break;
                        }
                    }
                }
                ?>
            <td width="30px">
                <div class="btn-group">
                    <a class="btn btn-inverse btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="icon-white icon-cog"></i>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu row-actions">
                        <li><a class="row-action" href="#" data-row-action="edit" data-row-id="<?php echo $row['uuid']; ?>"><i class="icon-edit"></i> Edit</a></li>
                        <li><a class="row-action" href="#" data-row-action="delete" data-row-id="<?php echo $row['uuid']; ?>"><i class="icon-trash"></i> <span class="text-error">Delete</span></a></li>
                    </ul>
                </div>
            </td>
        </tr>
            <?php } ?>
    </tbody>
</table>