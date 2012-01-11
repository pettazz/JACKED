<?php 
    if(!isset($JACKED)){
        include('index.php');
        exit();
    }
?>




          <div class="row">
              <div class="hero-unit span-one-third offset4">
              
                  <h1>Login</h1>
              
                      
                      <form class="form-stacked" method="POST" action="/JACKED/admin/login-handler.php">
                      
                        <fieldset>
                          <legend></legend>
                          <div class="clearfix">
                          <?php
                          if(isset($_GET['error'])){
                              echo'
                            <div data-alert="alert" id="hero-alert" class="alert-message error fade in">
                                <a href="#" class="close">×</a>
                                <p><strong>Login failed!</strong> ' . $_GET['error'] .  '</p>
                            </div>';
                          }
                      ?>
                            <label for="xlInput3">Username</label>
                            <div class="input">
                              <input type="text" size="30" name="username" id="username" class="xlarge">
                            </div>
                            <label for="xlInput3">Password</label>
                            <div class="input">
                              <input type="password" size="30" name="password" id="password" class="xlarge">
                            </div>
                          </div><!-- /clearfix -->
                        </fieldset>
                        <div class="actions">
                          <button class="btn primary" type="submit">Login</button>
                        </div>
                      </form>



            </div>
        </div>