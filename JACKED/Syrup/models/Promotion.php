<?php

    class PromotionModel extends SyrupModel{

        const tableName = 'Promotion';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $name = array(SyrupField::VARCHAR, 255);
        protected $description = array(SyrupField::TEXT, NULL, True, '');
        protected $value = array(SyrupField::INT, 8, False, 0);
        protected $active = array(SyrupField::TINYINT, 1, True, 1);

    }
    
?>