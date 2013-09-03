<?php

    $JACKED->loadDependencies(array('Syrup', 'Curator'));

    echo 'handler-load';
    require(JACKED_LIB_ROOT . 'php-markdown/markdown.php');
    $markdown = new Markdown();

    if(isset($_POST['editAction'])){
        switch($_POST['editAction']){
            case 'edit':
                include('edit.php');
                break;

            case 'save':
                try{
                    if(trim($_POST['guid']) == ''){
                        throw new Exception('No post specified.');
                    }
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

                    $existingPost = $JACKED->Syrup->Blag->findOne(array('guid' => $_POST['guid']));

                    if(!$existingPost){
                        throw new Exception('Post not found.');
                    }

                    $existingPost->author = $JACKED->Syrup->User->findOne(array(
                        'guid' => $JACKED->Sessions->read('auth.admin.userid')
                    ));
                    $existingPost->posted = time();
                    $existingPost->category = $JACKED->Syrup->BlagCategory->findOne(array(
                        'guid' => $_POST['inputCategory']
                    ));;
                    $existingPost->alive = ($_POST['saveType'] == 'live'? 1 : 0);
                    $existingPost->title = $_POST['inputTitle'];
                    $existingPost->headline = $_POST['inputHeadline'];
                    $existingPost->content = $markdown->toHTML($_POST['inputContent']);

                    // three cases for tags
                    // in extags, not in newtags => untag extag->name
                    // in extags, in newtags     => noop
                    // not in extags, in newtags => tag newtag->name
                    $newTags = explode(',', $_POST['inputTags']);

                    $exTags = array();
                    foreach($existingPost->Curator as $exTag){
                        $exTags[] = $exTag->name;
                        // in extags, not in newtags => untag extag->name
                        if(!in_array($exTag->name, $newTags)){
                            $JACKED->Curator->removeTagByName($existingPost->guid, $exTag->name);
                        }
                    }
                    foreach($newTags as $tagName){
                        $tagName = trim($tagName);
                        // not in extags, in newtags => tag newtag->name
                        if(!in_array($tagName, $exTags)){
                            $JACKED->Curator->assignTagByName($existingPost->guid, $tagName);
                        }
                    }
                    
                    $existingPost->save();
                    $JACKED->Sessions->write('admin.success.editpost', 'Post successfully saved.');
                    include('posts.php');
                }catch(Exception $e){
                    $JACKED->Sessions->write('admin.error.editpost', $e->getMessage() . $e->getTraceAsString());
                    include('edit.php');
                }
                break;

            case 'delete':
                try{
                    if(trim($_POST['guid']) == ''){
                        throw new Exception('No post specified.');
                    }
                    $post = $JACKED->Syrup->Blag->findOne(array('guid' => $_POST['guid']));
                    if(!$post){
                        throw new Exception('Post not found.');
                    }
                    $post->delete();
                    $tags = $JACKED->Curator->getTagsForTarget($_POST['guid']);
                    if($tags){
                        foreach($tags as $tag){
                            $JACKED->Curator->removeTag($_POST['guid'], $tag['guid']);
                        }
                    }
                    $JACKED->Sessions->write('admin.success.editpost', 'Post successfully deleted.');
                }catch(Exception $e){
                    $JACKED->Sessions->write('admin.error.editpost', $e->getMessage());
                }
                include('posts.php');
                break;

            default:
                $JACKED->Sessions->write('admin.error.editpost', 'No action specified.');
                include('posts.php');
                break;
        }
    }else{
        $JACKED->Sessions->write('admin.error.editpost', 'No action specified.');
        include('posts.php');
    }

?>