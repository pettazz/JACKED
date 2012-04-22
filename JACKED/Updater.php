<?php
    /**
    * Your one-stop shop for updating JACKED modules and core
    */
    class Updater extends JACKEDModule{
        const moduleName = 'Updater';
        const moduleVersion = 1.0;
        public static $dependencies = array('MySQL');


        /**
        * Get 
        * 
        * @param int $user User ID to check friendship with
        * @throws NotLoggedInException if the user is not logged in
        * @return Boolean Whether the user is friends with the given user 
        */
        

?>