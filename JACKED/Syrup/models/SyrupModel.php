<?php

    /**
     *  Base class for all Syrup Models to inherit from.
     */

    class SyrupModel extends SyrupDriver{

        public $_contentType = 'object';

        private $_fields = array();
        private $_primaryKey = array();

        private $_isNew;
        private $_isDirty;

        private $_constructing = true;

        public function __construct($config, $logr, $util, $data = NULL, $isNew = true){
            parent::__construct($config, $logr, $util, get_class($this));

            //the heart of jankiness
            foreach(get_class_vars(get_class($this)) as $fieldName => $fieldVal){
                //the mayor of jankville
                if(strpos($fieldName, '_') !== 0 && is_array($fieldVal)){
                    //create a new instance of the field with an array as constructor arguments 
                    $reflection = new ReflectionClass('SyrupField');
                    $this->$fieldName = $reflection->newInstanceArgs($fieldVal);

                    array_push($this->_fields, $fieldName);
                    if($this->$fieldName->isPrimaryKey){
                        $this->_primaryKey = array('name' => $fieldName, 'field' => $this->$fieldName);
                    }
                    //autogen fields
                    if(in_array('UUID', $this->$fieldName->extra)){
                        $this->$fieldName->setValue($util->uuid4());
                    }
                }
            }
            $this->_constructing = false;

            $this->_isNew = $isNew;
            $this->_isDirty = false; 

            if($data && is_array($data)){
                foreach($data as $dataFieldName => $dataFieldValue){
                    $this->$dataFieldName->setValue($dataFieldValue);
                }
                $this->_isDirty = true; 
            }
        }

        public function __set($key, $value){
            //constructor needs to be able to set anything it damn well pleases
            if(strpos($key, '_') !== 0){
                //this is a little janky, assumes all non-field prop names start with a _
                ////and everything else is a field
                if(in_array($key, $this->_fields)){
                    if($this->$key->isPrimaryKey){
                        throw new PrimaryKeyUnmodifiableException($key);
                    }else{
                        $this->$key->setValue($value);
                        $this->_isDirty = true;
                    }
                }else{
                    throw new UnknownModelFieldException($key);
                }
            }else{
                $this->$key = $value;
            }
        }

        public function __get($key){
            //see above __set() jankiness comment. also applies here.
            if(strpos($key, '_') !== 0){
                if($this->_constructing){
                    return $this->$key;
                }elseif(in_array($key, $this->_fields)){
                    return $this->$key->getValue();
                }else{
                    throw new UnknownModelFieldException($key);
                }
            }else{
                return $this->$key;
            }
        }

        public function getFields($skipPrimary = false){
            if($skipPrimary){
                $fields = array();

                return $fields;
            }else{
                return $this->_fields;
            }
        }

        public function getPrimaryKey(){
            return $this->_primaryKey;
        }

        public function getPrimaryKeyName(){
            $key = $this->_primaryKey;
            return $key['name'];
        }

    }

    class UnknownModelFieldException extends Exception{
        public function __construct($field, $code = 0, Exception $previous = null){
            $message = "Model does not have definition for field with name: `$field`.";
            
            parent::__construct($message, $code, $previous);
        }
    }

    class PrimaryKeyUnmodifiableException extends Exception{
        public function __construct($keyname, $code = 0, Exception $previous = null){
            $message = "Cannot change value of Primary Key: `$keyname`.";
            
            parent::__construct($message, $code, $previous);
        }
    }

    /**
     *  Base class for all fields to inherit from
     */

    class SyrupField{

        //right now these are just most of the MySQL field types, can be made better later.
        const TINYINT = 'tinyint';
        const INT = 'int';
        const BIGINT = 'bigint';
        const FLOAT = 'float';
        const DOUBLE = 'double';
        const DECIMAL = 'decimal';

        const DATE = 'date';
        const DATETIME = 'datetime';
        const TIMESTAMP = 'timestamp';

        const CHAR = 'char';
        const VARCHAR = 'varchar';
        const BLOB = 'blob';
        const TEXT = 'text';
        const LONGTEXT = 'longtext';
        const ENUM = 'enum';

        public $type;
        public $length;
        public $null;
        public $key;
        public $default;
        public $extra;
        public $comment;

        public $isPrimaryKey = false;
        public $isForeignKey = false;

        private $_value;
        
        public function __construct($type, $length = NULL, $null = NULL, $default = NULL, $key = NULL, $extra = NULL, $comment = NULL){
            $requiredLengthTypes = array(
                SyrupField::TINYINT, SyrupField::INT, SyrupField::BIGINT, SyrupField::FLOAT, SyrupField::DOUBLE, SyrupField::DECIMAL, SyrupField::CHAR, SyrupField::VARCHAR, SyrupField::ENUM
            );

            //not restricting to recognized types yet, maybe in the future 
            $this->type = $type;
            if(in_array($type, $requiredLengthTypes) && !$length){
                throw new MissingRequiredFieldParameterException('length');
            }
            $this->length = $length;
            $this->key = ($key? $key : '');
            //other types of keys need to be added here (if we care about them at all)
            switch($this->key){
                case 'PRI':
                    $this->isPrimaryKey = true;
                    break;
                case 'FK':
                    $this->isForeignKey = true;
                    break;
                default:
                    //nothin
                    break;
            }
            $this->null = ($null? true : false);
            if($this->null && $default === NULL){
                throw new MissingRequiredFieldParameterException('default');
            }
            $this->default = $default;
            $this->extra = $extra? $extra : array();
            $this->comment = $comment;

            if($this->default){
                $this->_value = $this->default;
            }else{
                $this->_value = NULL;
            }
        }

        public function getValue(){
            return $this->_value;
        }

        public function setValue($value){
            //TODO: add type restriction checks
            $this->_value = $value;
        }
    }


    class MissingRequiredFieldParameterException extends Exception{
        public function __construct($param, $code = 0, Exception $previous = null){
            $message = "Missing required field parameter: `$param`.";
            
            parent::__construct($message, $code, $previous);
        }
    }
?>