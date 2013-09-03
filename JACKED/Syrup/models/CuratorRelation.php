<?php

    class CuratorRelationModel extends SyrupModel{

        const tableName = 'CuratorRelation';

        protected $Curator = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $target = array(SyrupField::VARCHAR, 64);

    }
    
?>