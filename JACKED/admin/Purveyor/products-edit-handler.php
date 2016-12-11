<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        if($_POST['action'] == 'delete'){
            $exproduct = $JACKED->Syrup->Product->find(array('guid' => $_POST['guid']));
            if(!$exproduct){
                throw new Exception("Product not found");
            }
            $exobj = $exproduct[0];
            $exobj->active = false;
            $exobj->save();
            $JACKED->Sessions->write('admin.success.editproduct', 'Product deleted succesfully.');
        }else if($_POST['action'] == 'edit'){
            $exproduct = $JACKED->Syrup->Product->find(array('guid' => $_POST['guid']));
            if(!$exproduct){
                throw new Exception("Product not found");
            }
            $exobj = $exproduct[0];
            $exobj->name = $_POST['newname'];
            $exobj->image = $_POST['newimage'];
            $exobj->description = $_POST['newdescription'];
            $exobj->cost = floor($_POST['newcost'] * 100);
            // $exobj->tangible = (isset($_POST['newtangible']) && $_POST['newtangible'] == "true")? 1 : 0;
            $exobj->max_quantity = $_POST['newmaxquantity'];
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