<?php

    class DatasBeardRowModel extends SyrupModel{

        const tableName = 'DatasBeardRow';

        protected $uuid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $Table = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'DatasBeardTable.uuid'));
        protected $edited = array(SyrupField::INT, 11, false);
        protected $alive = array(SyrupField::TINYINT, 1, true, 1);
        protected $content = array(SyrupField::BLOB);

    }
    
?>