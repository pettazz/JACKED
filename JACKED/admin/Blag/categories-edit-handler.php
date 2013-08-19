<?php

    try{
        $JACKED->loadDependencies(array('Syrup'));
        if($_POST['action'] == 'delete'){
            $excat = $JACKED->Syrup->BlagCategory->find(array('guid' => $_POST['guid']));
            if(!$excat){
                throw new Exception("Category not found");
            }
            $exobj = $excat[0];
            $exobj->delete();
            $JACKED->Sessions->write('admin.success.editcategory', 'Category deleted succesfully.');
        }else if($_POST['action'] == 'edit'){
            $excat = $JACKED->Syrup->BlagCategory->find(array('guid' => $_POST['guid']));
            if(!$excat){
                throw new Exception("Category not found");
            }
            $exobj = $excat[0];
            $exobj->name = $_POST['newname'];
            $exobj->save();
            $JACKED->Sessions->write('admin.success.editcategory', 'Category updated succesfully.');
        }else{
            throw new Exception("No action defined.");
        }
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.editcategory', $e->getMessage());
    }

    include('categories.php');

?>