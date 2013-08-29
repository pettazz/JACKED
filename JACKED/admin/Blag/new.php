<?php

    $JACKED->loadDependencies(array('Syrup', 'Curator'));

    $tags = $JACKED->Curator->getAllTags();

    if(isset($_POST['saveType'])){
        $incomingPost = TRUE;
        if(isset($_POST['inputCategory'])){
            $inputCategory = stripslashes($_POST['inputCategory']);
        }else{
            $inputCategory = FALSE;
        }
        if(isset($_POST['inputHeadline'])){
            $inputHeadline = stripslashes($_POST['inputHeadline']);
        }else{
            $inputHeadline = FALSE;
        }
        if(isset($_POST['inputContent'])){
            $inputContent = stripslashes($_POST['inputContent']);
        }else{
            $inputContent = FALSE;
        }
        if(isset($_POST['inputTags'])){
            $inputTags = stripslashes($_POST['inputTags']);
        }else{
            $inputTags = FALSE;
        }
        if(isset($_POST['inputTitle'])){
            $inputTitle = stripslashes($_POST['inputTitle']);
        }else{
            $inputTitle = FALSE;
        }

    }else{
        $incomingPost = FALSE;
        $inputCategory = FALSE;
        $inputHeadline = FALSE;
        $inputContent = FALSE;
        $inputTags = FALSE;
        $inputTitle = FALSE;
    }

?>
<link href="/admin/assets/js/select2/select2.css" rel="stylesheet" />
<script type="text/javascript" src="/admin/assets/js/select2/select2.min.js"></script>
<script type="text/javascript">
    
    var editor;
    var editorStorageName = 'JACKED_Blag_new';
    var autoDraftExists = !(localStorage.getItem(editorStorageName) === null);

    if(<?php echo $incomingPost? 'true' : 'false'; ?> && autoDraftExists){
        localStorage.removeItem(editorStorageName);
    }

    var opts = {
        container: 'editoroverlay',
        textarea: 'inputContent',
        basePath: '',
        clientSideStorage: true,
        localStorageName: editorStorageName,
        file: {
            name: editorStorageName
        },
        useNativeFullscreen: true,
        parser: marked,
        theme: {
            base: '/admin/assets/js/EpicEditor/themes/base/epiceditor.css',
            preview: '/admin/assets/js/EpicEditor/themes/preview/github.css',
            editor: '/admin/assets/js/EpicEditor/themes/editor/epic-dark.css'
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
            var confirmCancel = confirm('Discard this post?');
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

<h2>New Post</h2>

<form class="form-horizontal" method="POST" action="/admin/module/Blag">
    <input type="hidden" name="manage_handler" value="new-handler" />
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="inputTitle">Title</label>
            <div class="controls">
                <input type="text" class="input-xxlarge" name="inputTitle" id="inputTitle" value="<?php echo $inputTitle? $inputTitle : ''; ?>" placeholder="New Post">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputHeadline">Headline/Preview Text</label>
            <div class="controls">
                <textarea rows="6" class="input-xxlarge" name="inputHeadline" id="inputHeadline"><?php echo $inputHeadline? $inputHeadline : "This is where we would put a brief intro or teaser type thing of the article to convince people that it's cool and they should read it. The container won't expand dynamically at all, so these should overall be kept relatively short. Because otherwise the text will overflow down into the tabs below, and my shiny css will actually just truncate it at an awkward point."; ?></textarea>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="inputContent">Content</label>
            <div class="controls">
                <textarea rows="6" class="input-xxlarge" style="display:none;" name="inputContent" id="inputContent"><?php echo $inputContent? $inputContent : ''; ?></textarea>
                <div id="editoroverlay"></div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputCategory">Category</label>
            <div class="controls">
                <select name="inputCategory" id="inputCategory">
                    <?php
                        $cats = $JACKED->Syrup->BlagCategory->find();
                        if($inputCategory){
                            $selected = $_POST['inputCategory'];
                        }else{
                            $selected = FALSE;
                        }
                        foreach($cats as $cat){
                            echo '<option value="' . $cat->guid . '"' . (($inputCategory && $selected && $selected == $cat->guid)? 'selected' : '') . '>' . $cat->name . "</option>\n";
                        }
                    ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputTags">Tags</label>
            <div class="controls">
                <input type="text" id="inputTags" name="inputTags" class="input-xxlarge" value="<?php echo $inputTags? $inputTags : ''; ?>" />
            </div>
        </div>

        <div class="form-actions pull-right span9">
            <input type="hidden" id="saveType" name="saveType" />
            <button id="savepost" type="submit" class="btn btn-success pull-right" style="margin-left:10px">Save and Post</button>
            <button id="savedraft" type="submit" class="btn btn-warning pull-right" style="margin-left:10px">Save as Draft</button>
            <a id="cancelButton" class="pull-right btn btn-danger" href="/admin/module/Blag">Cancel</a>
        </div>
    </fieldset>
</form>