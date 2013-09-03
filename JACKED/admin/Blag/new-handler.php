<?php
    
    $error = FALSE;
    $success = FALSE;

    try{
        $JACKED->loadDependencies(array('Syrup', 'Curator'));
        require(JACKED_LIB_ROOT . 'php-markdown/markdown.php');
        $markdown = new Markdown();

        $post = $JACKED->Syrup->Blag->create();

        if($_POST['saveType'] == 'live'){
            if(trim($_POST['inputCategory']) == ''){
                throw new Exception("Missing required field: Category.");
            }
            if(trim($_POST['inputTitle']) == ''){
                throw new Exception("Missing required field: Title.");
            }
            if(trim($_POST['inputHeadline']) == ''){
                throw new Exception("Missing required field: Headline.");
            }
            if(trim($_POST['inputContent']) == ''){
                throw new Exception("Missing required field: Content.");
            }
        }

        $post->author = $JACKED->Sessions->read('auth.admin.userid');
        $post->posted = time();
        $post->category = $_POST['inputCategory'];
        $post->alive = ($_POST['saveType'] == 'live'? 1 : 0);
        $post->title = $_POST['inputTitle'];
        $post->headline = $_POST['inputHeadline'];
        $post->content = $markdown->toHTML($_POST['inputContent']);

        $post->save();

        foreach(explode(',', $_POST['inputTags']) as $tagName){
            $tagName = trim($tagName);
            $JACKED->Curator->assignTagByName($post->guid, $tagName);
        }

        $success = TRUE;
    }catch(Exception $e){
        $error = TRUE; 
        $message = $e->getMessage();
    }

    if($error){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $message .  '" </p>
        </div>';

        include('new.php');
    }else if($success){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>Post was saved successfully. </p>
        </div>';
    }
?>