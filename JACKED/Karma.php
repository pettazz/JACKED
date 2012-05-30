<?php

    class Karma extends JACKEDModule{
        /*
            This isn't reddit

            Vote on shit
        */
            
        const moduleName = 'Karma';
        const moduleVersion = 1.0;
        public static $dependencies = array('MySQL', 'Flock');
        

    }

?>