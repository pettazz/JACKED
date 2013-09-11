<?php

    $JACKED->loadDependencies(array('Syrup', 'Curator'));

    $drafts = $JACKED->Syrup->Blag->find(array('alive' => 0), array('field' => 'posted', 'direction' => 'DESC'));
    $lives = $JACKED->Syrup->Blag->find(array('alive' => 1), array('field' => 'posted', 'direction' => 'DESC'));

?>

<script type="text/javascript">

    $(document).ready(function(){
        $('button.postdelete').click(function(eo){
            if(confirm('Permanently delete this post?')){
                $(this).siblings('input[name="editAction"]').val('delete');
            }else{
                return false;
            }
        });
        $('button.postedit').click(function(eo){
            $(this).siblings('input[name="editAction"]').val('edit');
        });
    });

</script>


<h2>Manage Posts</h2>

<?php
    if($JACKED->Sessions->check('admin.error.editpost')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.editpost') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.editpost');
    }

    if($JACKED->Sessions->check('admin.success.editpost')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.editpost') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.editpost');
    }

?>

<h3>Drafts</h3>

<table class="table table-hover">
    <thead>
        <tr>
            <th width="130px">id</th>
            <th>Title</th>
            <th>Author</th>
            <th>Saved on</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    if($drafts){
        foreach($drafts as $draft){
            echo '<tr>';
            echo '<td><a href="/post/' . $draft->guid . '" target="_blank">' . $draft->guid . '</a></td>';
            echo '<td>' . $draft->title . '</td>';
            echo '<td>' . $draft->author->username . '</td>';
            echo '<td>' . date("F j Y, g:i a", $draft->posted) . '</td>';
            echo '<td>
            <form method="POST" action="' . $JACKED->admin->config->entry_point . 'module/Blag">
                <input type="hidden" name="manage_handler" value="post-edit-handler" />
                <input type="hidden" name="guid" value="' . $draft->guid . '" />
                <input type="hidden" name="editAction" />
                <button class="btn btn-warning postedit"><i class="icon-edit"></i></button>  
                <button class="btn btn-danger postdelete"><i class="icon-trash"></i></button>
            </form>
            </td>';
            echo '</tr>';
        }
    }
?>

    </tbody>
</table>



<h3>Live Posts</h3>
<p class="lead">To take a currently live post down without deleting, edit it and save it as a draft. It will be saved in draft mode, and no longer be live on the site.</p>


<table class="table table-hover">
    <thead>
        <tr>
            <th width="130px">id</th>
            <th>Title</th>
            <th>Author</th>
            <th>Posted on</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    if($lives){
        foreach($lives as $post){
            echo '<tr>';
            echo '<td><a href="/post/' . $post->guid . '" target="_blank">' . $post->guid . '</a></td>';
            echo '<td>' . $post->title . '</td>';
            echo '<td>' . $post->author->username . '</td>';
            echo '<td>' . date("F j Y, g:i a", $post->posted) . '</td>';
            echo '<td>
            <form method="POST" action="' . $JACKED->admin->config->entry_point . 'module/Blag">
                <input type="hidden" name="manage_handler" value="post-edit-handler" />
                <input type="hidden" name="guid" value="' . $post->guid . '" />
                <input type="hidden" name="editAction" />
                <button class="btn btn-warning postedit"><i class="icon-edit"></i></button>  
                <button class="btn btn-danger postdelete"><i class="icon-trash"></i></button>
            </form>
            </td>';
            echo '</tr>';
        }
    }
?>

    </tbody>
</table>