<?php

    /**
     *  Base class for all Syrup Models to inherit from.
     */

    class SyrupModel extends SyrupDriver{

        //defaults to a non-magic object type
        const contentType = Syrup::OBJECT;

        private $_fields = array();
        private $_relations = array();
        private $_primaryKey = array();

        private $_isNew;
        private $_isDirty;

        private $_constructing = true;

        /**
        * Create a new SyrupModel instance. Fields are defined as protected class properties in the Model's class definition, as 
        * arrays of the values to be passed to the SyrupField constructor, in order (positional, not keyword). 
        * 
        * @param $config Configur A JACKED Configur instance containing the configuration for this Driver.
        * @param $logr Logr A JACKED Logr instance.
        * @param $util Util A JACKED Util instance.
        * @param $data Array [optional] Field values to be set upon creation. Defaults to each field's default value.
        * @param $isNew Boolean True = the instance will represent a new data object that has not been saved to the data source, False = loading an existing data object.
        * @return SyrupModel The newly created instance.
        */
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

                    //find and create automagic relations
                    if(in_array('relational_target_UUID', $this->$fieldName->extra)){
                        // this field is a target for some kind of meta content's relation, like Curator or Karma
                        // so we want to
                        // find the info on this relation from our site's Syrup config
                        if(array_key_exists($this::tableName, $config['auto_relations'])){
                            $relations = $config['auto_relations'][$this::tableName];
                            foreach($relations as $relation){
                                $relationName = $relation['model'];
                                $relationType = $relation['type'];
                                if(!class_exists($relationName . 'Model', false)){
                                    include(JACKED_MODULES_ROOT . $config['model_root'] . $relationName . '.php');
                                }
                                $this->$relationName = SyrupField::REL_PLACEHOLDER;
                                array_push($this->_fields, $relationName);
                                $this->_relations[$relationName] = array(
                                    'type' => $relationType . 'Foreign', 
                                    'field' => $relationName . '.guid', 
                                    'target' => $this::tableName . '.guid', 
                                );
                                if(!($relationType == 'hasOne' || $relationType == 'hasMany')){
                                    //i dunno what else there might be, but "handle it" for now
                                    $this->_logr->write('Only hasOne and hasMany automagic relations are supported right now.', Logr::LEVEL_WARNING, NULL);
                                }
                            }
                            
                        }else{
                            // we have nothing that wants to bind to this, so do nothing
                        }
                    }
                    //set up explicit relations
                    if(is_array($this->$fieldName->relation)){
                        $type = key($this->$fieldName->relation);
                        $this->_relations[$fieldName] = array('type' => $type, 'field' => $this->$fieldName->relation[$type]);
                        //we're going to need the models for the relations as well
                        $moduleName = explode('.', $this->$fieldName->relation[$type]);
                        $moduleName = $moduleName[0];
                        if(!class_exists($moduleName . 'Model', false)){
                            include(JACKED_MODULES_ROOT . $config['model_root'] . $moduleName . '.php');
                        }
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

        /**
        * Set an inaccessible property of this SyrupModel. All fields are inaccessible outside the object's inheritance, 
        * so we detect if this is an attempt to set a Field and calls the field's setValue() method. All non-field properties
        * begin with an underscore (_) so we assume that anything without one is a Field.
        * 
        * @param $key String Property name to be set.
        * @param $value mixed Property value to be set.
        */
        public function __set($key, $value){
            //constructor needs to be able to set anything it damn well pleases
            if($this->_constructing){
                $this->$key = $value;
            }elseif(strpos($key, '_') !== 0){
                //this is a little janky, assumes all non-field prop names start with a _
                ////and everything else is a field
                if(in_array($key, $this->_fields)){
                    if(is_object($value) && is_subclass_of($value, 'SyrupModel')){
                        $this->$key = $value;
                    }else{
                        if($this->$key->isPrimaryKey){
                            throw new PrimaryKeyUnmodifiableException($key);
                        }
                        $this->$key->setValue($value);
                    }
                    $this->_isDirty = true;
                }else{
                    throw new UnknownModelFieldException($key);
                }
            }else{
                $this->$key = $value;
            }
        }

        /**
        * Get an inaccessible property of this SyrupModel. All fields are inaccessible outside the object's inheritance, 
        * so we detect if this is an attempt to get a Field and calls the field's getValue() method. All non-field properties
        * begin with an underscore (_) so we assume that anything without one is a Field.
        * 
        * @param $key String Property name to be retrieved.
        * @return mixed The value of the requested property.
        */
        public function __get($key){
            if($key == '_mysqli_obj'){
                return $this->getLink();
            }else if(strpos($key, '_') !== 0){
                //see above __set() jankiness comment. also applies here.
                if($this->_constructing){
                    return $this->$key;
                }elseif(in_array($key, $this->_fields)){
                    if(is_object($this->$key) && get_class($this->$key) == 'SyrupField'){
                        return $this->$key->getValue();
                    }else{
                        return $this->$key;
                    }
                }else{
                    throw new UnknownModelFieldException($key);
                }
            }else{
                return $this->$key;
            }
        }

        /**
        * Get a list of all the field names this Model defines.
        *
        * @return Array List of string names of fields.
        */
        public static function getFieldNames(){
            $names = array();
            $props = get_class_vars(get_called_class());
            foreach($props as $prop => $val){
                //static jankiness even
                if(strpos($prop, '_') !== 0){
                    $names[] = $prop;
                }
            }
            return $names;
        }

        /**
        * Get all the fields that this Model contains.
        * 
        * @return Array List of all field names in this Model.
        */
        public function getFields(){
            return $this->_fields;
        }

        /**
        * Get the Primary Key for this Model.
        * 
        * @return Array Two keys "name" => name of the PK field, "field" => SyrupField instance of the PK field.
        */
        public function getPrimaryKey(){
            $key = $this->_primaryKey;
            return $key['field'];
        }

        /**
        * Get the field name of the Primary Key for this Model.
        * 
        * @return String Name of the PK field.
        */
        public function getPrimaryKeyName(){
            $key = $this->_primaryKey;
            return $key['name'];
        }

        /**
        * Get the relations defined by the fields in this Model.
        * 
        * @param $fieldName String Specific field to get relation info for. Defaults to NULL = get all relations for the model.
        * @return Array List of all relation arrays defined by this Model.
        */
        public function getRelations($fieldName = NULL){
            if($fieldName){
                return $this->_relations[$fieldName];
            }else{
                return $this->_relations;
            }
        }

        /**
        * Get an array representation of all this object's fields and their values.
        * 
        * @return Array Associative list of all fields and values.
        */
        public function repr(){
            $result = array();
            foreach($this->getFields() as $fieldName){
                if(is_object($this->$fieldName)){
                    if(get_class($this->$fieldName) == 'SyrupField'){
                        $result[$fieldName] = "SyrupField: " . $this->$fieldName->getValue();
                    }else{
                        $result[$fieldName] = get_class($this->$fieldName) . " Object";
                    }
                }else{
                    if(is_array($this->$fieldName)){
                        $result[$fieldName] = "Array()";
                    }else{
                        $result[$fieldName] = $this->$fieldName;
                    }
                }
            }
            return $result;
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

        //this is for use in marking a field that exists only as a placeholder to be filled in by a relationalGet
        const REL_PLACEHOLDER = 'these are not my pants';

        public $type;
        public $length;
        public $null;
        public $key;
        public $relation;
        public $default;
        public $extra;
        public $comment;

        public $isPrimaryKey = false;
        public $isForeignKey = false;

        private $_value;
        

        /**
        * Create a new SyrupField instance. 
        * 
        * @param $type String The type of this field (one of the class constants defined in this class).
        * @param $length int [optional] Length of this field. Required by some types.
        * @param $null Boolean [optional] Whether this field allows NULL values. Defaults to True.
        * @param $default mixed [optional] The default value for this field if none is specified.
        * @param $key String [optional] The type of key that this field is. Currently one of: PK (Primary Key), FK (Foreign Key)
        * @param $relation Array [optional] Relation this field represents. Key represents type (hasOne, hasMany), value represents foreign field (TableName.field_name). 
        * @param $extra Array [optional] List of extra data about this field. Currently one of: UUID (This field is a UUID and will have a uuid generated if none is specified)
        * @param $comment String [optional] Plain text comments to be stored in this field for human-readable documentation.
        * @return SyrupField The newly created instance.
        */
        public function __construct($type, $length = NULL, $null = NULL, $default = NULL, $key = NULL, $relation = NULL, $extra = NULL, $comment = NULL){
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
            $this->relation = $relation;
            $this->null = (($null === false)? false : true);
            $this->default = $default? $default : false;
            $this->extra = $extra? $extra : array();
            $this->comment = $comment;

            if($this->default){
                $this->_value = $this->default;
            }else{
                $this->_value = NULL;
            }
        }

        /**
        * Get the value contained in this field.
        * 
        * @return mixed Value that this field contains.
        */
        public function getValue(){
            return $this->_value;
        }

        /**
        * Set the value contained in this field
        * 
        * @param $value mixed The value to set.
        */
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