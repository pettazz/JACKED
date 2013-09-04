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