<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        $excat = $JACKED->Syrup->BlagCategory->find(array('name' => $_POST['inputName']));
        if($excat){
            throw new Exception("Category already exists");
        }
        $cat = $JACKED->Syrup->BlagCategory->create();
        $cat->name = $_POST['inputName'];
        $cat->save();
        $JACKED->Sessions->write('admin.success.addcategory', 'Category added succesfully.');
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.addcategory', $e->getMessage());
    }

    include('categories.php');

?>