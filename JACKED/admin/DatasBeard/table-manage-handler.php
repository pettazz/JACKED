<?php

    if(isset($_POST['table-id']) && trim($_POST['table-id']) !== ''){
        $JACKED->loadDependencies(array('DatasBeard'));

        $table = $JACKED->DatasBeard->getTable(trim($_POST['table-id']), true, false);

        if($table){
            if(isset($_POST['doSaveNew']) && trim($_POST['doSaveNew']) == 'true'){
                try{
                    $JACKED->DatasBeard->createRow(trim($_POST['inputTableId']), trim($_POST['inputRowContent']));
                    $JACKED->Sessions->write('admin.datasbeard.success', 'Table updated successfully.');
                }catch(Exception $e){
                    $JACKED->Sessions->write('admin.datasbeard.error', $e->getMessage());
                }
            }else if(isset($_POST['doSaveEdit']) && trim($_POST['doSaveEdit']) == 'true'){
                try{
                    $JACKED->DatasBeard->setRow(trim($_POST['inputRowId']), trim($_POST['inputRowContent']));
                    $JACKED->Sessions->write('admin.datasbeard.success', 'Table updated successfully.');
                }catch(Exception $e){
                    $JACKED->Sessions->write('admin.datasbeard.error', $e->getMessage());
                }
            }

            include('table-manager.php');   
        }else{
            $JACKED->Sessions->write('admin.datasbeard.error', 'Specified table not found.');
            include('menu.php');        
        }
    }else{
        $JACKED->Sessions->write('admin.datasbeard.error', 'No table specified.');
        include('menu.php');        
    }   

?>