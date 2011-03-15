<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of newPHPClass
 *
 * @author JanVanDerBreggen
 */
class Collection_Logic_Event_Instance 
{
    /*
     * account_collection_event_history ORM
     */
    protected $oDO;

    protected $oScenarioEvent; //optional, not supplied in the case of the 'reset' event

    public function __construct($mDefinition)
    {
        if (is_numeric($mDefinition))
        {
            //implement
        }
        else if (get_class($mDefinition) == 'Account_Collection_Event_History')
        {
            $this->oDO = $mDefinition;
        }
        else
        {
            throw new Exception('bad parameter passed into Collection_Logic_Event_Instance constructor');
        }
    }



    /**
     * Creates a new Collection_Logic_Event_Instance object and saves it's data
     * @param <type> $mItemToSchedule, either a Collection_Logic_Scenario_Event object or a Collection_Logic_Event object
     * @param <type> $oAccount
     */
    public static function schedule($oAccount, $mItemToSchedule)
    {
        //return an instance of this class
    }

    /**
     * retrieves the Collection_Logic_Event object for this event instance and calls its invoke() method
     */
    public function invoke()
    {

    }

    public static function getMostRecentForAccount($oAccount) 
    {
        $oORM = Account_Collection_Event_History::getMostRecentForAccountId($oAccount->id);
        return $oORM === null ? null : new self($oORM);
    }
}
?>
