<?php

    $JACKED->loadDependencies(array('MySQL'));

    $admins = $JACKED->MySQL->query("SELECT User.* FROM User, " . $JACKED->admin->config->dbt_users . " WHERE User.guid = " . $JACKED->admin->config->dbt_users . ".User");

?>

<?php
    if($JACKED->Sessions->check('admin.error.edituser')){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $JACKED->Sessions->read('admin.error.edituser') .  '" </p>
        </div>';
        $JACKED->Sessions->delete('admin.error.edituser');
    }

    if($JACKED->Sessions->check('admin.success.edituser')){
        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>' . $JACKED->Sessions->read('admin.success.edituser') .  ' </p>
        </div>';
        $JACKED->Sessions->delete('admin.success.edituser');
    }

?>

<h3>Admin Users</h3>

<table class="table table-hover">
    <thead>
        <tr>
            <th width="130px">id</th>
            <th>Username</th>
            <th>Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>

<?php
    if($admins){
        foreach($admins as $admin){
            echo '<tr>';
            echo '<td>' . $admin['guid'] . '</td>';
            echo '<td>' . $admin['username'] . '</td>';
            echo '<td>' . $admin['email'] . '</td>';
            echo '<td>' . $admin['first_name'] . '</td>';
            echo '<td>' . $admin['last_name'] . '</td>';
            echo '<td><form method="POST" action="' . $JACKED->admin->config->entry_point . 'module/admin">
                    <input type="hidden" name="manage_handler" value="reset-password-handler" />
                    <input type="hidden" name="guid" value="' . $admin['guid'] . '" />
                    <button class="resetbutton btn-small btn-warning btn" type="submit" title="Reset Password"><i class="icon-refresh"></i></a>
                  </form></td>';
            echo '</tr>';
        }
    }
?>

    </tbody>
</table>