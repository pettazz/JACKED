<?php

    $JACKED->loadDependencies(array('Syrup', 'DatasBeard'));

    if(isset($_POST['doSave']) && trim($_POST['doSave']) !== ''){

        try{
            $extable = $JACKED->Syrup->DatasBeardTable->findOne(array('uuid' => trim($_POST['inputTableId'])));
            $extable->name = trim($_POST['inputTableName']);
            $extable->schema = trim($_POST['inputTableSchema']);
            $extable->save();
        }catch(Exception $e){
            $JACKED->Sessions->write('admin.datasbeard.error', $e->getMessage());
        }

        $JACKED->Sessions->write('admin.datasbeard.success', 'Table edited successfully.');
        include('menu.php');    

    }else{
        if(isset($_POST['table-id'])){
            $table = $JACKED->DatasBeard->getTable(trim($_POST['table-id']), true, false);
        }else{
            $table = null;
        }

        include('table-editor.php');    
    }

?>