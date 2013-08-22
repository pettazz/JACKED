<?php
     
    require('../jacked_conf.php');
    $JACKED = new JACKED("admin");

    if(!$JACKED->admin->checkLogin()){
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }

    if(!empty($_FILES)){
        try{
            $tempFile = $_FILES['file']['tmp_name'];                     
            $targetPath = $JACKED->admin->config->imgupload_directory;
            $targetFile = $targetPath . $_FILES['file']['name'];
            if(file_exists($targetFile)){
                header('HTTP/1.1 409 Conflict');
                echo 'File already exists.';
                exit();    
            }
            move_uploaded_file($tempFile, $targetFile); 
        }catch(Exception $e){
            header('HTTP/1.1 500 Internal Server Error');
            exit();
        }
        header('HTTP/1.1 200 OK');
    }else{
        header('HTTP/1.1 400 Bad Request');
    }
?>