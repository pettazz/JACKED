<?php 
    if(!isset($JACKED)){
        include('index.php');
        exit();
    }
?>


        <div class="row">
            <div class="hero-unit span-one-third offset4">
              
                <h1>Login</h1>
                      
                <form class="form-stacked" method="POST" action="<?php echo $JACKED->admin->config->entry_point; ?>handler/login">
                  
                    <fieldset>
                        <legend></legend>
                        <div class="clearfix">
                            <?php
                                if($JACKED->Sessions->read('admin.loginform.error')){
                                    echo'
                            <div class="alert alert-error">
                                <a class="close" data-dismiss="alert" href="#">&times;</a>
                                <p><strong>Login failed!</strong><br /> ' . $JACKED->Sessions->read('admin.loginform.error') .  '</p>
                            </div>';
                                    $JACKED->Sessions->delete('admin.loginform.error');
                                }
                            ?>
                            <label for="username">Username</label>
                            <div class="input">
                                <input type="text" size="30" name="username" id="username" class="xlarge">
                            </div>
                            <label for="password">Password</label>
                            <div class="input">
                                <input type="password" size="30" name="password" id="password" class="xlarge">
                            </div>

                            <input type="hidden" id="qs" name="qs" value="<?php echo (isset($_POST['qs'])? $_POST['qs'] : $_SERVER['REQUEST_URI']); ?>">
                        </div><!-- /clearfix -->
                    </fieldset>
                    <div class="actions">
                        <button class="btn primary" type="submit">Login</button>
                    </div>
                </form>

            </div>
        </div>