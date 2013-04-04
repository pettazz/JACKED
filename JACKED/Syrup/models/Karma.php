<?php

    class KarmaModel extends SyrupModel{

        const tableName = 'Karma';
        const contentType = Syrup::CONTENT_META;

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $target = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', NULL, array('meta_target_UUID'));
        protected $source = array(SyrupField::VARCHAR, 64, false, NULL);//, 'FK', array('hasOne' => 'User.guid'));
        protected $weight = array(SyrupField::INT, 10);
        protected $timestamp = array(SyrupField::INT, 11);
        
    }
    
?>