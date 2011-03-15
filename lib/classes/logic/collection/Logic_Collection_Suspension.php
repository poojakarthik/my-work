<?php
/**
 * Description of Collection_Logic_Suspension
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Suspension implements DataLogic 
{
    protected $oDO;
    
    protected $oType;
    
    protected $oAccount;

    protected $oException;

    public function __construct($mDefinition)
    {
        if (is_numeric($mDefinition))
    	{
            $this->oDO = Collection_Suspension::getForId($mDefinition);
    	}
    	else if ($mDefinition instanceof Collection_Suspension)
    	{
            $this->oDO = $mDefinition;
    	}
    	else
    	{
            throw new Exception("Invalid arguments passed to ".get_class($this));
    	}
    }

    public static function batchProcess($aActiveSuspensions)
    {
        Log::getLog()->log('------Starting Suspensions Batch Process--------');    
           
        foreach ($aActiveSuspensions as $oORM)
        {
            try
            {
                Log::getLog()->log("Processing Suspension with id: $oORM->id");
		$oDataAccess = DataAccess::getDataAccess();
		$oDataAccess->TransactionStart();
		$oCurrentSuspension = new self($oORM);
                if ( $oCurrentSuspension->isPastEffectiveEndDateTime())
                {
                    Log::getLog()->log("Suspension $oCurrentSuspension->id is past its effective enddate time and will be ended.");
                    $oCurrentSuspension->end();
                    
                }
		else
		{
		    Log::getLog()->log("Suspension $oCurrentSuspension->id is ongoing.");
		}
		Logic_Collection_BatchProcess_Report::addSuspension($oCurrentSuspension);
		$oDataAccess->TransactionCommit();

                
            }
            catch(Exception $e)
            {
		$oDataAccess->TransactionRollback();
		if ($e instanceof Exception_Database)
                {                   
                    throw $e;
                }
                else
                {
                    $oCurrentSuspension->setException($e);
                    Logic_Collection_BatchProcess_Report::addSuspension($oCurrentSuspension);
                }
            }
        }

        Log::getLog()->log('------End Suspensions Batch Process--------');

    }

    public function setException($e)
    {
        $this->oException = $e;
    }

    public function getException()
    {
        return $this->oException;
    }

    public function isPastEffectiveEndDateTime()
    {
        
        if (strtotime(Data_Source_Time::currentDate())>= Flex_Date::truncate($this->proposed_end_datetime, "d", false))
                return true;
        return false;
    }

    public static function processForAccount($iAccountId)
    {
        $oActiveSuspension = Collection_Suspension::getActiveForAccount($iAccountId);
        if ($oActiveSuspension !== null)
        {
            $oObject = new self( $oActiveSuspension);
          

            if ($oObject->isPastEffectiveEndDateTime())
            {
                $oObject->end();
            }
          
        }
    }

    /**
     *
     */
    public function end($iReason = null)
    {

        ////Log::getLog()->log("Ending Suspension $this->id");
        $this->effective_end_datetime = Data_Source_Time::currentDate();
        $this->end_employee_id = Flex::getUserId();
        $this->collection_suspension_end_reason_id = Collection_Suspension_End_Reason::getForSystemName('EXPIRED')->id;
        $this->save();
        
    }

    public function getAccount() {
        if ($this->oAccount === null && $this->account_id !== null)
        {
            $this->oAccount = Logic_Account::getForId($this->account_id);
        }
        return $this->oAccount;

    }



    public function getReason()
    {
        if ($this->oReason === null)
            $this->oReason = Collection_Suspension_Reason::getForId($this->collection_suspension_reason_id);
        return $this->oReason;
    }

    public function __get($sField) 
    {
        return $this->oDO->$sField;
    }

    public function __set($sField, $mValue) 
    {
        $this->oDO->$sField = $mValue;
    }
    
    public function save() 
    {
        $this->oDO->save();
    }
    
    public function toArray() 
    {

    }

    public function display()
    {
        ////Log::getLog()->log('Details of Suspension '.$this->id);

        ////Log::getLog()->log('Account ID: '.$this->account_id);
        ////Log::getLog()->log('Started On: '.$this->start_datetime);
        ////Log::getLog()->log('Proposed End Date: '.$this->proposed_end_datetime);
        $sCompleted = $this->effective_end_datetime == null ? 'Active Suspension, not yet completed' :  $this->effective_end_datetime;
        ////Log::getLog()->log('Completed On: '.$sCompleted);
        $sCompletionReason = $this->collection_suspension_end_reason_id == null ? 'Active Suspension, not yet completed' : $this-collection_suspension_end_reason_id;
        ////Log::getLog()->log('Completion Reason: '. $sCompletionReason);
        $iScenario = $this->getReason()->collection_scenario_id;
        $mScenario = $iScenario != null ? $iScenario : 'No scenario change';
        ////Log::getLog()->log('Scenario Triggered when ended: '.$mScenario);

    }
    
    public static function getNumberOfSuspensionsInCurrentCollectionsPeriod($iAccountId, $sEffectiveDate=null)
    {
    	$aSuspensions = Collection_Suspension::getSuspensionsForCurrentCollectionsPeriod($iAccountId, $sEffectiveDate);
    	return count($aSuspensions);
    }
}
?>
