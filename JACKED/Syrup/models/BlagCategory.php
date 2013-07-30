<?php

    class BlagCategoryModel extends SyrupModel{

        const tableName = 'BlagCategory';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $name = array(SyrupField::VARCHAR, 255);

    }
    
?>