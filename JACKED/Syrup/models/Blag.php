<?php

    class BlagModel extends SyrupModel{

        const tableName = 'BlagPost';

        protected $guid = array(SyrupField::VARCHAR, 64, false, NULL, 'PRI', array('UUID'));
        protected $author = array(SyrupField::VARCHAR, 64, false, NULL, 'FK');
        protected $posted = array(SyrupField::INT, 11, false);
        protected $alive = array(SyrupField::TINYINT, 1, true, 1);
        protected $title = array(SyrupField::VARCHAR, 255);
        protected $headline = array(SyrupField::TEXT, NULL, true, '');
        protected $content = array(SyrupField::TEXT);

    }
    
?>