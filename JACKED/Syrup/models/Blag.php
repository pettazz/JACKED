<?php

    class BlagModel extends SyrupModel{

        const tableName = 'BlagPost';

        public $guid = new SyrupField(SyrupField::VARCHAR, 64, false, NULL, 'PRI');
        public $author = new SyrupField(SyrupField::VARCHAR, 64, false, NULL, 'FK');
        public $posted = new SyrupField(SyrupField::INT, 11, false);
        public $alive = new SyrupField(SyrupField::TINYINT, 1, true, 1);
        public $title = new SyrupField(SyrupField::VARCHAR, 255);
        public $headline = new SyrupField(SyrupField::TEXT, NULL, true, '');
        public $content = new SyrupField(SyrupField::TEXT);

    }
    
?>