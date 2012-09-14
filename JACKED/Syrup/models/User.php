<?php

    class UserModel extends SyrupModel{

        const tableName = 'User';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $email = array(SyrupField::VARCHAR, 255);
        protected $password = array(SyrupField::VARCHAR, 255);
        protected $username = array(SyrupField::VARCHAR, 255);
        protected $first_name = array(SyrupField::VARCHAR, 255);
        protected $last_name = array(SyrupField::VARCHAR, 255);

    }
    
?>