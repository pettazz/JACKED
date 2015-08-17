<?php

    if(isset($_POST['doSave']) && trim($_POST['doSave']) !== ''){
        
        $JACKED->loadDependencies(array('DatasBeard'));

        try{
            $JACKED->DatasBeard->createTable(trim($_POST['inputTableName']), trim($_POST['inputTableSchema']));
        }catch(Exception $e){
            $JACKED->Sessions->write('admin.datasbeard.error', $e->getMessage());
        }

        $JACKED->Sessions->write('admin.datasbeard.success', 'Table created successfully.');
        include('menu.php');    

    }else{
        include('table-editor.php');    
    }

?>