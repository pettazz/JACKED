<?php

    $JACKED->loadDependencies(array('DatasBeard'));

    $tables = $JACKED->DatasBeard->getTables();

?>

<h3>Tables</h3>

<table class="table table-hover">
    <thead>
        <tr>
            <th width="130px">id</th>
            <th>Name</th>
            <th>Created</th>
            <th>HasSchema?</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

    <?php
        foreach($tables as $table){
    ?>
        <tr>
            <td><?php echo $table->guid; ?></td>
            <td><?php echo $table->name; ?></td>
            <td><?php echo $table->created; ?></td>
            <td><?php echo ($table->schema == null) ? '<i class="icon-remove-sign"></i>' : '<i class="icon-ok-sign"></i>'; ?></td>
            <td>lol</td>
        </tr>
    <?php
        }
    ?>

    </tbody>
</table>