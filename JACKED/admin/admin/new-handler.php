<?php 

    try{
        $JACKED->loadDependencies(array('MySQL', 'Flock'));

        if(!$JACKED->Util->validateEmail($_POST['inputEmail'])){
            throw new Exception('Invalid email address provided.');
        }

        $details = array();
        if(isset($_POST['inputFirstname']) && !(trim($_POST['inputFirstname']) == '')){
            $details['first_name'] = trim($_POST['inputFirstname']);
        }
        if(isset($_POST['inputLastName']) && !(trim($_POST['inputLastName']) == '')){
            $details['last_name'] = trim($_POST['inputLastName']);
        }

        $uid = $JACKED->Flock->createUser($_POST['inputUsername'], $_POST['inputEmail'], $_POST['inputPassword'], $details);

        $JACKED->MySQL->insert($JACKED->admin->config->dbt_users, array('id' => $JACKED->Util->uuid4(), 'User' => $uid));

        $login_url = substr($JACKED->config->base_url, 0, strlen($JACKED->config->base_url) -1) . $JACKED->admin->config->entry_point;

        $to = $_POST['inputEmail'];
        $subject = 'Welcome to your ' . $JACKED->config->client_name . ' Admin account';
        $message = '<h3>Another admin has created an admin account for you.</h3><p>You can login at <a href="' . $login_url . '">' . $login_url . '</a> with the following information.</p><p>Username: <strong>' . $_POST['inputUsername'] . '</strong><br />Password: ' . $_POST['inputPassword'] . '</strong></p>';
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-Type: text/html; charset=utf-8;' . "\r\n";
        $headers .= 'From: jackedbot' . $JACKED->config->email_url . "\r\n";
        $sentMail = mail($to, $subject, $message, $headers);

        echo '<div class="alert alert-success alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p>User was created successfully. </p>
        </div>';

    }catch(Exception $e){
        echo '<div class="alert alert-error alert-block">
                  <a href="#" class="close" data-dismiss="alert">&times;</a>
                  <p><strong>Error: </strong>"' . $e->getMessage() .  '" </p>
        </div>';
    }



?>