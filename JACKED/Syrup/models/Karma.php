<?php

    class KarmaModel extends SyrupModel{

        const tableName = 'Karma';

        public $guid = new SyrupField(SyrupField::VARCHAR, 64, false, NULL, 'PRI');
        public $target = new SyrupField(SyrupField::VARCHAR, 64, false, NULL, 'FK');
        public $source = new SyrupField(SyrupField::VARCHAR, 64, false, NULL, 'FK');
        public $weight = new SyrupField(SyrupField::INT, 10);
        public $guid = new SyrupField(SyrupField::INT, 11);
        
    }
    
?>