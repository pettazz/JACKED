<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        if($_POST['action'] == 'delete'){
            $exticket = $JACKED->Syrup->Ticket->find(array('guid' => $_POST['guid']));
            if(!$exticket){
                throw new Exception("Ticket not found");
            }
            $exobj = $exticket[0];
            $exobj->delete();
            $JACKED->Sessions->write('admin.success.editticket', 'Ticket deleted succesfully.');
        }else if($_POST['action'] == 'edit'){
            $exticket = $JACKED->Syrup->Ticket->find(array('guid' => $_POST['guid']));
            if(!$exticket){
                throw new Exception("Ticket not found");
            }
            $exobj = $exticket[0];
            $exobj->valid = (isset($_POST['newactive']) && $_POST['newactive'] == "true")? 1 : 0;
            $exobj->save();
            $JACKED->Sessions->write('admin.success.editticket', 'Ticket updated succesfully.');
        }else{
            throw new Exception("No action defined.");
        }
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.editticket', $e->getMessage());
    }

    include('tickets.php');

?>