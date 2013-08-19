<?php

    $JACKED->loadDependencies(array('Syrup', 'Curator'));

    $tags = $JACKED->Curator->getAllTags();

?>
<link href="/admin/assets/js/select2/select2.css" rel="stylesheet" />
<script type="text/javascript" src="/admin/assets/js/select2/select2.min.js"></script>
<script type="text/javascript">
    
    var editor;

    var opts = {
        container: 'editoroverlay',
        textarea: 'inputContent',
        basePath: '',
        clientSideStorage: true,
        localStorageName: 'JACKED_Blag_new',
        file: {
            name: "JACKED_Blag_new"
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
            editor.unload();
            $('#inputContent').val('');
            editor.remove('JACKED_Blag_new');
        });

        $("#savepost").click(function(eo){
            $("#saveType").val('live');
        });

        $("#savedraft").click(function(eo){
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

</script>

<h2>New Post</h2>

<form class="form-horizontal" method="POST" action="/admin/module/Blag">
    <input type="hidden" name="manage_handler" value="new-handler" />
    <fieldset>
        <div class="control-group">
            <label class="control-label" for="inputTitle">Title</label>
            <div class="controls">
                <input type="text" class="input-xxlarge" name="inputTitle" id="inputTitle" placeholder="New Post">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputHeadline">Headline/Preview Text</label>
            <div class="controls">
                <textarea rows="6" class="input-xxlarge" name="inputHeadline" id="inputHeadline">This is where we would put a brief intro or teaser type thing of the article to convince people that it's cool and they should read it. The container won't expand dynamically at all, so these should overall be kept relatively short. Because otherwise the text will overflow down into the tabs below, and my shiny css will actually just truncate it at an awkward point.</textarea>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="inputContent">Content</label>
            <div class="controls">
                <textarea rows="6" class="input-xxlarge" style="display:none;" name="inputContent" id="inputContent"></textarea>
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
                            echo '<option value="' . $cat->guid . '">' . $cat->name . "</option>\n";
                        }
                    ?>
                </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputTags">Tags</label>
            <div class="controls">
                <input type="text" id="inputTags" name="inputTags" class="input-xxlarge" />
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