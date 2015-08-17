<?php

    class DatasBeardTableModel extends SyrupModel{

        const tableName = 'DatasBeardTable';

        protected $uuid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $name = array(SyrupField::VARCHAR, 255, false, NULL);
        protected $created = array(SyrupField::INT, 11, false);
        protected $alive = array(SyrupField::TINYINT, 1, true, 1);
        protected $schema = array(SyrupField::BLOB);

    }
    
?>