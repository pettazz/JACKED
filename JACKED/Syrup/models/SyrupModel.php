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

        public function __construct($config, $logr, $isNew = true){
            parent::__construct($config, $logr);

            $this->_isNew = $isNew;
            $this->_isDirty = false; 

            //internally collect all fields and determine PRI key
            foreach(get_class_vars(get_class($this)) as $field => $val){
                //see __set() jankiness comment
                if(strpos($field, '_') !== 0){
                    if($this->$field->isPrimaryKey()){
                        $this->_primaryKey = array("name" => $field, "field" => $this->$field);
                    }
                    $this->fields[$field] = $this->$field;
                }
            }

            $this->_constructing = false;
        }

        public function __set($key, $value){
            //constructor needs to be able to set anything it damn well pleases
            if($this->_constructing){
                $this->$key = $value;
            }elseif(strpos($key, '_') !== 0){
                //this is a little janky, assumes all non-field prop names start with a _
                ////and everything else is a field
                if(array_key_exists($key, $this->_fields)){
                    if($this->$key->_isPrimaryKey){
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
                if(array_key_exists($key, $this->_fields)){
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
            $this->extra = $extra;
            $this->comment = $comment;

            if($this->default){
                $this->_value = $this->default;
            }else{
                $this->_value = NULL;
            }
        }

        public function getValue(){
            return $this->$_value;
        }

        public function setValue($value){
            //TODO: add type restriction checks
            return $this->_value = $value;
        }
    }


    class MissingRequiredFieldParameterException extends Exception{
        public function __construct($param, $code = 0, Exception $previous = null){
            $message = "Missing required field parameter: `$param`.";
            
            parent::__construct($message, $code, $previous);
        }
    }
?>