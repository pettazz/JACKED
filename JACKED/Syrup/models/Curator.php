<?php

    class CuratorModel extends SyrupModel{

        const tableName = 'Curator';
        const relationTable = 'CuratorRelation';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $name = array(SyrupField::VARCHAR, 255, False);
        protected $usage = array(SyrupField::INT, 11, True, 0);
    }
    
?>