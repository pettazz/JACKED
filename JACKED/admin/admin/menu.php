<div class="container">
    <div id="menuitems" class="row">
        <div class="">
            <h2>Add User</h2>
            <p>Add a new Admin User.</p>
            <a class="btn large" href="<?php echo $JACKED->admin->config->entry_point; ?>module/admin?manage_section=new">Create</a>
        </div>
        
        <div class="">
            <h2>Reset Password</h2>
            <p>Reset another Admin User's password.</p>
            <a class="btn large" href="<?php echo $JACKED->admin->config->entry_point; ?>module/admin?manage_section=password_reset">Reset</a>
        </div>
    </div>
</div>