<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        
        $product = $JACKED->Syrup->Product->create();
        $product->name = $_POST['inputName'];
        $product->image = $_POST['inputImage'];
        $product->description = $_POST['inputDescription'];
        $product->cost = floor($_POST['inputCost'] * 100);
        // $product->tangible = (isset($_POST['inputTangible']))? 1 : 0;
        $product->max_quantity = $_POST['inputMaxQuantity'];
        $product->save();
        $JACKED->Sessions->write('admin.success.addproduct', 'Product added succesfully.');
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.addproduct', $e->getMessage());
    }

    include('products.php');

?>