<?php

    $JACKED->loadDependencies(array('DatasBeard'));

    // $table was loaded by the handler
    $schema = json_decode($table['schema'], true);
    $fieldDefs = $schema['properties'];

    $rows = $JACKED->DatasBeard->getRows($table['uuid']);
?>

<style type="text/css">

</style>

<script type="text/javascript">

    $(document).ready(function(){
        
    });

</script>

<h2>Contents of <?php echo $table['name']; ?></h2>

<p class="pull-right">
    <button class="btn btn-success btn-mini table-action" data-row-action="create"><i class="icon-white icon-plus"></i> Add New Row</button>
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