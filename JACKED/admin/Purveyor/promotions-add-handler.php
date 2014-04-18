<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        
        $promotion = $JACKED->Syrup->Promotion->create();
        $promotion->name = $_POST['inputName'];
        $promotion->value = floor($_POST['inputValue'] * 100);
        $promotion->single_use = $_POST['inputSingleUse'];
        $promotion->save();
        $JACKED->Sessions->write('admin.success.addpromotion', 'Promotion added succesfully.');
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.addpromotion', $e->getMessage());
    }

    include('promotions.php');

?>