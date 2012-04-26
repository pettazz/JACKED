<?php

    /**
    * Your one-stop shop for updating JACKED modules and core
    */
    class Updater extends JACKEDModule{
        const moduleName = 'Updater';
        const moduleVersion = 1.0;
        public static $dependencies = array('MySQL');


        /**
        * Create a Backup of the entire JACKED folder as it exists right now in the backups folder.
        * 
        * @param $appendName String [optional] String to append to the tarball name. Defaults to nothing.
        * @return String The filename of the newly created backup tarball
        */
        private function createBackup($appendName = false){
            if(!is_dir(JACKED_MODULES_ROOT . 'backups')){
                mkdir(JACKED_MODULES_ROOT . 'backups');
            }

            $name = time() . ($appendName? '.' . $appendName : '') . '.tar';
            $phar = new PharData($name); 
            $phar->buildFromDirectory(JACKED_MODULES_ROOT, '/^((?!backups).)*$/');
            $this->JACKED->Logr->write('Created a backup called ' . $name);

            return $name;
        }
        
        /**
        * Restores a backup of the JACKED folder. Makes a new backup of the current folder 
        * marked with 'current_from_restore' first. 
        * 
        * @param $name String [optional] The name of the backup tarball to restore. Defaults to the most recent.
        * @return Boolean Whether the restore was completed successfully.
        */
        private function createBackup($name = false){
            if(!is_dir(JACKED_MODULES_ROOT . 'backups')){
                return false;
            }

            $current = $this->createBackup('current_from_restore');
            try{
                $backups = scandir(JACKED_MODULES_ROOT . 'backups', 1);
                if($backups[0] == $current){
                    if($backups[1] == '..'){
                        return false;
                    }else{
                        $name = $backups[1];
                    }
                }else{
                    $name = $backups[0];
                }
                $phar = new PharData(JACKED_MODULES_ROOT . 'backups' . $name);
                $done = $phar->extractTo(JACKED_MODULES_ROOT, null, true);
                $this->JACKED->Logr->write('Restored from a backup called ' . $name);
                return $done;

            }catch(Exception $e){
                $this->JACKED->Logr->write('Failed to restore backup (' . $name . '): ' . $e->getMessage() , 2);
                return false;
            }
        }
?>