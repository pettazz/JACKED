<?php

    function generateResponse($data, $success = true){
        header('Content-Type: application/json');
        $data['status'] = $success ? 'success' : 'error';
        echo json_encode($data);
        exit();
    }
     
    $JACKED = new JACKED("admin");

    if(!$JACKED->admin->checkLogin()){
        header('HTTP/1.1 401 Unauthorized');
        exit();
    }

    $isCroppic = array_key_exists('isCroppic', $_POST) && $_POST['isCroppic'];

    if(!empty($_FILES)){
        try{
            $tempFile = $_FILES['img']['tmp_name'];                     
            $targetPath = JACKED_SITE_ROOT . $JACKED->admin->config->imgupload_directory;
            $ext = substr(strrchr($_FILES['img']['name'], '.'), 0);
            $targetName = $JACKED->Util->uuid4(false) . $ext;
            $targetFile = $targetPath . $targetName;
            if(file_exists($targetFile)){
                if($isCroppic){
                    header('HTTP/1.1 200 OK');
                }else{
                    header('HTTP/1.1 409 Conflict');
                }
                generateResponse(array('message' => 'A file with that name already exists!'), false);  
            }
            move_uploaded_file($tempFile, $targetFile); 
            $imagedetails = getimagesize($targetFile);
        }catch(Exception $e){
            if($isCroppic){
                header('HTTP/1.1 200 OK');
            }else{
                header('HTTP/1.1 500 Internal Server Error');
            }
            error_log('JACKED admin imgupload error: ' . $e->getMessage());
            generateResponse(array('message' => 'Internal server error: ' . $e->getMessage()), false);
        }
        header('HTTP/1.1 200 OK');
        generateResponse(array(
            'url' => $JACKED->config->base_url . $JACKED->admin->config->imgupload_directory . rawurlencode($targetName),
            'width' => $imagedetails[0] / 2,
            'height' => $imagedetails[1] / 2 
        ));
    }else if(array_key_exists('imgUrl', $_POST) && $_POST['imgUrl']){
        if(extension_loaded('imagick')){
            if($isCroppic){
                header('HTTP/1.1 200 OK');
            }else{
                header('HTTP/1.1 400 Bad Request');
            }
            generateResponse(array('message' => 'I didn\'t make this yet.'));
        }else{
            try{
                $imgUrl = $_POST['imgUrl'];
                // original sizes
                $imgInitW = $_POST['imgInitW'] * 2;
                $imgInitH = $_POST['imgInitH'] * 2;
                // resized sizes
                $imgW = $_POST['imgW'] * 2;
                $imgH = $_POST['imgH'] * 2;
                // offsets
                $imgY1 = $_POST['imgY1'] * 2;
                $imgX1 = $_POST['imgX1'] * 2;
                // crop box
                $cropW = $_POST['cropW'] * 2;
                $cropH = $_POST['cropH'] * 2;
                // rotation angle
                $angle = $_POST['rotation'];

                $png_quality = -1;
                $jpeg_quality = 100;

                $name = "cropped_" . $JACKED->Util->uuid4(false);
                $output_filename = JACKED_SITE_ROOT . $JACKED->admin->config->imgupload_directory . $name;
                $output_url = $JACKED->config->base_url . $JACKED->admin->config->imgupload_directory . $name;
                $what = getimagesize($imgUrl);
                switch(strtolower($what['mime']))
                {
                    case 'image/png':
                        $img_r = imagecreatefrompng($imgUrl);
                        $source_image = imagecreatefrompng($imgUrl);
                        $type = '.png';
                        break;
                    case 'image/jpeg':
                        $img_r = imagecreatefromjpeg($imgUrl);
                        $source_image = imagecreatefromjpeg($imgUrl);
                        $type = '.jpeg';
                        break;
                    case 'image/gif':
                        $img_r = imagecreatefromgif($imgUrl);
                        $source_image = imagecreatefromgif($imgUrl);
                        $type = '.gif';
                        break;
                    default: 
                        throw new Exception('Image type not supported');
                }

                // resize the original image to size of editor
                $resizedImage = imagecreatetruecolor($imgW, $imgH);
                imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
                // rotate the rezized image
                $rotated_image = imagerotate($resizedImage, -$angle, 0);
                // find new width & height of rotated image
                $rotated_width = imagesx($rotated_image);
                $rotated_height = imagesy($rotated_image);
                // diff between rotated & original sizes
                $dx = $rotated_width - $imgW;
                $dy = $rotated_height - $imgH;
                // crop rotated image to fit into original rezized rectangle
                $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
                // imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
                imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
                // crop image into selected area
                $final_image = imagecreatetruecolor($cropW, $cropH);
                // imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
                imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);
                // finally output png image
                imagepng($final_image, $output_filename . '.png', $png_quality);
                // imagejpeg($final_image, $output_filename.$type, $jpeg_quality);

                header('HTTP/1.1 200 OK');
                generateResponse(array(
                    "status" => 'success',
                    "url" => $output_url . '.png'
                ));

            }catch(Exception $e){
                if($isCroppic){
                    header('HTTP/1.1 200 OK');
                }else{
                    header('HTTP/1.1 500 Internal Server Error');
                }
                error_log('JACKED admin imgupload error: ' . $e->getMessage());
                generateResponse(array('message' => 'Internal server error: ' . $e->getMessage()), false);
            }
        }
    }else{
        if($isCroppic){
            header('HTTP/1.1 200 OK');
        }else{
            header('HTTP/1.1 400 Bad Request');
        }
        generateResponse(array('message' => 'Invalid request.'), false);
    }
?>