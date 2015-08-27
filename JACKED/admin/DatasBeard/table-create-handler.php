<?php

    if(isset($_POST['doSave']) && trim($_POST['doSave']) !== ''){
        
        $JACKED->loadDependencies(array('DatasBeard'));

        try{
            $JACKED->DatasBeard->createTable(trim($_POST['inputTableName']), trim($_POST['inputTableSchema']));
            $JACKED->Sessions->write('admin.datasbeard.success', 'Table created successfully.');
        }catch(Exception $e){
            $JACKED->Sessions->write('admin.datasbeard.error', $e->getMessage());
        }

        include('menu.php');    

    }else{
        include('table-editor.php');    
    }

?>