<?php

    class Purveyor extends JACKEDModule{
        /*
            Sell stuff
        */
    
        const moduleName = 'Purveyor';
        const moduleVersion = 1.0;
        public static $dependencies = array('Syrup', 'Flock');
        

        /**
        * Create a new Promotion for Tickets 
        * 
        * @param $name String Name of the new Promotion
        * @param $description String Descriptive text for the new Promotion
        * @param $value Integer Value (in USD cents) of Tickets associated to this Promotion
        * @param $active Boolean Whether the new Promotion should be active upon creation. Default: True
        * @return String GUID of the newly created Promotion
        */
        public function createPromotion($name, $description, $value, $active = True){
            $promo = $this->JACKED->Syrup->Promotion->create();

            $promo->name = $name;
            $promo->description = $description;
            $promo->value = $value;
            $promo->active = $active;

            $promo->save();

            return $promo->guid;
        }

        /**
        * Create a Ticket
        * 
        * @param $user String GUID of the Flock User who owns this Ticket
        * @param $promotion String GUID of the Promotion that this Ticket is a part of
        * @param $valid Boolean Whether this Ticket is currently valid. Default: True
        * @return String GUID of the newly created Promotion
        */
        public function createTicket($user, $promotion, $valid = True){
            $tk = $this->JACKED->Syrup->Ticket->create();

            $tk->User = $user;
            $tk->User = $promotion;
            $tk->valid = $valid;
            
            $tk->save();

            return $tk->guid;
        }

    }

?>