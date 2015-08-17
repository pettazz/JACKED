<?php

    if(isset($_POST['tableId']){
        $tableId = $_POST['tableId'];
    else{
        $JACKED->Sessions->write('admin.error.edittable', 'No table selected');
    }

    $JACKED->loadDependencies(array('DatasBeard', 'Syrup'));
    $table = $JACKED->DatasBeard->getTable($tableId);

?>

<h3>Tables</h3>

<p class="pull-right">
    <button class="btn"><i class="icon-plus"></i> Add New Table</button>
</p>

<table class="table table-hover">
    <thead>
        <tr>
            <th>Name</th>
            <th>Rows</th>
            <th>Updated</th>
            <th width="215px">Actions</th>
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
            <td width="215px">
                <button class="btn table-action" data-table-action="manage" data-table-id="<?php echo $table->uuid; ?>"><i class="icon-edit"></i> Manage Contents</button>

                <div class="btn-group">
                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="icon-cog"></i>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu table-actions">
                        <li><a class="table-action" href="#" data-table-action="edit" data-table-id="<?php echo $table->uuid; ?>"><i class="icon-edit"></i> Edit Table Structure</a></li>
                        <li><a class="table-action" href="#" data-table-action="delete" data-table-id="<?php echo $table->uuid; ?>"><span class="text-error"><i class="icon-trash"></i> Delete Table</span></a></li>
                    </ul>
                </div>
            </td>
        </tr>
    <?php
        }
    ?>

    </tbody>
</table>