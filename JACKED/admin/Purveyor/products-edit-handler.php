<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        if($_POST['action'] == 'delete'){
            $exproduct = $JACKED->Syrup->Product->find(array('guid' => $_POST['guid']));
            if(!$exproduct){
                throw new Exception("Product not found");
            }
            $exobj = $exproduct[0];
            $exobj->delete();
            $JACKED->Sessions->write('admin.success.editproduct', 'Product deleted succesfully.');
        }else if($_POST['action'] == 'edit'){
            $exproduct = $JACKED->Syrup->Product->find(array('guid' => $_POST['guid']));
            if(!$exproduct){
                throw new Exception("Product not found");
            }
            $exobj = $exproduct[0];
            $exobj->name = $_POST['newname'];
            $exobj->cost = floor($_POST['newcost'] * 100);
            $exobj->tangible = (isset($_POST['newtangible']) && $_POST['newtangible'] == "true")? 1 : 0;
            $exobj->save();
            $JACKED->Sessions->write('admin.success.editproduct', 'Product updated succesfully.');
        }else{
            throw new Exception("No action defined.");
        }
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.editproduct', $e->getMessage());
    }

    include('products.php');

?>