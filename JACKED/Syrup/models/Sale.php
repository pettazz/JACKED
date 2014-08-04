<?php

    class SaleModel extends SyrupModel{

        const tableName = 'Sale';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $User = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'User.guid'));
        protected $ShippingAddress = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'ShippingAddress.guid'));
        protected $Product = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'Product.guid'));
        protected $dimensions = array(SyrupField::TEXT);
        protected $timestamp = array(SyrupField::INT, 10, False);
        protected $total = array(SyrupField::INT, 8, False, 0);
        protected $converted_total = array(SyrupField::INT, 15, True, 0);
        protected $quantity = array(SyrupField::INT, 5, False, 1);
        protected $payment = array(SyrupField::ENUM, "ENUM('PAYPAL', 'STRIPE', 'DOGE')", False);
        protected $confirmed = array(SyrupField::TINYINT, 1, True, 0);
        protected $IPN_timestamp = array(SyrupField::INT, 10, True, 0);
        protected $shipped = array(SyrupField::TINYINT, 1, True, 0);
        protected $tracking = array(SyrupField::VARCHAR, 255);
        protected $external_transaction_id = array(SyrupField::VARCHAR, 255);

    }
    
?>