<?php

    abstract class SyrupDriverInterface{
        
        /**
        * Allow methods such as findByGUID('someguid') or countByAliveAndAuthor(1, 'someauthorguid')
        * Wraps to call find/findOne/count as needed
        */
        public function __call($method, $params){
            if (!preg_match('/^(find|findOne|count)By([a-zA-Z0-9]+)$/', $method, $matches)) {
                throw new Exception("Call to undefined method {$method}");
            }
     
            $criteriaKeys = explode('_And_', preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $matches[2]));
            $criteriaKeys = array_map('strtolower', $criteriaKeys);
            $criteriaValues = array_slice($params, 0, count($criteriaKeys));
            $criteria = array_combine($criteriaKeys, $criteriaValues);

            $method = $matches[1];
            return $this->$method($criteria);
        }

        /**
        * Find one data object matching the given criteria. This is a wrapper for a call to find() with a limit of 1.
        * 
        * @param $criteria Array [optional] Criteria for searching data objects. Defaults to all objects.
        * @param $order Array [optional] Two keys to specify ordering: 'field' field name to order by, 'direction' ASC or DESC. Defaults to none.
        * @param $followRelations [optional] Whether to find objects specified by relations. Defaults to true.
        * @return SyrupModel|Boolean The object returned from the data source, or False if no matches were found or an error occurred.
        */
        public function findOne($criteria = array(), $order = null, $followRelations = true){
            $objects = $this->find($criteria, $order, 1, 0, $followRelations);
            return count($objects) == 1 ? $objects[0] : null;
        }

        /**
        * Find all objects matching the given criteria, with optional ordering, limits, and offset.
        * 
        * @param $criteria Array [optional] Criteria for searching data objects. Defaults to all objects.
        * @param $order Array [optional] Two keys to specify ordering: 'field' field name to order by, 'direction' ASC or DESC. Defaults to none.
        * @param $limit int [optional] Limit results to this number. Defaults to no limit.
        * @param $offset int [optional] Start returning results at this offset. Ex: 5 rows are returned, offset 3 would return rows 3 and 4 (4th and 5th) Defaults to 0.
        * @param $followRelations [optional] Whether to find objects specified by relations. Defaults to true.
        * @return Array|Boolean List of data objects returned from the data source. Empty array for no results. False if an error occurred.
        */
        abstract public function find($criteria = array(), $order = null, $limit = null, $offset = 0, $followRelations = true);

        /**
        * Counts the number of objects matching @criteria
        * 
        * @param $criteria Array [optional] Criteria for searching data objects. Defaults to all objects.
        * @return int Number of matching objects.
        */
        abstract public function count($criteria = array());

        /**
        * Creates a new data object instance
        * 
        * @param $data Array [optional] Field values to be set upon creation. Defaults to each field's default value.
        * @return SyrupModel The new instance.
        */
        abstract public function create($data = NULL);

        /**
        * Saves the existing state of this data object to the data source.
        * 
        * @return Boolean Whether the save was completed successfully.
        */
        abstract public function save();

        /**
        * Deletes the data object from the data source.
        * 
        * @return Boolean Whether the delete was completed successfully.
        */
        abstract public function delete();

    } 

?>