<?php

    $JACKED->loadDependencies(array('Syrup'));

?>

<script type="text/javascript">
    $(document).ready(function(){
        $('#saveuser').click(function(eo){
            if(!($('#inputPassword').val() == '') && ($('#inputPassword').val() == $("#inputPassword2").val())){
                return true;
            }else{
                eo.preventDefault();
                alert('Passwords do not match.');
                $('#inputPassword').focus();
                return false;
            }
        });
    });
</script>

<h2>New User</h2>

<form id="newUserForm" class="form-horizontal" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>module/admin">
    <input type="hidden" name="manage_handler" value="new-handler" />
    <fieldset>

        <div class="control-group">
            <label class="control-label" for="inputEmail">Email</label>
            <div class="controls">
                <input type="text" required class="input-xxlarge" name="inputEmail" id="inputEmail">
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="inputUsername">Username</label>
            <div class="controls">
                <input type="text" required class="input-xxlarge" name="inputUsername" id="inputUsername">
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="inputPassword">Password</label>
            <div class="controls">
                <input type="password" required class="input-xxlarge" name="inputPassword" id="inputPassword">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputPassword2">Password Again</label>
            <div class="controls">
                <input type="password" required class="input-xxlarge" name="inputPassword2" id="inputPassword2">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="inputFirstName">First Name</label>
            <div class="controls">
                <input type="text" class="input-xxlarge" name="inputFirstName" id="inputFirstName">
            </div>
        </div>


        <div class="control-group">
            <label class="control-label" for="inputLastName">Last Name</label>
            <div class="controls">
                <input type="text" class="input-xxlarge" name="inputLastName" id="inputLastName">
            </div>
        </div>


        <div class="form-actions pull-right span9">
            <button id="saveuser" type="submit" class="btn btn-success pull-right" style="margin-left:10px">Save</button>
            <a id="cancelButton" class="pull-right btn btn-danger" href="<?php echo $JACKED->admin->config->entry_point; ?>module/admin">Cancel</a>
        </div>
    </fieldset>
</form>