<?php

    class BlagModel extends SyrupModel{

        const tableName = 'Blag';
        const contentType = Syrup::CONTENT_OBJECT;

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', NULL, array('UUID'));
        protected $author = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'User.guid'));
        protected $posted = array(SyrupField::INT, 11, false);
        protected $category = array(SyrupField::VARCHAR, 64, false, NULL, 'FK', array('hasOne' => 'BlagCategory.guid'));
        protected $alive = array(SyrupField::TINYINT, 1, true, 1);
        protected $title = array(SyrupField::VARCHAR, 255);
        protected $headline = array(SyrupField::TEXT, NULL, true, '');
        protected $content = array(SyrupField::TEXT);

    }
    
?>