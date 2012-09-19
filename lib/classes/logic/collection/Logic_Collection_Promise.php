<?php

/**
 * Description of Collection_Logic_Promise
 * When a promise is created, we create as many collectables
 * as there are 'invoices' that the promise is derived from
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Promise implements DataLogic
{
    // Put your code here
    protected $oDO;
    protected $aInstalments;   
    protected $oAccount;
	protected $aCollectables;
    protected $oException;
	public $aReportDetails = array();

    public function __construct($mDefinition)
    {
    	if (is_numeric($mDefinition))
    	{
    		$this->oDO = Collection_Promise::getForId($mDefinition);
    	}
    	else if ($mDefinition instanceof Collection_Promise)
    	{
    		$this->oDO = $mDefinition;
    	}
    	else
    	{
    		throw new Exception("Invalid arguments passed to ".get_class($this));
    	}
    }

    public function getBalance()
    {
         $aCollectables	= $this->getCollectables();

    	foreach ($aCollectables as $oCollectable)
    	{
    		$fBalance += $oCollectable->balance;
    	}

        return $fBalance;
    }

    public function getAmount()
    {
        $aCollectables	= $this->getCollectables();
		$fAmount 		= 0;
    	foreach ($aCollectables as $oCollectable)
    	{
    		$fAmount += $oCollectable->amount;
    	}
        return $fAmount;
    }

    public function getCollectables()
    {
    	if ($this->aCollectables === NULL)
		{
			$this->aCollectables = array();
			$aCollectables = Collectable::getForPromiseId($this->oDO->id);
			foreach ($aCollectables as $oCollectable)
			{
				$this->aCollectables[$oCollectable->id] = Logic_Collectable::getInstance($oCollectable);
			}
		}
       
        if (count($this->aCollectables)==0)
                throw new Logic_Collection_Exception ("Promise $this->id has no underlying collectable");
    	return $this->aCollectables;
    }

    public function getOldestOpenCollectable()
    {
        $aCollectables = $this->getCollectables();
        $oOldest;
        foreach ($aCollectables as $oCollectable)
        {
            if ( $oCollectable->balance > 0 && ($oOldest === null ||  $oCollectable->due_date < $oOldest->due_date))
                    $oOldest = $oCollectable;

        }

        return $oOldest;
    }

    public function getNewestCollectableWithRoomForDebit()
    {
        $aCollectables = array_reverse($this->getCollectables());
        $oNewest;
        foreach ($aCollectables as $oCollectable)
        {
            if ( $oCollectable->balance < $oCollectable->amount)
                    return $oCollectable;
        }
        return null;
    }



    public function getAccount()
    {
        if ($this->oAccount === null && $this->oDO->account_id !== null)
        {
        	$this->oAccount = Logic_Account::getForId($this->oDO->account_id);
        }
        return $this->oAccount;
    }

    public function getInstalments()
    {
        if ($this->aInstalments === null)
        {
            $aInstalments = Collection_Promise_Instalment::getForPromiseId($this->oDO->id);
            $this->aInstalments = array();
            foreach($aInstalments as $oInstalment)
            {
                $this->aInstalments[] = new Logic_Collection_Promise_Instalment($oInstalment);
            }
        }
        return $this->aInstalments;
    }

    public function paidUptoAfter($sDate)
    {
        $aInstalment = $this->getInstalments();
        foreach ($aInstalment as $oInstalment)
        {
            if ($oInstalment->getBalance() < $oInstalment->amount && $oInstalment->due_date > $sDate)
                    return true;
        }
        return false;
    }

    public function getNextDueInstalment()
    {
        $aCollectables	= $this->getCollectables();
    	$fPaid			= 0;

        $fCollectableAmount = 0.0000;
        $fPromiseAmount = 0.0000;

    	foreach ($aCollectables as $oCollectable)
    	{
    		$fPaid += $oCollectable->amount - $oCollectable->balance;
                 $fCollectableAmount += $oCollectable->amount;
    	}

    	// Calculate how many instalments are covered by what has been paid, and return the first instalment that is not fully covered by the total amount paid
        $aInstalments		= $this->getInstalments();
        $oResult = null;
        foreach ($aInstalments as $oInstalment)
        {
            $fPaid -= $oInstalment->amount;
            if ($fPaid < 0.00 && $oResult === null)
            {
                $oResult =  $oInstalment;
            }
            $fPromiseAmount += $oInstalment->amount;
        }

        if ($fPromiseAmount !== $fCollectableAmount)
            throw new Exception ("Promise Amount ($fPromiseAmount)  does not equal the total amount of the underlying collectables ($fCollectableAmount). Promise Id: $this->id");
        return $oResult;
    }

    public function getLatestInstalmentWithRoomForDebit()
    {
        $mResult = null;
         $aInstalments = $this->getInstalments();
        $oNextDue = $this->getNextDueInstalment();
        if ($oNextDue !== null && $oNextDue->getBalance()< $oNextDue->amount)
        {

            $mResult =  $oNextDue;
        }
        else if ($oNextDue === null)
        {
           $mResult = array_pop($aInstalments);
        }
        else
        {
            $oPreviousInstalment = null;
            for ($i=0;$i<count($aInstalments);$i++)
            {
                $oInstalment = $aInstalments[$i];
                if ($oInstalment->id == $oNextDue->id)
                {
                        $mResult = $oPreviousInstalment;
                        break;
                }
                $oPreviousInstalment = $oInstalment;
            }
        }

        return $mResult;

    }

    public function isActive()
    {
        return ($this->completed_datetime == null);
    }

	public function getInstalmentAmount()
	{
		$aInstalments		= $this->getInstalments();
		$fAmount = 0.0;
		foreach ($aInstalments as $oInstalment)
		{
			$fAmount += $oInstalment->amount;
		}

		return $fAmount;
	}

	 public function isBroken()
    {
    	// Calculate balance
		$bBroken = FALSE;
		$iNow			= time();
		$iLeniencyWindow = Collections_Config::get()->promise_instalment_leniency_days;
		$fLeniencyAmount =  Collections_Config::get()->promise_instalment_leniency_dollars;
		$iDaysToAddForOverdue = $iLeniencyWindow +1;
		$iInstalment = 1;
		$fAccountBalance = $this->getAccount()->getAccountBalance();
		$aInstalments		= $this->getInstalments();
		foreach ($aInstalments as $oInstalment)
		{
			$sDueDate = $oInstalment->due_date;
			//add one day to determine when the account will be overdue
			$iOverDue = strtotime("+$iDaysToAddForOverdue day", strtotime($sDueDate));
			$sOverDueDate = date("Y-m-d",$iOverDue );
			$fBalance = $oInstalment->getBalance();
			if ($iOverDue < $iNow && $fBalance > $fLeniencyAmount)
			{
				$this->aReportDetails = array(	'promise id'						=> $this->id,
												'promise balance'					=>$this->getBalance(),
												'instalment number'					=>$iInstalment,
												'instalment amount'					=>$oInstalment->amount,
												'instalment balance'				=>$oInstalment->getBalance(),
												'instalment due date'				=> $sDueDate,
												'leniency window applied'			=> $iLeniencyWindow,
												'leniency amount applied'			=> $fLeniencyAmount,
												'instalment overdue date'			=> $sOverDueDate,
												'account balance'					=> $fAccountBalance,
												'total promise collectable amount'	=> $this->getAmount(),
												'total promise instalment amount'	=> $this->getInstalmentAmount());
				$bBroken = TRUE;
				break;

			}
			$iInstalment++;
		}
		return $bBroken;
    }




    /**
     * Returns true if the promise is not yet complete and the balance == 0 OR if the promise is complete and the completion status is KEPT
     * Returns false if the above condition was not met     * 
     */
    public function isFulfilled()
    {
	$bFulfilled = false;
        if ($this->collection_promise_completion_id == COLLECTION_PROMISE_COMPLETION_KEPT)
        {
                $bFulfilled =  true;
        }
        else
        {

            // Calculate balance
            $fBalance		= 0;
            $aCollectables	= $this->getCollectables();
            foreach ($aCollectables as $oCollectable)
            {
                    $fBalance += $oCollectable->balance;
            }

            $bFulfilled = ($fBalance == 0);

        }

        ////Log::getLog()->log("isFulfilled: (($fBalance == 0) = ".($bFulfilled ? 'yes' : 'no'));
        return $bFulfilled;
    }

    /**
     * @method complete
     * Sets the status of a promise to 'complete' as follows:
     * - sets the collection_promise_completion_id
     * - sets the completed_employee_id
     * - sets the completed_employee_id
     * - if the promise is broken: the appropriate scenario is set to be the current scenario on the account
     *
     * @param <type> $iCompletionId - collection_promise_completion constant
     *
     */

    public function complete($iCompletionId)
    {
        $this->collection_promise_completion_id = $iCompletionId;       
        $iEmployeeId					= Flex::getUserId();
        $this->completed_employee_id 	= ($iEmployeeId != null ? $iEmployeeId : Employee::SYSTEM_EMPLOYEE_ID);
        $this->completed_datetime 		= Data_Source_Time::currentTimestamp();
		$oAccount = $this->getAccount();
		$fBalanceBefore = $oAccount->getCollectableBalance();
        if ($iCompletionId == COLLECTION_PROMISE_COMPLETION_BROKEN)
        {           
            $iScenario = $this->getScenarioId();
            $oAccount->setCurrentScenario($iScenario, false);
        }

        $this->save();

        if ($iCompletionId == COLLECTION_PROMISE_COMPLETION_BROKEN || $iCompletionId == COLLECTION_PROMISE_COMPLETION_CANCELLED)
        {           			
            $oAccount->redistributeBalances();
        }
		$fBalanceAfter = $oAccount->getCollectableBalance();


		$oEmail	=  Email_Notification::getForSystemName('ALERT');
		
		$oEmail->setSubject('Collection Promise Completion Report');
		$sText = "Summary: \n\n";
		foreach ($this->aReportDetails as $key => $value)
		{
			$sText .= " $key: $value;\n";
		}

		$sText .= "Collectable Balance prior to save: $fBalanceBefore\n";
		$sText .= "Collectable Balance after save: $fBalanceAfter\n\n";

		$sText .="Promise Details: \n\n";

		$aArray = $this->toArray();

		foreach ($aArray as $sKey => $mValue)
		{
			if (is_array($mValue))
			{
				$sText .= $sKey.":\n";
				foreach ($mValue as $sKey1 => $mValue1)
				{
					if (is_array($mValue1))
					{
						$sText .= $sKey1.":\n";
						foreach ($mValue1 as $sKey2 => $mValue2)
						{
							$sText .= "\t$sKey2: $mValue2\n";
						}
					}
					else
					{
					
					$sText .= "\t$sKey1: $mValue1\n";
					}
				}
			}
			else
			{
				$sText .= "$sKey: $mValue\n";
			}
		}

		$oEmail->setBodyText($sText);
		$oEmployee = Employee::getForId(Flex::getUserId());
		//if ($oEmployee!= null && $oEmployee->email!=null)
		//	$oEmail->addTo($oEmployee->Email, $name=$oEmployee->FirstName.' '.$oEmployee->LastName);
		$oEmail->send();

        return $this->id;

    }

    /**
     * promises that are broken or fulfilled are completed
     */
    public function process()
    {
        //only process active promises
		if ($this->completed_datetime !== NULL)
			return;

		if ($this->isBroken())
        {
            Log::getLog()->log("... promise is broken");
            $this->complete(COLLECTION_PROMISE_COMPLETION_BROKEN);
        }
        else if ($this->isFulfilled())
        {
            Log::getLog()->log("... promise is fulfilled");
            $this->complete(COLLECTION_PROMISE_COMPLETION_KEPT);
        }
        else
        {
            Log::getLog()->log("... promise is ongoing");
        }
		Logic_Collection_BatchProcess_Report::addPromise($this);
    }

    public function getScenarioId() {
        $iCurrentScenarioId = $this->getAccount()->getCurrentScenarioInstance()->collection_scenario_id;
        return Collection_Scenario::getForId($iCurrentScenarioId)->broken_promise_collection_scenario_id;
    }

    public function setException($e)
    {
        $this->oException = $e;
    }

    public function getException()
    {
        return $this->oException;
    }


    public static function getForId($iId)
    {
        $oORM = Collection_Promise::getForId($iId);
        return $oORM != null ? new self($oORM) : null;
    }


    public static function getForCollectable($oCollectable)
    {
	$oDO = Collection_Promise::getForId($oCollectable->collection_promise_id);
	if ($oDO)
	{
		return new self($oDO);
	}
	return null;
    }

    public static function getForAccount($oAccount)
    {
	$oORM = Collection_Promise::getForAccountId($oAccount->Id);
	return $oORM === NULL ? NULL : new self($oORM);


    }

    public static function getActivePromises()
    {
        $aDO = Collection_Promise::getActivePromises();
        $aObjects = array();
        foreach ($aDO as $oDO)
        {
            $aObjects[$oDO->id] = new self($oDO);
        }
        return $aObjects;
    }

    /**
     * all active promises to pay are processed
     */
    public static function batchProcess($aPromises)
    {
		Log::getLog()->log("--------Promises Batch Process Start -------------");

		foreach ($aPromises as $oPromise)
		{
			$oDataAccess = DataAccess::getDataAccess();
			$oDataAccess->TransactionStart();
			try
			{
			Log::getLog()->log("Processing promise with ID: .$oPromise->id. ");
			$oPromise->process();
			$oDataAccess->TransactionCommit();

			}
			catch (Exception $e)
			{
			if ($e instanceof Exception_Database)
			{
				$oDataAccess->TransactionRollback();
				throw $e;
			}
			else
			{
				$oDataAccess->TransactionRollback();
				$oPromise->setException($e);
				Logic_Collection_BatchProcess_Report::addPromise($oPromise);
			}
			}
		}

		Log::getLog()->log("-------Promises Batch Process End--------------");
    }



    public function __get($sField)
    {
    	return $this->oDO->{$sField};
    }

    public function __set($sField, $mValue)
    {
		$this->oDO->{$sField} = $mValue;
    }

    public function save()
    {
		return $this->oDO->save();
    }

    public function toArray()
    {
        $aArray = $this->oDO->toArray();

        $oAccount = $this->getAccount();
        $aArray['account'] = $oAccount->toArray();
        $aCollectables = $this->getCollectables();
        $aArray['collectables'] = array();
        foreach ($aCollectables as $oCollectable)
        {
            $aArray['collectables'][] = $oCollectable->toArray();
        }

        $aInstalments = $this->getInstalments();
        $aArray['instalments'] = array();
        foreach ($aInstalments as $oInstalment)
        {
            $aArray['instalments'][] = $oInstalment->toArray();
        }

        return $aArray;
    }


    public function display()
    {

        $aArray = $this->oDO->toArray();
        ////Log::getLog()->log('%%%%%%%%%%%%%%%%%%%%%Details of Promise '.$this->id.' %%%%%%%%%%%%%%%%%%%%%%%%');

        ////Log::getLog()->log('Account ID: '.$this->account_id);
        ////Log::getLog()->log('Created On: '.$this->created_datetime);
        $sCompleted = $this->completed_datetime == null ? 'Active Promise, not yet completed' :  $this->completed_datetime;
        ////Log::getLog()->log('Completed On: '.$sCompleted);
        $sCompletionReason = $this->collection_promise_completion_id!= null ? Collection_Promise_Completion::getForId($this->collection_promise_completion_id)->name : 'Active Promise, not yet completed';

        ////Log::getLog()->log('Completion Reason:'.$sCompletionReason);
        ////Log::getLog()->log('Scenario Triggered when broken '.$this->collection_scenario_id);
        ////Log::getLog()->log('Collectables:');
        $aCollectables = $this->getCollectables();
        foreach($aCollectables as $oCollectable)
        {
            $oCollectable->display();
        }
         ////Log::getLog()->log('Instalments:');

         $aInstalments = $this->getInstalments();
         foreach ($aInstalments as $oInstalment)
         {
             $oInstalment->display();
         }

          ////Log::getLog()->log('%%%%%%%%%%%%%%%%%%%%% END Details of Promise '.$this->id.' %%%%%%%%%%%%%%%%%%%%%%%%');
    }

}
?>
