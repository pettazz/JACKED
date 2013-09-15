<?php
    try{

        if(trim($_POST['guid']) == ''){
            throw new Exception('No post id defined.');
        }
        $guid = $_POST['guid'];

        $JACKED->loadDependencies(array('Syrup', 'Curator'));
        $tags = $JACKED->Curator->getAllTags();

        $existingEdit = FALSE;
        if(isset($_POST['existingEdit']) && trim($_POST['existingEdit']) != ''){
            $existingEdit = TRUE;
            $post = array(
                'guid' => $_POST['guid'],
                'title' => isset($_POST['inputTitle'])? $_POST['inputTitle'] : '',
                'headline' => isset($_POST['inputHeadline'])? $_POST['inputHeadline'] : '',
                'content' => isset($_POST['inputContent'])? $_POST['inputContent'] : '',
                'category' => isset($_POST['inputCategory'])? $_POST['inputCategory'] : '',
                'Author' => isset($_POST['inputAuthor'])? $_POST['inputAuthor'] : '',
                'overrideAuthor' => isset($_POST['inputOverrideAuthor'])? $_POST['inputOverrideAuthor'] : '',
                'posted' => isset($_POST['inputDate'])? $_POST['inputDate'] : '',
                'overrideDate' => isset($_POST['inputOverrideDate'])? $_POST['inputOverrideDate'] : ''
            );
            $postTagsString = isset($_POST['inputTags'])? $_POST['inputTags'] : '';
        }else{
            $post = $JACKED->Syrup->Blag->findOne(array('guid' => $guid));
            if(!$post){
                throw new Exception('Post not found.');
            }

            $postTagsString = '';
            foreach($post->Curator as $tag){
                $postTagsString .= $tag->name . ',';
            }
            $postTagsString = rtrim($postTagsString, ',');
        }
    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.editpost', $e->getMessage());
        include('posts.php');
    }

?>
<link href="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/select2/select2.css" rel="stylesheet" />
<script type="text/javascript" src="<?php echo $JACKED->admin->config->entry_point; ?>assets/js/select2/select2.min.js"></script>
<script type="text/javascript">
    
    var editor;
    var editorStorageName = 'JACKED_Blag_edit_<?php echo $guid; ?>';
    var autoDraftExists = !(localStorage.getItem(editorStorageName) === null);

    if(autoDraftExists){
        draft = JSON.parse(localStorage.getItem(editorStorageName));
        draftDate = new Date(draft[editorStorageName]['modified']);
        if(confirm('You have an existing draft for this post that was autosaved on ' + draftDate.toLocaleString() + '. Would you like to load it or cancel the draft and load the saved post?')){

        }

    }

    var opts = {
        container: 'editoroverlay',
        textarea: 'inputContent',
        basePath: '',
        clientSideStorage: true,
        localStorageName: editorStorageName,
        file: {
            name: editorStorageName,
        },
        useNativeFullscreen: true,
        parser: marked,
        theme: {
            base: '<?php echo $JACKED->admin->config->entry_point; ?>assets/js/EpicEditor/themes/base/epiceditor.css',
            preview: '<?php echo $JACKED->admin->config->entry_point; ?>assets/js/EpicEditor/themes/preview/wnv.css',
            editor: '<?php echo $JACKED->admin->config->entry_point; ?>assets/js/EpicEditor/themes/editor/epic-dark.css'
        },
        button: {
            preview: true,
            fullscreen: true,
            bar: "auto"
        },
        focusOnLoad: false,
        shortcut: {
            modifier: 18,
            fullscreen: 70,
            preview: 80
        },
        string: {
            togglePreview: 'Toggle Preview Mode',
            toggleEdit: 'Toggle Edit Mode',
            toggleFullscreen: 'Enter Fullscreen'
        },
        autogrow: {
            minHeight: 500
        }
    }

    $(document).ready(function(){
        editor = new EpicEditor(opts);
        editor.load();

        $("#cancelButton").click(function(eo){
            var confirmCancel = confirm('Discard your changes to this post?');
            if(confirmCancel){
                window.onbeforeunload = null;
                editor.unload();
                $('#inputContent').val('');
                editor.remove(editorStorageName);
                localStorage.removeItem(editorStorageName);
            }else{
                eo.preventDefault();
                return false;
            }
        });

        $("#savepost").click(function(eo){
            window.onbeforeunload = null;
            editor.remove(editorStorageName);
            localStorage.removeItem(editorStorageName);
            $("#saveType").val('live');
        });

        $("#savedraft").click(function(eo){
            window.onbeforeunload = null;
            editor.remove(editorStorageName);
            localStorage.removeItem(editorStorageName);
            $("#saveType").val('draft'); 
        });

        $('#inputTags').select2({
            tags: [<?php
                    if($tags){
                        foreach($tags as $tag){
                            echo "'" . addslashes($tag['name']) . "', ";
                        }
                    }
                    ?>],
            tokenSeparators: [","]
        });
    });

    var confirmOnPageExit = function(winev){
        winev = winev || window.event;

        var message = 'You haven\'t saved your post yet. Do you want to leave without saving?';

        // For IE6-8 and Firefox prior to version 4
        if(winev){
            winev.returnValue = message;
        }

        // For Chrome, Safari, IE8+ and Opera 12+
        return message;
    };
    window.onbeforeunload = confirmOnPageExit;

</script>

<h2>Edit Post</h2>

<?php
    if($JACKED->Sessions->check('admin.error.editpost')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.editpost') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.editpost');
    }
?>

<form class="form-horizontal" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/Blag">
    <input type="hidden" name="manage_handler" value="post-edit-handler" />
    <input type="hidden" name="editAction" value="save" />
    <input type="hidden" name="existingEdit" value="true" />
    <input type="hidden" name="guid" value="<?php echo $guid; ?>" />
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="inputTitle">Title</label>
            <div class="controls">
                <input type="text" class="input-xxlarge" name="inputTitle" id="inputTitle" value="<?php echo ($existingEdit? $post['title'] : $post->title); ?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputHeadline">Headline/Preview Text</label>
            <div class="controls">
                <textarea rows="6" class="input-xxlarge" name="inputHeadline" id="inputHeadline"><?php echo ($existingEdit? $post['headline'] : $post->headline); ?></textarea>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="inputContent">Content</label>
            <div class="controls">
                <span class="help-block">All content is in Markdown: <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet" target="_blank">Syntax Cheat Sheet</a></span><br />
                <span class="help-block">Use this at the beginning of your content to set the post image header:<br /> <code>&lt;img class="headliner topbar-type" src="/assets/img/lol/whatever.png" title="Hover text here." /&gt;</code> <br />And be sure to change the "type" to the post category, like topbar-articles. If this is a comic, this should be the comic image and only content of the post. YouTube Videos can also be added using the iframe embed exactly as copied from YouTube's share.<br /><br />
                <textarea rows="6" class="input-xxlarge" style="display:none;" name="inputContent" id="inputContent"><?php echo ($existingEdit? $post['content'] : $markdown->toMarkdown($post->content)); ?></textarea>
                <div id="editoroverlay"></div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputCategory">Category</label>
            <div class="controls">
                <select name="inputCategory" id="inputCategory">
                    <?php
                        $cats = $JACKED->Syrup->BlagCategory->find();
                        foreach($cats as $cat){
                            if((!$existingEdit && $cat->guid == $post->category->guid) || ($existingEdit && $cat->guid == $post['category'])){
                                echo '<option selected value="' . $cat->guid . '">' . $cat->name . "</option>\n";
                            }else{
                                echo '<option value="' . $cat->guid . '">' . $cat->name . "</option>\n";
                            }
                        }
                    ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputTags">Tags</label>
            <div class="controls">
                <input type="text" id="inputTags" name="inputTags" class="input-xxlarge" value="<?php echo $postTagsString; ?>" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputAuthor">Author</label>
            <div class="controls">
                <input type="text" id="inputAuthor" disabled name="inputAuthor" class="input-xxlarge" value="<?php echo ($existingEdit? $post['Author'] : $post->author->username); ?>" />
            </div>
            <div class="controls">
                <input type="checkbox" id="inputOverrideAuthor" name="inputOverrideAuthor" value="true" <?php echo (($existingEdit && $post['overrideAuthor'] == 'true')? 'checked' : ''); ?> /> Update Author to current user: <span class="label label-info"><?php echo $JACKED->Sessions->read('auth.admin.user'); ?></span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputDate">Date/Time Posted</label>
            <div class="controls">
                <input type="text" id="inputDate" disabled name="inputDate" class="input-xxlarge" value="<?php echo ($existingEdit? date("F j Y, g:i a", $post['posted']) : date("F j Y, g:i a", $post->posted)); ?>" />
            </div>
            <div class="controls">
                <input type="checkbox" id="inputOverrideDate" name="inputOverrideDate" value="true" <?php echo (($existingEdit && $post['overrideDate'] == 'true')? 'checked' : ''); ?> /> Update posted date and time to Now
            </div>
        </div>


        <div class="form-actions pull-right span9">
            <input type="hidden" id="saveType" name="saveType" />
            <button id="savepost" type="submit" class="btn btn-success pull-right" style="margin-left:10px">Save and Post</button>
            <button id="savedraft" type="submit" class="btn btn-warning pull-right" style="margin-left:10px">Save as Draft</button>
            <a id="cancelButton" class="pull-right btn btn-danger" href="<?php echo $JACKED->admin->config->entry_point; ?>module/Blag">Cancel</a>
        </div>
    </fieldset>
</form>