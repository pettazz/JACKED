<?php

    try{
        $JACKED->loadDependencies(array('Syrup', 'Purveyor'));
        
        $exsale = $JACKED->Syrup->Sale->find(array('guid' => $_POST['saleGuid']));
        if(!$exsale){
            throw new Exception("Sale not found");
        }
        $exobj = $exsale[0];
        $exobj->tracking = $_POST['inputTracking'];
        $exobj->shipped = True;
        $exobj->save();
        $JACKED->Sessions->write('admin.success.editsale', 'Sale updated succesfully.');

        if($exobj->tracking){
            $JACKED->Purveyor->sendShippedEmail($exobj->guid);
        }
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.editsale', $e->getMessage());
    }

    include('sales.php');

?>