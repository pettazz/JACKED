<?php

    class ProductModel extends SyrupModel{

        const tableName = 'Product';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $name = array(SyrupField::VARCHAR, 255);
        protected $image = array(SyrupField::VARCHAR, 255);
        protected $max_quantity = array(SyrupField::INT, 3, False, 1);
        protected $description = array(SyrupField::TEXT, NULL, True, '');
        protected $cost = array(SyrupField::INT, 8, False, 0);
        protected $active = array(SyrupField::TINYINT, 1, True, 1);
        protected $tangible = array(SyrupField::TINYINT, 1, True, 1);

    }
    
?>