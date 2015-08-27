<?php

    $JACKED->loadDependencies(array('DatasBeard', 'Syrup'));

    $tables = $JACKED->DatasBeard->getTables();

?>

<script type="text/javascript">
    
    var tableNames = {
        <?php foreach($tables as $table){
            echo '"' . $table->uuid . '": "' . $table->name . '",' . "\n";
        }?>
    };

    $(document).ready(function(){
        $('.table-action').click(function(el, ev){
            var action = $(this).data('table-action');
            var tableId = $(this).data('table-id');

            $("#bullshitRouterTM").find('input[name=table-action]').val(action);
            $("#bullshitRouterTM").find('input[name=manage_handler]').val('table-' + action + '-handler');
            $("#bullshitRouterTM").find('input[name=table-id]').val(tableId);

            if(action === 'delete'){
                $('#deleteConfirmationModal').find('.tableNameConfirm').html(tableNames[tableId]);
                $('#deleteConfirmationModal').modal({
                    keyboard: false,
                    backdrop: 'static',
                    show: true
                });
            }else{
                $('form#bullshitRouterTM').submit();
            }
        });

        $('#confirmDelete').click(function(el, ev){
            $('form#bullshitRouterTM').submit();
        });
    });

</script>

<form id="bullshitRouterTM" action="<?php echo $JACKED->admin->config->entry_point; ?>module/DatasBeard" method="POST">
    <input type="hidden" name="manage_handler" value="" />
    <input type="hidden" name="table-action" value="" />
    <input type="hidden" name="table-id" value="" />
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

<h3>Tables</h3>

<p class="pull-right">
    <button class="btn btn-success btn-mini table-action" data-table-action="create"><i class="icon-white icon-plus"></i> Add New Table</button>
</p>
<?php
    if(empty($tables)){
        echo '<p class="lead text-center">No tables</p>';
    }else{
?>
<table class="table table-hover table-condensed table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Rows</th>
            <th>Updated</th>
            <th width="160px">Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php
        foreach($tables as $table){
            $rows = $JACKED->DatasBeard->getRows($table->uuid);
            if(count($rows) > 0){
                $lastUpdatedRow = $JACKED->Syrup->DatasBeardRow->findOne(array(
                    'alive' => 1,
                    'Table' => $table->uuid
                ), array(
                    'field' => 'edited',
                    'direction' => 'DESC'
                ));
                if($lastUpdatedRow){
                    $lastUpdate = date("D F j, Y g:i a", $lastUpdatedRow->edited);
                }else{
                    $lastUpdate = date("D F j, Y g:i a", $table->created);    
                }
            }else{
                $lastUpdate = date("D F j, Y g:i a", $table->created);
            }
    ?>
        <tr data-table-id="<?php echo $table->uuid; ?>">
            <td><?php echo $table->name; ?></td>
            <td><?php echo count($rows); ?></td>
            <td><?php echo $lastUpdate; ?></td>
            <td width="160px">
                <button class="btn btn-info btn-mini table-action" data-table-action="manage" data-table-id="<?php echo $table->uuid; ?>"><i class="icon-white icon-edit"></i> Manage Contents</button>

                <div class="btn-group">
                    <a class="btn btn-inverse btn-mini dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="icon-white icon-cog"></i>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu table-actions">
                        <li><a class="table-action" href="#" data-table-action="edit" data-table-id="<?php echo $table->uuid; ?>"><i class="icon-th-list"></i> Edit Structure</a></li>
                        <li><a class="table-action" href="#" data-table-action="delete" data-table-id="<?php echo $table->uuid; ?>"><i class="icon-trash"></i> <span class="text-error">Delete Table</span></a></li>
                    </ul>
                </div>
            </td>
        </tr>
    <?php
        }
    }
    ?>

    </tbody>
</table>

<div id="deleteConfirmationModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-body">
        <p class="lead">Are you sure you want to delete the table <strong class="tableNameConfirm"></strong>?</p>
        <p>Any rows within and all the data they contain will become inaccessible. This may cause template rendering to become unstable.</p>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
        <button class="btn btn-danger" id="confirmDelete">Delete Table</button>
    </div>
</div>