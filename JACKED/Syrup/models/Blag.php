<?php

    class BlagModel extends SyrupModel{

        const tableName = 'BlagPost';

        public function __construct($config, $logr){
            parent::__construct($config, $logr);
            
            $this->$guid = new SyrupField(SyrupField::VARCHAR, 64, false, NULL, 'PRI');
            $this->$author = new SyrupField(SyrupField::VARCHAR, 64, false, NULL, 'FK');
            $this->$posted = new SyrupField(SyrupField::INT, 11, false);
            $this->$alive = new SyrupField(SyrupField::TINYINT, 1, true, 1);
            $this->$title = new SyrupField(SyrupField::VARCHAR, 255);
            $this->$headline = new SyrupField(SyrupField::TEXT, NULL, true, '');
            $this->$content = new SyrupField(SyrupField::TEXT);
        }

    }
    
?>