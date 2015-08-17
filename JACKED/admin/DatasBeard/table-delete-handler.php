<?php

    try{
        $JACKED->loadDependencies(array('DatasBeard'));
        if($_POST['table-action'] === 'delete' && isset($_POST['table-id'])){
            $JACKED->DatasBeard->deleteTable(trim($_POST['table-id']));
            $JACKED->Sessions->write('admin.datasbeard.deletetable.success', 'Table deleted successfully.');
        }else{
            throw new Exception("No action defined.");
        }
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.datasbeard.deletetable.error', $e->getMessage());
    }

    include('menu.php');

?>