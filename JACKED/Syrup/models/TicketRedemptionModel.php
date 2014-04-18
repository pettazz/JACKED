<?php

    class TicketRedemptionModel extends SyrupModel{

        const tableName = 'TicketRedemption';

        protected $Ticket = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI');
        protected $Sale = array(SyrupField::VARCHAR, 64);

    }
    
?>