<?php

    $JACKED->loadDependencies(array('Syrup', 'Curator'));

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

                    if(isset($_POST['inputOverrideAuthor']) && $_POST['inputOverrideAuthor'] == 'true'){
                        $existingPost->author = $JACKED->Syrup->User->findOne(array(
                            'guid' => $JACKED->Sessions->read('auth.admin.userid')
                        ));
                    }
                    if(isset($_POST['inputOverrideDate']) && $_POST['inputOverrideDate'] == 'true'){
                        $existingPost->posted = time();
                    }
                    $existingPost->category = $JACKED->Syrup->BlagCategory->findOne(array(
                        'guid' => $_POST['inputCategory']
                    ));
                    $existingPost->alive = ($_POST['saveType'] == 'live'? 1 : 0);
                    $existingPost->title = $_POST['inputTitle'];
                    $existingPost->headline = $_POST['inputHeadline'];
                    $existingPost->thumbnail = substr(strrchr($_POST['inputThumbnail'], '/'), 1);
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

                    if($existingPost->alive == 1){
                        //tell facebook this live post needs a new scrape

                        $ch = curl_init();
                        // TODO: this shouldnt use a hardcoded "/post" in the path
                        //    canonical post url path should be a configur value for Blag
                        $params = array(
                            'id' => 'http://staging.warrantynowvoid.com/post/' . $existingPost->guid,
                            'scrape' => 'true'
                        );
                        curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com');
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        $result = curl_exec($ch);
                        curl_close($ch);
                    }

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