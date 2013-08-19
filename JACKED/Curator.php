<?php

    class Curator extends JACKEDModule{
        /*
            Curate your junk with tags

            Tagificationator
        */
            
        const moduleName = 'Curator';
        const moduleVersion = 1.0;
        public static $dependencies = array('MySQL');
        

        /**
        * Tag a given target object with the given tag name(s). If a tag name does not exist, 
        * we will attempt to create it before tagging. Names must be an exact match.
        * 
        * @param $target String The GUID of the target
        * @param $tags Mixed A single String name of a tag to assign, or an Array list of names
        * @return Boolean Whether the assignation was completed successfully
        */
        public function assignTagByName($target, $tagNames){
            if(!is_array($tagNames)){
                $tagNames = array($tagNames);
            }

            $tagIDs = array();
            foreach($tagNames as $tag){
                if($this->doesTagExistByName($tag)){
                    $tagData = $this->getTagByName($tag);
                    $tagIDs[] = $tagData['guid'];
                }else{
                    $tagIDs[] = $this->createTag($tag);
                }
            }
            return $this->assignTag($target, $tagIDs);
        }

        /**
        * Tag a given target object with the given tag(s) GUIDs.
        * 
        * @param $target String The GUID of the target
        * @param $tags Mixed A single String GUID of a tag to assign, or an Array list of GUIDs
        * @return Boolean Whether the assignation was completed successfully
        */
        public function assignTag($target, $tags){
            if(!is_array($tags)){
                $tags = array($tags);
            }

            $success = True;
            foreach($tags as $tag){
                if($this->isTargetTagged($target, $tag)){
                    throw new TargetAlreadyTaggedException();
                }
                $done_tag = $this->JACKED->MySQL->insert(
                    $this->config->dbt_tagrels,
                    array(
                        'Curator' => $tag,
                        'target' => $target
                    )
                );
                $done_inc = $this->JACKED->MySQL->update(
                    $this->config->dbt_tags,
                    array('usage' => 'function:`usage` + 1'),
                    'guid = "' . $tag . '"'
                );
                $success = $success && ($done_tag && $done_inc);
            }
            
            return $success;
        }

        /**
        * Remove tag(s) of a given name from a target. Tag names that do not exist are ignored.
        * 
        * @param $target String The GUID of the target
        * @param $tags Mixed A single String name of a tag to assign, or an Array list of names
        * @return Boolean Whether the removal was completed successfully
        */
        public function removeTagByName($target, $tagNames){
            if(!is_array($tagNames)){
                $tagNames = array($tagNames);
            }

            $tagIDs = array();
            foreach($tagNames as $tag){
                if($this->doesTagExistByName($tag)){
                    $tagData = $this->getTagByName($tag);
                    $tagIDs[] = $tagData['guid'];
                }
            }
            return $this->removeTag($target, $tagIDs);
        }

        /**
        * Remove tag(s) from a target.
        * 
        * @param $target String The GUID of the target
        * @param $tags Mixed A single String GUID of a tag to assign, or an Array list of GUIDs
        * @return Boolean Whether the removal was completed successfully
        */
        public function removeTag($target, $tags){
            if(!is_array($tags)){
                $tags = array($tags);
            }

            $success = True;
            foreach($tags as $tag){
                if(!$this->isTargetTagged($target, $tag)){
                    throw new TargetNotTaggedException();
                }
                $done_tag = $this->JACKED->MySQL->delete(
                    $this->config->dbt_tagrels,
                    "Curator = '$tag' AND
                    target = '$target'"
                );
                $done_inc = $this->JACKED->MySQL->update(
                    $this->config->dbt_tags,
                    array('usage' => 'function:`usage` - 1'),
                    'guid = "' . $tag . '"'
                );
                $success = $success && ($done_tag && $done_inc);
            }
            
            return $success;
        }

        /**
        * Creates a new tag but does not tag anything with it. 
        * 
        * @param $name String The exact name of the tag to create
        * @return Mixed String GUID of the new tag if created successfully, else False
        */
        public function createTag($name){
            if($this->doesTagExistByName($name)){
                throw new TagAlreadyExistsException();
            }

            $new_guid = $this->JACKED->Util->uuid4();
            $done = $this->JACKED->MySQL->insert(
                $this->config->dbt_tags,
                array(
                    'guid' => $new_guid,
                    'name' => $name
                )
            );
            if($done === false){
                return false;
            }else{
                return $new_guid;
            }
        }

        /**
        * Gets the tag data for a given tag GUID
        * 
        * @param $guid String The GUID of the tag to find
        * @return Array List of all tag data
        */
        public function getTag($guid){
            if(!$this->doesTagExist($guid)){
                throw new TagDoesNotExistException();
            }

            return $this->JACKED->MySQL->getRow(
                $this->config->dbt_tags, 
                'guid = "' . $guid . '"'
            );
        }

        /**
        * Gets the tag data for a given tag name. Name must be an exact match.
        * 
        * @param $name String The exact name of the tag to find
        * @return Array List of all tag data
        */
        public function getTagByName($name){
            if(!$this->doesTagExistByName($name)){
                throw new TagDoesNotExistException();
            }

            return $this->JACKED->MySQL->getRow(
                $this->config->dbt_tags, 
                'name = "' . $name . '"'
            );
        }

        /**
        * Get a list of all existing tags.
        * 
        * @return Array of Associative arrays of data for every tag
        */
        public function getAllTags(){
            return $this->JACKED->MySQL->getRows($this->config->dbt_tags, 1);
        }

        /**
        * Get all the tags for a given target GUID
        * 
        * @param $target String The GUID of the target to find tags for
        * @return Array of Associative arrays of data for @target's tags. May be empty.
        */
        public function getTagsForTarget($target){
            return $this->JACKED->MySQL->getJoin(
                array('guid', 'name', 'usage'),
                false,
                'LEFT',
                $this->config->dbt_tags,
                $this->config->dbt_tagrels,
                'guid',
                'Curator',
                $this->config->dbt_tagrels . '.target = "' . $target . '"'
            );
        }

        /**
        * Determines whether a target is tagged with a given Tag.
        * 
        * @param $target String The GUID of the target to check
        * @param $tag String The GUID of the tag to check for
        * @return Boolean Whether the tag exists on the Target
        */
        public function isTargetTagged($target, $tag){
            return ($this->JACKED->MySQL->getRow(
                $this->config->dbt_tagrels, 
                'Curator = "' . $tag . '" AND target = "' . $target . '"'
            )) ? True : False;
        }

        /**
        * Determines whether a tag exists. 
        * 
        * @param $guid String The GUID of the tag to check for
        * @return Boolean Whether the tag exists
        */
        public function doesTagExist($guid){
            return ($this->JACKED->MySQL->getRow(
                $this->config->dbt_tags, 
                'guid = "' . $guid . '"'
            )) ? True : False;
        }

        /**
        * Determines whether a tag exists by the full string name. 
        * 
        * @param $name String The full, exact name of the tag to search for
        * @return Boolean Whether the tag exists
        */
        public function doesTagExistByName($name){
            return ($this->JACKED->MySQL->getRow(
                $this->config->dbt_tags, 
                'name = "' . $name . '"'
            )) ? True : False;
        }

    }


    class TagDoesNotExistException extends Exception{
        protected $message = 'The referenced tag does not exist.';
    }

    class TargetNotTaggedException extends Exception{
        protected $message = 'The target is not tagged with the referenced tag.';
    }

    class TagAlreadyExistsException extends Exception{
        protected $message = 'The referenced tag name already exists.';
    }

    class TargetAlreadyTaggedException extends Exception{
        protected $message = 'The referenced target is already tagged with the referenced Tag.';
    }

?>