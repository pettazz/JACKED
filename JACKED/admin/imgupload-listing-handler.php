<?php
    $JACKED = new JACKED(array('admin', 'Syrup'));

    if(!$JACKED->admin->checkLogin()){
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }

    $images = array();
    $imgUploadDir = JACKED_SITE_ROOT . $JACKED->admin->config->imgupload_directory;
    foreach(scandir($imgUploadDir) as $file){
        $fullPath = $imgUploadDir . $file;
        if(is_file($fullPath) && substr(mime_content_type($fullPath), 0, 5) === 'image'){
            $images[] = $file;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($images);
    
    exit();
?>