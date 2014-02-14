<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        if($_POST['action'] == 'delete'){
            $expromotion = $JACKED->Syrup->Promotion->find(array('guid' => $_POST['guid']));
            if(!$expromotion){
                throw new Exception("Promotion not found");
            }
            $exobj = $expromotion[0];
            $tickets = $JACKED->Syrup->find(array('Promotion' => $exobj->guid));
            foreach($tickets as $ticket){
                $ticket->delete();
            }
            $exobj->delete();
            $JACKED->Sessions->write('admin.success.editpromotion', 'Promotion deleted succesfully.');
        }else if($_POST['action'] == 'edit'){
            $expromotion = $JACKED->Syrup->Promotion->find(array('guid' => $_POST['guid']));
            if(!$expromotion){
                throw new Exception("Promotion not found");
            }
            $exobj = $expromotion[0];
            $exobj->name = $_POST['newname'];
            $exobj->value = floor($_POST['newvalue'] * 100);
            $exobj->active = (isset($_POST['newactive']) && $_POST['newactive'] == "true")? 1 : 0;
            $exobj->save();
            $JACKED->Sessions->write('admin.success.editpromotion', 'Promotion updated succesfully.');
        }else{
            throw new Exception("No action defined.");
        }
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.editpromotion', $e->getMessage());
    }

    include('promotions.php');

?>