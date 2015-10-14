<?php

    if(isset($_POST['table-id']) && trim($_POST['table-id']) !== ''){
        $JACKED->loadDependencies(array('DatasBeard'));

        $table = $JACKED->DatasBeard->getTable(trim($_POST['table-id']), true, false);

        if($table){
            if(isset($_POST['row-action']) && trim($_POST['row-action']) == 'create'){
                try{
                    $JACKED->DatasBeard->createRow(trim($_POST['table-id']), json_decode(trim($_POST['row-data'])));
                    $JACKED->Sessions->write('admin.datasbeard.success', 'Row saved successfully.');
                }catch(Exception $e){
                    $JACKED->Sessions->write('admin.datasbeard.error', $e->getMessage());
                }
            }else if(isset($_POST['row-action']) && trim($_POST['row-action']) == 'edit'){
                try{
                    $JACKED->DatasBeard->setRow(trim($_POST['row-id']), json_decode(trim($_POST['row-data'])));
                    $JACKED->Sessions->write('admin.datasbeard.success', 'Row updated successfully.');
                }catch(Exception $e){
                    $JACKED->Sessions->write('admin.datasbeard.error', $e->getMessage());
                }
            }else if(isset($_POST['row-action']) && trim($_POST['row-action']) == 'delete'){
                try{
                    $JACKED->DatasBeard->deleteRow(trim($_POST['row-id']));
                    $JACKED->Sessions->write('admin.datasbeard.success', 'Row deleted successfully.');
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