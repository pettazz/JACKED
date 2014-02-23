<?php

    class ShippingAddressModel extends SyrupModel{

        const tableName = 'ShippingAddress';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $User = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'User.guid'));
        protected $recipient_name = array(SyrupField::VARCHAR, 50, false);
        protected $line1 = array(SyrupField::VARCHAR, 100, false);
        protected $line2 = array(SyrupField::VARCHAR, 100, false);
        protected $city = array(SyrupField::VARCHAR, 50, false);
        protected $postal_code = array(SyrupField::VARCHAR, 20, false);
        protected $state = array(SyrupField::VARCHAR, 2, false);
        protected $phone = array(SyrupField::VARCHAR, 50, true, NULL);

    }
    
?>