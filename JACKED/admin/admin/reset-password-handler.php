<?php 

    try{
        $JACKED->loadDependencies(array('Flock', 'Syrup', 'Testur'));

        $user = $JACKED->Syrup->User->findOne(array('guid' => $_POST['guid']));

        //already has an easy interface to Markov
        // http://xkcd.com/936/
        $newPass = '';
        for($iter = 1; $iter < 5; $iter++){
            $newPass .= trim($JACKED->Testur->generateWord(4, 10));
        }
        //hopefully not correcthorsebatterystaple

        $done = $JACKED->Flock->updateUserPassword($user->guid, $newPass);

        //TODO: be less shitty
        $to = $user->email;
        $subject = 'Your ' . $JACKED->config->client_name . ' Admin password was reset';
        $message = '<h3>Another admin has reset your password.</h3><p>This affects both your Admin and site login.</p><p>Your new password is <strong>' . $newPass . '<strong></p>';
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-Type: text/html; charset=utf-8;' . "\r\n";
        $headers .= 'From: jackedbot' . $JACKED->config->email_url . "\r\n";
        $sentMail = mail($to, $subject, $message, $headers);

        $result = 'User password was reset successfully. New password: <strong>' . $newPass . '</strong>';
        if($sentMail){
            $result .= '<br />An email with the new password was sent to the user.';
        }else{
            $result .= '<br />An email with the new password <em><strong>was not successfully</strong></em> sent to the user.';
        }

        $JACKED->Sessions->write('admin.success.edituser', $result);

    }catch(Exception $e){
        $JACKED->Sessions->write('admin.error.edituser', '' . $e->getMessage() .  ' <em>(' . $e->getFile() . ':' . $e->getLine() . ')</em>:</p><p><pre><code>' . $e->getTraceAsString() . '</code></pre></p>');
    }

    include('users.php');

?>