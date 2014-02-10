<?php

    class TicketModel extends SyrupModel{

        const tableName = 'Ticket';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $User = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'User.guid'));
        protected $Promotion = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'Promotion.guid'));
        protected $valid = array(SyrupField::TINYINT, 1, True, 1);
        protected $redeemed = array(SyrupField::TINYINT, 1, True, 0);

    }
    
?>