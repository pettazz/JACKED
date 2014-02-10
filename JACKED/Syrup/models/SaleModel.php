<?php

    class SaleModel extends SyrupModel{

        const tableName = 'Sale';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $User = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'User.guid'));
        protected $Ticket = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'Ticket.guid'));
        protected $Product = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'Product.guid'));
        protected $total = array(SyrupField::INT, 8, False, 0);
        protected $quantity = array(SyrupField::INT, 5, False, 1);
        protected $payment = array(SyrupField::ENUM, "ENUM('PAYPAL', 'STRIPE', 'DOGE')", False);
        protected $confirmed = array(SyrupField::TINYINT, 1, True, 0);
        protected $shipped = array(SyrupField::TINYINT, 1, True, 0);
        protected $tracking = array(SyrupField::VARCHAR, 255);

    }
    
?>