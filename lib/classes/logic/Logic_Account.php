<?php

/**
 * Description of Account_Logic
 *
 * @author JanVanDerBreggen
 */
class Logic_Account implements DataLogic
{

    const	    SCENARIO_OFFSET_USED_TO_DETERMINE_EXIT_COLLECTIONS = FALSE;

    protected	    $oDO;
    protected	    $aActiveScenarioInstances;

    protected	    $aCollectables;
    protected	    $aPayments;
    protected	    $aAdjustments;
    protected	    $_aPayables;
    protected	    $oActivePromise;

    public static   $_aTime = array();
    public static   $aMemory = array();

    protected	    $bPreviousEventNotCompleted;
    protected	    $bNoNextEventFound = false;
    protected	    $oException;

    protected static $aInstances = array();


    public static function getInstance($mDefinition, $bRefreshCache = false)
    {
	$oDO = null;
	if (is_numeric($mDefinition))
	{
	    $oDO = Account::getForId($mDefinition);
	    if ($oDO === null)
		throw new Exception("Invalid Account Id.....");
	}
	else if (get_class($mDefinition) == 'Account' && $mDefinition->Id != null )
	{
	    $oDO = $mDefinition;
	}
	else
	{
	    throw new Exception("Bad definition passed to Logic_Account::getInstance(): parameter should be a valid acount id or an instance of class 'Account' representing an already exisiting account.");
	}

	if ($bBypassCache)
	    return new self($oDO);

	if (!$bRefreshCache && in_array($oDO->Id, array_keys(self::$aInstances)))
	{
		return self::$aInstances[$oDO->Id];
	}
	else
	{
	    $oInstance = new self($oDO);
	    self::$aInstances[$oDO->Id]	= $oInstance;
	    return $oInstance;

	}

	

    }



    private function __construct($mDefinition)
    {
        $this->oDO = is_numeric($mDefinition) ? Account::getForId($mDefinition) : (get_class($mDefinition) == 'Account' ? $mDefinition : null);
    }



    /**
     * Returns the sum of all collectable balance for this account
     * only collectables that are currently due are taken into account
     * collectables that are part of an active promise are excluded
     * @todo: $iOffset is currently the way to calculate the due date against a point in time later than today.
     * instead of $iOffset the function parameter should be '$sNow = null'.
     * This will be much cleaner, and a way in which the due date can be calculated against any point in time.
     */
    public function getOverdueCollectableBalance($sNow = null)
    {

       $aCollectables = $this->getCollectables(Logic_Collectable::DEBIT);

	$fTotal = 0;
	foreach($aCollectables as $oCollectable)
	{
	    if ($oCollectable->isOverDue($sNow))
	    {
		$fTotal += $oCollectable->balance;
	    }
	}

	//////Log::getLog()->log("Account overdue balance: {$fTotal}");
	return $fTotal;
    }

    public function close($bSetDefaultRateplanOnServices)
    {
        $this->oDO->close($bSetDefaultRateplanOnServices);
    }


    public function getServicesForOCAReferral()
    {
        $aServices = $this->oDO->getAllServiceRecords(true);
        $aResult = array();
        foreach ($aServices as $oService)
        {
            $aServicesForFNN = Service::getFNNInstances($oService->FNN, $this->id, false);
            $oResult = array_pop($aServicesForFNN);
            if($oResult->Status != SERVICE_ARCHIVED)
                   $aResult[] = $oResult;
        }
        return $aResult;
    }

    /**
     * Returns the sum of all collectables amount for this account
     * only collectables that are currently due are taken into account
     * collectables that are part of an active promise are excluded
     *  @todo: $iOffset is currently the way to calculate the due date against a point in time later than today.
     * instead of $iOffset the function parameter should be '$sNow = null'.
     * This will be much cleaner, and a way in which the due date can be calculated against any point in time.
     * @todo: possibly implement this method in such a way that it determines the amount by means of an sql query rather than iterating over collectables.
     *
     */
    public function getOverDueCollectableAmount($sNow = null)
    {
        $aCollectables = $this->getCollectables(Logic_Collectable::DEBIT);

        $fTotal = 0;
        foreach($aCollectables as $oCollectable)
        {

            if ($oCollectable->isOverDue($sNow))
            {

            	$fTotal += $oCollectable->amount;
            }
        }

        //////Log::getLog()->log("Account collectable amount: {$fTotal}");
        return $fTotal;

    }

    public function cancelScheduledScenarioEvents()
    {
        $aInstances = Logic_Collection_Event_Instance::getWaitingEvents($this->id);
            foreach ($aInstances as $oInstance)
            {
                $oInstance->cancel();
            }
    }



    /**
     *
     * @return <type> Logic_Collection_Event_Instance
     */
    public function getMostRecentCollectionEventInstance()
    {
        return Logic_Collection_Event_Instance::getMostRecentForAccount($this);
    }

    public function getStartOfCollectionsEventInstance()
    {
	return Logic_Collection_Event_Instance::getFirstForAccount($this);
    }

   /**
    * @method Account_Logic::isCurrentlyInCollections
    * Determines if an account is currently in collections as follows:
    * - if most recent Logic_Collection_Event_Instance (account_collection_event_history) is the 'exit
    *  event or does not exist - return false
    * - else: return true
    */
    public function isCurrentlyInCollections()
    {
    	$oLastEventInstance = $this->getMostRecentCollectionEventInstance();

	if ($oLastEventInstance === null || $oLastEventInstance->isExitEvent())
	    return false;

        return true;
    }



    /**
     * Logic_Account::getNextScheduledEvent
     * based on the current scenario, the next scheduled event is returned
     * @return Scenario Event object or NULL if none qualifies
     */

    private function getNextCollectionScenarioEvent()
    {
            $oMostRecentEventInstance 		= $this->getMostRecentCollectionEventInstance();
           if ($oMostRecentEventInstance!== null && $oMostRecentEventInstance->completed_datetime === null)
           {
               $this->bPreviousEventNotCompleted = true;
               return null;
           }
           else
           {
               $this->bPreviousEventNotCompleted = false;
           }
            $oScenario	= $this->getCurrentScenarioInstance();
            $oNextEvent = $oScenario->getNextScheduledEvent($oMostRecentEventInstance,  $this->getCollectionsStartDate());

            $this->bNoNextEventFound = $oNextEvent === null ? true : false;

            return $oNextEvent;


    }

    public function previousEventNotCompleted()
    {
        return $this->bPreviousEventNotCompleted;
    }

    public function noNextEventFound()
    {
        return $this->bNoNextEventFound;
    }

    public function getSeverity()
    {
        return Logic_Collection_Severity::getForAccount($this);
    }

    public function resetSeverity()
    {
       $this->collection_severity_id = Collection_Severity::getForSystemName('UNRESTRICTED')->id;
       $this->save();
    }

    public function isBarred()
    {
        $oBarringLevel = Account_Barring_Level::getMostRecentForAccount($this->id);
        return $oBarringLevel == null ? false : $oBarringLevel->barring_level_id != BARRING_LEVEL_UNRESTRICTED;
    }

    public function shouldCurrentlyBeInCollections()
    {
	if ($this->isInSuspension())
	    return false;

	 $oScenario  = $this->getCurrentScenarioInstance()->getScenario();

	 //if they are not in collections
	 if (!$this->isCurrentlyInCollections())
	 {
	     $mNow = date('Y-m-d', strtotime("+$oScenario->day_offset days", time()));
	     return $oScenario->evaluateThresholdCriterion($this->getOverDueCollectableAmount($mNow),$this->getOverdueCollectableBalance($mNow));
	 }
	 //if they are in collections
	 else
	 {
	     //first we're going to determine whether the collectable(s) that triggered the account into collections continue to warrant them being in collections
	     //this step will cover the situation where there is a scenario offset,
	     //and the current date is after the start of the collections process but before the due date of the collectable(s) that triggered the start of the collections process
	     $iCollectionsStart = strtotime($this->getStartOfCollectionsEventInstance()->scheduled_datetime);
	     $iNow = strtotime("+$oScenario->day_offset days",$iCollectionsStart );
	     $sNow = date('Y-m-d', $iNow );
	     if ($oScenario->evaluateThresholdCriterion($this->getOverDueCollectableAmount($sNow),$this->getOverdueCollectableBalance($sNow)))
	         return true;

	     //If this is not the case, we will now check if there are any other collectables that are overdue, which taken into account will keep the account in collections
	     $mNow = self::SCENARIO_OFFSET_USED_TO_DETERMINE_EXIT_COLLECTIONS ? date('Y-m-d', strtotime("+$oScenario->day_offset days", time())) : Data_Source_Time::currentDate();
	     return $oScenario->evaluateThresholdCriterion($this->getOverDueCollectableAmount($mNow),$this->getOverdueCollectableBalance($mNow));
	 }
    }

    public function shouldCurrentlyBeInCollectionsOldAndIncorrect()
    {
	if ($this->isInSuspension())
	{
		return false;
	}
	//if they are not in collections: call 'getCollectionsStartDate'. If <= now, start collections
	 $oScenario  = $this->getCurrentScenarioInstance()->getScenario();
	 $oSourceCollectable = $this->getSourceCollectable();

	if (!$this->isCurrentlyInCollections())
	{
	    return $this->getCollectionsStartDate () <= Data_Source_Time::currentDate () && $oScenario->evaluateThresholdCriterion($oSourceCollectable->amount, $oSourceCollectable->balance);
	}
	//if they are in collectons:
	else
	{

	    if ( $oScenario->evaluateThresholdCriterion($oSourceCollectable->amount, $oSourceCollectable->balance))
	    {
		//if the  source collectable has not been paid off, stay in collections
		return true;
	    }
	    else
	    {
		$oScenario  = $this->getCurrentScenarioInstance()->getScenario();
		$mNow = self::SCENARIO_OFFSET_USED_TO_DETERMINE_EXIT_COLLECTIONS ? date('Y-m-d', strtotime("+$oScenario->day_offset days", time())) : null;
		$fBalance   = $this->getOverdueCollectableBalance($mNow);
		$fAmount    = $this->getOverDueCollectableAmount($mNow);
		return  $oScenario->evaluateThresholdCriterion($fAmount, $fBalance);
	    }
	}


    }


    /**
     * @method Account_Logic::scheduleNextScheduledScenarioEvent
     * If the account is currently in collections or should be in collections, the next scenario event is triggered,
     * based on the following rules:
     * - An event will be scheduled for accounts that are not in suspension AND are either currently in collections or have a due balance above the entry threshold
     * - If such an account is below the scenario exit threshold, the ExitCollections event is triggered
     * - Else: the next event is triggered, based on the collections start date of the account (if no previous event exists for the current scenario) OR on the most recent event of the current scenario
     *
     * - if it is a manually invoked event, it is only scheduled
     * - if it is a fully automated event, it is scheduled and invoked and completed
     *
     * It is the responsibility of the caller of this method to ensure that any pending changes of scenario
     * have been completed before calling this method, eg if there is a broken promise on the account, this should
     * have been dealt with before calling this method.
     *
     * Also, this method assumes that all distribution of balances from payments, adjustments, and credit collectables has been done. It only looks at collectable balances
     *
     */
    public function scheduleNextScenarioEvent()
    {
	$bShouldBeInCollections = $this->shouldCurrentlyBeInCollections();
	$bIsCurrentlyInCollections = $this->isCurrentlyInCollections();

        if ($bIsCurrentlyInCollections && !$bShouldBeInCollections)
        {
		$oEventInstance = Logic_Collection_Event_Instance::schedule($this, COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS);
		$oEventInstance->_registerWithArray();
	}
	else if ($bShouldBeInCollections)
	{
	    // Schedule the next scheduled event. Get the Logic_Collection_Scenario_Event object that should be scheduled
	    $oNextScenarioEvent = $this->getNextCollectionScenarioEvent();
	    if ($oNextScenarioEvent !== null)
	    {
		$oEventInstance = Logic_Collection_Event_Instance::schedule($this, $oNextScenarioEvent);
		if ($oEventInstance->getInvocationId() != COLLECTION_EVENT_INVOCATION_MANUAL)
		{
		    Log::getLog()->log("The event will be invoked and completed automatically");
		    $oEventInstance->_registerWithArray();
		}
		else
		{
		    Log::getLog()->log("The event will require manual completion through Flex");
		    Logic_Collection_BatchProcess_Report::addEvent($oEventInstance);
		}
	    }
	    else
	    {
		if ($this->previousEventNotCompleted())
		    Log::getLog()->log("No next event was scheduled because a previous event is still awaiting manual completion.");
		else
		    Log::getLog()->log("Day offset since last event did not result in next event");
		Logic_Collection_BatchProcess_Report::addAccount($this);
	    }

	}


    }

    public function processActivePromise()
    {
	$oPromise = $this->getActivePromise();
	if ($oPromise)
	    $oPromise->process();
    }

    /**
     * @param <type> to specify either debit or credit collectables
     * @return all collectables of the specified type with balance > 0
     */
    public function getCollectables($iType = Logic_Collectable::DEBIT)
    {
        if ($this->aCollectables === null)
        {
            $this->aCollectables[Logic_Collectable::DEBIT] = Logic_Collectable::getForAccount($this, true, Logic_Collectable::DEBIT);
            $this->aCollectables[Logic_Collectable::CREDIT] = Logic_Collectable::getForAccount($this, true,Logic_Collectable::CREDIT );
        }
        return $iType === null ? $this->aCollectables : $this->aCollectables [$iType];
    }

    public function getPayments($iPaymentNature = null, $bRefreshFromDatabase = false)
    {
        if ($this->aPayments === null)
        {
            $this->aPayments[PAYMENT_NATURE_PAYMENT] = Logic_Payment::getForAccount($this,PAYMENT_NATURE_PAYMENT);
            $this->aPayments[PAYMENT_NATURE_REVERSAL] = Logic_Payment::getForAccount($this,PAYMENT_NATURE_REVERSAL);
        }

        return $iPaymentNature=== null ? $this->aPayments : $this->aPayments[$iPaymentNature];
    }

    public function getAdjustments($iSignType = null, $bRefreshFromDatabase = false)
    {
        if ($this->aAdjustments === null)
        {
            $this->aAdjustments[Logic_Adjustment::DEBIT] = Logic_Adjustment::getForAccount($this,  Logic_Adjustment::DEBIT);
            $this->aAdjustments[Logic_Adjustment::CREDIT] = Logic_Adjustment::getForAccount($this, Logic_Adjustment::CREDIT);
        }
        return $iSignType === null ? $this->aAdjustments : $this->aAdjustments [$iSignType];
    }

    /**
     *
     * @param <type> debit or credit
     * @return <type> the balance of the collectables of the specified type
     */
    public function getCollectableBalance($iType = Logic_Collectable::DEBIT)
    {
        $aCollectable = $this->getCollectables($iType);
        $fTotalBalance = 0;
        foreach ($aCollectable as $oCollectable)
        {
            $fTotalBalance += $oCollectable->balance;
        }

        return $fTotalBalance;
    }



    /**
     *
     * @param <type> debit or credit
     * @return <type> the sum of amounts of the collectables of the specified type
     * NOTE: this will only include collectables that currently have balance!=o
     */
    public function getCollectableAmount($iType)
    {
	$aCollectable = $this->getCollectables($iType);
	$fTotalAmount = 0;
	foreach ($aCollectable as $oCollectable)
	{
	    $fTotalAmount += $oCollectable->amount;
	}

	return  $fTotalAmount;
    }

    /**
     * @return array of all payable items for the account, comprising objects of the following classes:
     *  - Logic_Collectable with amount > 0
     *  - Logic_Collection_Promise_Instalment that belong to a currently active promise
     * @return array of all payable items for the account (objects of the following classes: Logic_Collectable with amount > 0  and Logic_Collection_Promise_Instalment that belong to a currently active promise)
     */
    public function getPayables($bRefreshFromDatabase = false)
    {
        //return $this->oDO->getPayables();
        if ($this->_aPayables === null || $bRefreshFromDatabase)
                $this->_aPayables = $this->oDO->getPayables();

        return $this->_aPayables;
    }

    /**
     * equivalent to getCollectableBalance()
     * @return total a amount payable as the sum of the balance of all payables
     */
    public function getPayableBalance($bRefreshFromDatabase = false)
    {
       $fTotalBalance = 0;
       $aPayables = $this->getPayables($bRefreshFromDatabase);
        foreach ($aPayables as $oPayable)
        {
            $fTotalBalance += $oPayable->getBalance();
        }

        return $fTotalBalance;
    }

    /**
     *
     * @return sum of the amount of all payables retrieved through getPayables()
     */
    public function getPayableAmount()
    {
        $fTotalAmount = 0;
        foreach ($this->getPayables() as $oPayable)
        {
             $fTotalAmount += $oPayable->getAmount();
        }

        return  $fTotalAmount;
    }

    public function processDistributable($mDistributable)
    {
        $bDistributableIsCredit = $mDistributable->isCredit();
       //Log::getLog()->log("Before processing distributable $mDistributable->id ,".memory_get_usage(true));
        $aPayables = $bDistributableIsCredit ? $this->getPayables() : array_reverse($this->getPayables());

	for ($i=0;$i<count($aPayables);$i++)
	{
	    $oPayable = $aPayables[$i];

	    if ($bDistributableIsCredit && $oPayable->getBalance() > 0)
	    {
		$oPayable->processDistributable($mDistributable);

	    }
	    else if (!$bDistributableIsCredit && $oPayable->getBalance() < $oPayable->getAmount())
	    {
		$oPayable->processDistributable($mDistributable);
	    }

	    if ($mDistributable->balance == 0)
		    break;
	}
	unset($aPayables);
          //Log::getLog()->log("After processing distributable $mDistributable->id,".memory_get_usage(true));
    }



    public function getDistributableDebitBalance()
    {
        $fDebitBalance = 0;
        $aDebitAdjustments = $this->getAdjustments(Logic_Adjustment::DEBIT);
        $aReversedPayments = $this->getPayments(PAYMENT_NATURE_REVERSAL);
        foreach ($aDebitAdjustments as $oAdjustment)
        {
            $fDebitBalance += $oAdjustment->balance;
        }

        foreach ($aReversedPayments as $oPayment)
        {
            $fDebitBalance += $oPayment->balance;
        }

        return $fDebitBalance;
    }

    public function getDistributableCreditBalance()
    {
        $fCreditBalance = 0;
        $aCreditAdjustments = $this->getAdjustments(Logic_Adjustment::CREDIT);
        $aPayments = $this->getPayments(PAYMENT_NATURE_PAYMENT);
        $aCollectables = $this->getCollectables(Logic_Collectable::CREDIT);

        foreach ($aCollectables as $oCollectable)
        {
            $fCreditBalance -= $oCollectable->balance;
        }


        foreach ($aCreditAdjustments as $oAdjustment)
        {
            $fCreditBalance += $oAdjustment->balance;
        }

        foreach ($aPayments as $oPayment)
        {
            $fCreditBalance += $oPayment->balance;
        }

        return $fCreditBalance;

    }

    public function hasPayablesWithBalanceBelowAmount()
    {
        $aCollectables = $this->getPayables();
        foreach ($aCollectables as $oCollectable)
        {
            if ($oCollectable->amount > 0 && ($oCollectable->amount - $oCollectable->balance >0))
                    return true;
        }

        return false;

    }



    public function redistributeBalances()
    {
        //1. delete all records for this account in the following tables: collectable_adjustment; collectable_payment; collectable_transfer where collectable_transfer_type == COLLECTABLE_TRANSFER_TYPE_BALANCE
        Collectable_Adjustment::deleteForAccount($this->id);
        Collectable_Payment::deleteForAccount($this->id);
        Collectable_Transfer_Balance::deleteForAccount($this->id);

        //2. for each of the following tables, for records belonging to this account, set .balance = .amount: adjustment; payment; collectable
        Adjustment::resetBalanceForAccount($this->id);
        Payment::resetBalanceForAccount($this->id);
        Collectable::resetBalanceForAccount($this->id);


        $iIterations = 0;

        while ($iIterations<1 && (($this->getPayableBalance(TRUE) > 0 && $this->getDistributableCreditBalance() > 0)
                || ($this->hasPayablesWithBalanceBelowAmount() && $this->getDistributableDebitBalance() > 0)))
        {
            $iIterations++;
//            //Log::getLog()->log("Pre Iteration $iIterations");
//            //Log::getLog()->log("@@@@@ Account $this->id Collectable Balance: ".$this->getCollectableBalance(Logic_Collectable::DEBIT));
//            //Log::getLog()->log("Distributable Credit Balance: ".$this->getDistributableCreditBalance() );
//            //Log::getLog()->log("Distributable Debit Balance: ".$this->getDistributableDebitBalance());

            ////Log::getLog()->log("&&&CREDIT BALANCE REDISTRIBUTION &&&");
            //3. process all credit balances for this account in the tables mentioned in step 2. see below for the rules

           $aCreditCollectable = $this->getCollectables(Logic_Collectable::CREDIT);
            //Log::getLog()->log("after get credit collectables,".memory_get_usage(true));
            foreach ($aCreditCollectable as $oCollectable)
            {
                $this->processDistributable($oCollectable);
            }

           $aPayments = $this->getPayments(PAYMENT_NATURE_PAYMENT);
          //Log::getLog()->log("after get payments,".memory_get_usage(true));
            foreach ($aPayments as $oPayment)
            {

                $this->processDistributable($oPayment);
            }

            $aAdjustments = $this->getAdjustments(Logic_Adjustment::CREDIT);
            foreach ($aAdjustments as $oAdjustment)
            {
                $this->processDistributable($oAdjustment);
            }
            // self::$aMemory['after_processing_credits'] = memory_get_usage (TRUE );
            //4. process all debit balances for this account in the tables mentioned in step 2. see below for the rules
             ////Log::getLog()->log("&&&DEBIT BALANCE REDISTRIBUTION &&&");
            $aDebitAdjustments = $this->getAdjustments(Logic_Adjustment::DEBIT);
            foreach ( $aDebitAdjustments as $oAdjustment)
            {
                $this->processDistributable($oAdjustment);
            }
           // self::$aMemory['after_processing_debits'] = memory_get_usage (TRUE );
        }

//        $aCollectables = $this->getCollectables(Logic_Collectable::DEBIT);
//       $oReport = new Logic_Spreadsheet(array( 'account_id', 'amount', 'balance', 'created', 'due_date', 'promise_id', 'invoice_id', 'id'));
//        foreach ($aCollectables as $oCollectable)
//       {
//            $oReport->addRecord($oCollectable->toArray());
//        }
//
//       $sPath = FILES_BASE_PATH.'temp/';
//
//           $sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
//           $sFilename	= "$this->id"."_Collections_Balance_Redistribution_Report_$sTimeStamp.csv";
//          $oReport->saveAs( $sPath.$sFilename, "CSV");
//
//            //send the email
//            $sFile = file_get_contents($sPath.$sFilename);
//            $oEmail	=  new Email_Notification(1);
//            $oEmail->addAttachment($sFile, $sFilename, 'text/csv');
//            //$oEmail->setFrom('ybs-admin@ybs.net.au', 'Yellow Billing Services');
//            $oEmail->setSubject('Collections Balance Redistribution Report');
//           $oEmail->setBodyText("Report Testing");
//
//            $oEmail->send();
//       //Log::getLog()->log("Post:");
//       //Log::getLog()->log("Account Collectable Balance: ".$this->getCollectableBalance(Logic_Collectable::DEBIT));
//       //Log::getLog()->log("Distributable Credit Balance: ".$this->getDistributableCreditBalance() );
//       //Log::getLog()->log("Distributable Debit Balance: ".$this->getDistributableDebitBalance());
        return array('iterations'=>$iIterations, 'Debit Collectables' =>count($this->getCollectables()), 'Credit Collectables'=> count($aCreditCollectable) , 'Credit Payments' =>count($aPayments) , 'Credit Adjustments'=> count($aAdjustments), 'Debit Payments' => count($aReversedPayments) , 'Debit Adjustments' =>count($aDebitAdjustments), 'Balance'=>$this->getPayableBalance() );
    }

    /**
     * @return array of scenarios that are current for the account, this can be more than one
     * use getCurrentScenarioInstance to determine which one should apply to the account.
     */
    public function getActiveScenarios($bRefreshFromDatabase = false)
    {
        if ($this->aActiveScenarioInstances === null || $bRefreshFromDatabase)
	    $this->aActiveScenarioInstances = Logic_Collection_Scenario_Instance::getForAccount($this);

        if (count($this->aActiveScenarioInstances)== 0)
            throw new Logic_Collection_Exception("Configuration Error: no active Scenario for Account $this->id .");

	return $this->aActiveScenarioInstances;
    }

    /**
     *
     * @return Logic Scenario Instance object representing the scenario that currently applies
     * if more than one active scenarios are found, the scenario instance that was started most recently is selected
     */
    public function getCurrentScenarioInstance()
    {
       return end($this->getActiveScenarios());
    }

     /**
     *
     * @return Logic Scenario Instance object representing the scenario that would currently apply if there were no overrides
     * if more than one active scenarios are found, the scenario instance with the earliest created datetime is returned
     */
    public function getBaseScenarioInstance()
    {
        return reset($this->getActiveScenarios());
    }

   /**
    * Sets end date on the current Logic_Collection_Scenario_Instance (account_collection_scenario record)
    * and creates a new  Logic_Collection_Scenario_Instance to be the current one
    * saves changes to the database
    * @param <type> $iCollectionScenarioId
    *
    */
    public function setCurrentScenario($iCollectionScenarioId, $bEndCurrentScenario = true)
    {
	$this->resetScenario();
	$this->cancelScheduledScenarioEvents();

	if ($bEndCurrentScenario)
	{
	    $oCurrentScenario = $this->getBaseScenarioInstance();
	    $oCurrentScenario->end_datetime = date('Y-m-d H:i:s');
	    $oCurrentScenario->save();
	    $this->aActiveScenarioInstances = array();
	}


	$sNow = date('Y-m-d H:i:s');
	$oNewInstance 				= new Account_Collection_Scenario();
	$oNewInstance->account_id 			= $this->oDO->Id;
	$oNewInstance->collection_scenario_id 	= $iCollectionScenarioId;
	$oNewInstance->created_datetime		= $sNow;
	$oNewInstance->start_datetime		= $sNow;
	$oNewInstance->end_datetime                 = Data_Source_Time::END_OF_TIME;
	$oNewInstance->save();

	// Clear the cached list of instances, next time they are retrieved the new instance will be included
	$this->aActiveScenarioInstances[] = new Logic_Collection_Scenario_Instance($oNewInstance);

       $oScenario = Collection_Scenario::getForId($iCollectionScenarioId);
       if ($oScenario->initial_collection_severity_id !== null)
       {
	   $this->collection_severity_id = $oScenario->initial_collection_severity_id;
	   $this->save();
       }
    }

    /**
     * ends all scenario overrides that are current, resets the scenario that currently applies to be the oldest active scenario
     */
    public function resetScenario()
    {
         // End the current account_collection_scenario record
            $aScenarioInstances = $this->getActiveScenarios();
            $oBaseScenario = $this->getBaseScenarioInstance();
            foreach($aScenarioInstances as $oScenarioInstance)
            {
                if ($oScenarioInstance->id != $oBaseScenario->id)
                {
                    $oScenarioInstance->end_datetime = date('Y-m-d H:i:s');
                    $oScenarioInstance->save();
                }
            }
	    //this will refresh the $this->aActiveScenarios data member
	    $this->getActiveScenarios(TRUE);
    }

   /**
    * Simply checks for an effective end_datetime on suspensions
    * This method is only guaranteed to reflect reality if first either Logic_Suspension::processForAccount($iAccountId) or Logic_Suspension::batchProcess is invoked
    */
    public function isInSuspension()
    {
	$oSuspension =  Collection_Suspension::getActiveForAccount($this->id);
	$bInsuspension =  ($oSuspension!== null);
	return $bInsuspension;
    }

    /**
     * Returns a Logic_Collection_Promise object representing the active promise to pay on this account, or null if there is none
     */
    public function getActivePromise()
    {
	if ($this->oActivePromise === NULL)
		$this->oActivePromise = Logic_Collection_Promise::getForAccount($this);
	return $this->oActivePromise;
    }

    /**
     * Logic_Account::getSourceCollectable
     * @return Logic_Collectable -  If the account is currently in collections, return the collecatble that triggered it into collections
     *				    Else - the collectable with the oldest due date that has a balance > 0 and is not part of an active promise
     */
    public function getSourceCollectable()
    {
	$oCurrentScenario = $this->getCurrentScenarioInstance()->getScenario();
	$oMostRecentEventInstance = $this->getMostRecentCollectionEventInstance();
	$iMostRecentEventScenario    = $oMostRecentEventInstance!= null && $oMostRecentEventInstance->getScenario()!=null ? $oMostRecentEventInstance->getScenario()->id : null;

	if ($iMostRecentEventScenario === $oCurrentScenario->id)
	{
	    return Logic_Collectable::getInstance($this->getMostRecentCollectionEventInstance()->collectable_id);
	}
	else
	{
	    $aCollectables	= $this->getCollectables();
	    $oOldest	= null;
	    $iOldestDueDate = null;
	    foreach ($aCollectables as $oCollectable)
	    {
		if (!$oCollectable->belongsToActivePromise() && $oCollectable->balance > 0)
		{
		    $iDueDate		= strtotime($oCollectable->due_date." 23:59:59");
		    if (($oOldest == null) || ($iDueDate < $iOldestDueDate))
		    {
			$oOldest = $oCollectable;
			$iOldestDueDate = $iDueDate;
		    }
		}
	    }
	    return $oOldest;
	}
    }

    /**
     * @method Account_Logic::getCurrentDueDate
     * Returns the due date that will be the point of reference to work out which event should be triggered.
     * the date returned will be either:
     *  - the due date of the collectable that triggered most recent Logic_Collection_Event_Instance (account_collection_event_history record) IF that event instance was not the 'exit collections' event
     *  - the due date of the oldest currently open collectable that is not part of an active promise to pay
     * This function does not check whether a promise is in a 'broken' state but has not been processed as such. It is the caller's responsibility to first process the promises that are active on the account
     * This function does not evaluate the source collectable amount and balance against the scenario threshold criteron
     */
    public function getCurrentDueDate()
    {
        $mSourceCollectable =  $this->getSourceCollectable();
	return $mSourceCollectable === null ? Data_Source_Time::END_OF_TIME : $mSourceCollectable->due_date;
    }

    /**
     *	This method returns a date based on the source collectable due date and scenario day offset.
     *	This function does not evaluate the source collectable amount and balance against the scenario threshold criteron, so in itself is not sufficient to determine whether collections should start.
     * @return <type>
     */
    public function getCollectionsStartDate()
    {
	$iOffset = $this->getCurrentScenarioInstance()->getScenario()->day_offset;
	$sDueDate = $this->getCurrentDueDate();
	$iDueDate = strtotime($sDueDate);
	$iOverDueDate = strtotime("+1 day", $iDueDate);
	$iStartDate = strtotime("-$iOffset day", $iOverDueDate);
	$sStartDate = date ("Y-m-d", $iStartDate);
	Log::getLog()->log("Scenario day offset: $iOffset");
	Log::getLog()->log("account due date: ".$sDueDate."; collections date: $sStartDate");
	return $sStartDate;
    }

    public function setException($e)
    {
        $this->oException = $e;
    }

    public function getException()
    {
        return $this->oException;
    }

    public function hasPendingOCAReferral()
    {
        $mReferral = Account_OCA_Referral::getForAccountId($this->Id, ACCOUNT_OCA_REFERRAL_STATUS_PENDING);
        return $mReferral!== null ? $mReferral[0]->account_oca_referral_status_id === ACCOUNT_OCA_REFERRAL_STATUS_PENDING : FALSE;
    }

    public function cancelPendingOCAReferral()
    {
        $mReferral = Account_OCA_Referral::getForAccountId($this->Id, ACCOUNT_OCA_REFERRAL_STATUS_PENDING);
        if ($mReferral!== NULL)
        {
            $mReferral[0]->cancel();
        }
    }

    public static function clearCache($bClearRelatedCaches = true)
    {
        self::$aInstances = array();
        Account::emptyCache();
        if ($bClearRelatedCaches)
        {
            Logic_Collectable::clearCache();
            Payment::clearCache();
            Adjustment::clearCache();
            Collection_Promise::clearCache();
            Collection_Promise_Instalment::clearCache();
            Collectable_Payment::clearCache();
            Collectable_Adjustment::clearCache();
            Collectable_Transfer_Balance::clearCache();
            Payment_Nature::clearCache();
        }
        memory_get_usage (TRUE );
    }

    public static function batchRedistributeBalances($aAccounts)
    {
        self::$aMemory['before_looping'] = memory_get_usage (TRUE );
       // //Log::getLog()->log(count($aAccounts)." Accounts");

        foreach ($aAccounts as $iIndex => $oAccountORM)
        {
	    $oDataAccess = DataAccess::getDataAccess();
            $oDataAccess->TransactionStart();
            try
            {

            //Log::getLog()->log("Before processing account $oAccountORM->Id,".  memory_get_usage(true));
            $oAccount = self::getInstance($oAccountORM);
            $iId = $oAccount->Id;
            $oStopwatch = Logic_Stopwatch::getInstance(true);
            $oStopwatch->start();
             //Log::getLog()->log("Instantiated logic account $oAccountORM->Id,".  memory_get_usage(true));
	     $fStartCollectableBalance = $oAccount->getPayableBalance();
	     $fAccountBalance = $oAccount->getAccountBalance();
            $aResult = $oAccount->redistributeBalances();
            $time = $oStopwatch->split();
	     $fOverdueBalance = $oAccount->getOverdueCollectableBalance($mNow);
            self::$aMemory['after_before_cache_clear'] = memory_get_usage (TRUE );
	   
            $oAccount->reset();
            unset($oAccount);
            unset($oStopwatch);
            unset ($aAccounts[$iIndex]);
            self::clearCache();
            $iMemory = (memory_get_usage (TRUE ));

            self::$aMemory['after_after_cache_clear'] = $iMemory;
          Log::getLog()->log("$iId, $time,  $iMemory , ".self::$_aTime['delete_linking_data'].",".self::$_aTime['reset_balances'].",".$aResult['iterations'].",".$aResult['Debit Collectables'].",".$aResult['Credit Collectables'].",".$aResult['Credit Payments'].",".$aResult['Credit Adjustments'].",".$aResult['Debit Payments'].",".$aResult['Debit Adjustments'].",".$fAccountBalance.",".$fStartCollectableBalance.",".$aResult['Balance'].",".$fOverdueBalance);
           // foreach(self::$aMemory as $key => $value)
           // {
           //     //Log::getLog()->log("$key, $value");
           // }

            //Log::getLog()->log("After processing account $oAccountORM->Id,".  memory_get_usage(true));
            //Log::getLog()->log(" ");
             $oDataAccess->TransactionCommit();
            }
            catch(Exception $e)
            {
                $oDataAccess->TransactionRollback();
                if ($e instanceof Exception_Database)
                    throw $e;


            }

        }
    }

    public function reset()
    {
        unset($this->oDO);
        unset($this->aActiveScenarioInstances);
        unset($this->aCollectables);
        unset($this->aPayments);
        unset($this->aAdjustments);
        unset($this->oActivePromise);
        unset($this->_aPayables);
    }

    public static function batchProcessCollections(&$aAccounts)
    {

        Log::getLog()->log("-------Starting Account Batch Collections Process-------------------------");

            foreach ($aAccounts as $oAccount)
            {
                if (!$oAccount->noNextEventFound())
		{
		    $oDataAccess	= DataAccess::getDataAccess();
		    $oDataAccess->TransactionStart();
		    try
		    {
			Log::getLog()->log("Trying to schedule next event for account $oAccount->Id ");
			Logic_Stopwatch::getInstance()->lap();
			$oAccount->scheduleNextScenarioEvent();
			//if no event was scheduled, no need to include this account in the next batch process iteration
			if ($oAccount->noNextEventFound())
			    unset($aAccounts[$oAccount->id]);

			Log::getlog()->log("Processed account $oAccount->Id in : ".Logic_Stopwatch::getInstance()->lap());
			$oDataAccess->TransactionCommit();
		    }

		    catch (Exception $e)
		    {
			    // Exception caught, rollback db transaction
		       $oDataAccess->TransactionRollback();

			if ($e instanceof Exception_Database)
			{
			    throw $e;
			}
			else
			{
			    $oAccount->setException($e);
			    Logic_Collection_BatchProcess_Report::addAccount($oAccount);
			}

		    }
		}

            }

           return  Logic_Collection_Event_Instance::completeWaitingInstances();


    }

    public static function getForId($iId)
    {
        return self::getInstance($iId);
    }

    public function __get($sField)
    {
    	if ($sField == 'id')
            $sField = 'Id';
        return $this->oDO->{$sField};
    }

     private function __call($function, $args)
    {
        return call_user_func_array(array($this->oDO, $function),$args);
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
		return $this->oDO->toArray();
    }

    public function display()
    {

        ////Log::getLog()->log('%%%%%%%%%%%%%%%%%%%%%%%Details for Account '.$this->id.'%%%%%%%%%%%%%%%%%');
       $this->getOverdueCollectableBalance();
       $this->getOverDueCollectableAmount();
        $this->isCurrentlyInCollections();
        $this->isInSuspension();
        ////Log::getLog()->Log('Severity Level: '.$this->collection_severity_id);
        ////Log::getLog()->log('////////Active Scenario Instances:');
        $aScenarioInstances = $this->getActiveScenarios();
        foreach ($aScenarioInstances as $oInstance)
        {
            $oCurrentInstance = $this->getCurrentScenarioInstance();
            ////Log::getLog()->log('Scenario Instance id '.$oInstance->id.'(Scenario id: '.$oInstance->collection_scenario_id.')');
            if ($oCurrentInstance->id == $oInstance->id)
            {
                    ////Log::getLog()->log('THIS IS THE CURRENT SCENARIO ON ACCOUNT '.$this->id);
                    $oInstance->display();
            }
        }
        ////Log::getLog()->log('%%%%%%%%%%%%%%%%%%%%%%%End Details for Account '.$this->id.'%%%%%%%%%%%%%%%%%');
    }



    /**
     * getAccountsForBatchCollectionProcess
     * @return array of Account_Logic objects that should be processed
     * These accounts are not currently suspended from collections, and either:
     * 1 are currently in collections, defined by most recent account_collection_event_history record not being for the 'exit collections' event
     * 2 OR are not in collections (as defined under 1) but have collectables with a balance > 0 that are not part of an active promise
     *
     */
    public static function getForBatchCollectionProcess($aExcludedAccounts = null)
    {

        $aAccounts = array();
	Logic_Stopwatch::getInstance()->start();
	Log::getLog()->log("about to retrieve orms");
        $aAccountORMs = Account::getForBatchCollectionsProcess($aExcludedAccounts);
	Log::getLog()->log("retrieved accounts in ".Logic_Stopwatch::getInstance()->lap());
        if ($aAccountORMs != null)
        {
            foreach($aAccountORMs as $oORM)
            {
		$aAccounts[$oORM->Id] =self::getInstance($oORM, TRUE);
            }
        }
	
	Log::getLog()->log("Number of accounts: ".count($aAccounts));
	Log::getLog()->log("finished instantiating logic in ".Logic_Stopwatch::getInstance()->lap());
        return $aAccounts;
    }

	public static function countForCollectionsLedger($aFilter=null)
	{
		// Generate search table (temporary) and where clause data
		self::_buildSearchTableForCollectionsLedger($aFilter);

		// Get the count of the unlimited results
		$oSearchCountSelect	=	new StatementSelect(
										'account_collection_ledger',
										'COUNT(DISTINCT account_id) AS count',
										'',
										'',
										''
									);
		if ($oSearchCountSelect->Execute() === FALSE)
		{
			throw new Exception_Database("Failed to retrieve record count, query - ". $oSearchCountSelect->Error());
		}

		$aCount	= $oSearchCountSelect->Fetch();
		return $aCount['count'];
	}

	public static function searchAndCountForCollectionsLedger($iLimit=null, $iOffset=null, $aSort=null, $aFilter=null)
	{
		// ORDER BY clause (with field alias' for category and type)
		$sOrderByClause = StatementSelect::generateOrderBy(null, $aSort);

		// LIMIT clause
		$sLimitClause = StatementSelect::generateLimit($iLimit, $iOffset);

		// Generate search table (temporary) and where clause data
		self::_buildSearchTableForCollectionsLedger($aFilter);

		// Get the count of the unlimited results
		$oSearchCountSelect	=	new StatementSelect(
											'account_collection_ledger',
											'COUNT(DISTINCT account_id) AS count',
											'1',
											'',
											''
										);
		if ($oSearchCountSelect->Execute() === FALSE)
		{
			throw new Exception_Database("Failed to retrieve record count, query - ". $oSearchCountSelect->Error());
		}

		$aCount = $oSearchCountSelect->Fetch();

		// Get the limited + offset results
		$oSearchSelect =	new StatementSelect(
										'account_collection_ledger',
										'*',
										'1',
										$sOrderByClause,
										$sLimitClause
									);
		if ($oSearchSelect->Execute() === FALSE)
		{
			throw new Exception_Database("Failed to retrieve records, query - ". $oSearchSelect->Error());
		}

		// Return the results as well as the count
		return array('aData' => $oSearchSelect->FetchAll(), 'iCount' => $aCount['count']);
	}

	public static function searchForCollectionsLedger($iLimit=null, $iOffset=null, $aSort=null, $aFilter=null, $bCountOnly=false)
	{
		// ORDER BY clause (with field alias' for category and type)
		$sOrderByClause	= StatementSelect::generateOrderBy(null, $aSort);

		// LIMIT clause
		$sLimitClause = StatementSelect::generateLimit($iLimit, $iOffset);

		// Generate search table (temporary) and where clause data
		self::_buildSearchTableForCollectionsLedger($aFilter);

		// Get the limited + offset results
		$oSearchSelect	=	new StatementSelect(
								'account_collection_ledger',
								'*',
								'1',
								$sOrderByClause,
								$sLimitClause
							);
		if ($oSearchSelect->Execute() === FALSE)
		{
			throw new Exception_Database("Failed to retrieve records, query - ". $oSearchSelect->Error());
		}

		$mRecords	= $oSearchSelect->FetchAll();
		return ($mRecords === null ? array() : $mRecords);
	}

	private static function _buildSearchTableForCollectionsLedger($aFilter=null)
	{
		// Create temporary table 'account_collection_ledger'
		$oQuery					= new Query();
		$mTempTableCheckResult	= $oQuery->Execute("SELECT	count(*)
													FROM 	account_collection_ledger");
		if ($mTempTableCheckResult === false)
		{
			// Create the temp table
			$mResult	= 	$oQuery->Execute(
								"	CREATE TEMPORARY TABLE account_collection_ledger
									(
										account_id BIGINT UNSIGNED,
										account_collection_event_history_id BIGINT UNSIGNED,
										account_collection_event_status_id INT UNSIGNED,
										account_collection_event_status_name VARCHAR(256),
										account_name VARCHAR(256),
										collection_event_name VARCHAR(256),
										collection_event_type_id INT UNSIGNED,
										collection_event_type_implementation_id INT UNSIGNED,
										completed_datetime DATETIME,
										current_suspension_end_datetime DATETIME,
										customer_group_id BIGINT UNSIGNED,
										customer_group_name VARCHAR(256),
										next_promise_instalment_due_date DATE,
										previous_promise_instalment_due_date DATE,
										scenario_id BIGINT UNSIGNED,
										scenario_name VARCHAR(256),
										scenario_description VARCHAR(256),
										scenario_day_offset INT,
										scenario_status_id INT UNSIGNED,
										last_payment_amount DECIMAL(13,4),
										last_payment_paid_date DATE,
										overdue_amount_from_1_30 DECIMAL(13,4),
										overdue_amount_from_30_60 DECIMAL(13,4),
										overdue_amount_from_60_90 DECIMAL(13,4),
										overdue_amount_from_90_on DECIMAL(13,4),
										collection_event_invocation_id INT UNSIGNED,
										balance DECIMAL(13,4),
										overdue_balance DECIMAL(13,4),
										collection_status VARCHAR(256),
										INDEX in_account_collection_ledger_account_id (account_id),
										INDEX in_account_collection_ledger_account_collection_event_history_id (account_collection_event_history_id),
										INDEX in_account_collection_ledger_account_collection_event_status_id (account_collection_event_status_id),
										INDEX in_account_collection_ledger_collection_event_status_name (account_collection_event_status_name),
										INDEX in_account_collection_ledger_account_name (account_name),
										INDEX in_account_collection_ledger_collection_event_name (collection_event_name),
										INDEX in_account_collection_ledger_collection_event_type_id (collection_event_type_id),
										INDEX in_account_collection_ledger_event_type_implementation_id (collection_event_type_implementation_id),
										INDEX in_account_collection_ledger_completed_datetime (completed_datetime),
										INDEX in_account_collection_ledger_current_suspension_end_datetime (current_suspension_end_datetime),
										INDEX in_account_collection_ledger_customer_group_id (customer_group_id),
										INDEX in_account_collection_ledger_customer_group_name (customer_group_name),
										INDEX in_account_collection_ledger_next_instalment_due_date (next_promise_instalment_due_date),
										INDEX in_account_collection_ledger_previous_instalment_due_date (previous_promise_instalment_due_date),
										INDEX in_account_collection_ledger_scenario_id (scenario_id),
										INDEX in_account_collection_ledger_scenario_name (scenario_name),
										INDEX in_account_collection_ledger_scenario_description (scenario_description),
										INDEX in_account_collection_ledger_scenario_day_offset (scenario_day_offset),
										INDEX in_account_collection_ledger_scenario_status_id (scenario_status_id),
										INDEX in_account_collection_ledger_last_payment_amount (last_payment_amount),
										INDEX in_account_collection_ledger_last_payment_paid_date (last_payment_paid_date),
										INDEX in_account_collection_ledger_overdue_amount_from_1_30 (overdue_amount_from_1_30),
										INDEX in_account_collection_ledger_overdue_amount_from_30_60 (overdue_amount_from_30_60),
										INDEX in_account_collection_ledger_overdue_amount_from_60_90 (overdue_amount_from_60_90),
										INDEX in_account_collection_ledger_overdue_amount_from_90_on (overdue_amount_from_90_on),
										INDEX in_account_collection_ledger_collection_event_invocation_id (collection_event_invocation_id),
										INDEX in_account_collection_ledger_balance (balance),
										INDEX in_account_collection_ledger_overdue_balance (overdue_balance),
										INDEX in_account_collection_ledger_collection_status (collection_status)
									)"
							);
			if ($mResult === false)
			{
				throw new Exception_Database("Error creating temporary table. Database Error = ".$oQuery->Error());
			}
		}
		else
		{
			// The table has already been created, clear it's contents for this search
			$oQuery->Execute("	DELETE FROM account_collection_ledger
								WHERE 		1");
		}

		$sSelectQuery 	= self::_buildAccountLedgerQuery($aFilter);

		//file_put_contents('/home/rmctainsh/log.txt', $sSelectQuery);

		$sInsert 		= "	INSERT INTO account_collection_ledger(
											account_id,
											account_collection_event_history_id,
											account_collection_event_status_id,
											account_collection_event_status_name,
											account_name,
											collection_event_name,
											collection_event_type_id,
											collection_event_type_implementation_id,
											completed_datetime,
											current_suspension_end_datetime,
											customer_group_id,
											customer_group_name,
											next_promise_instalment_due_date,
											previous_promise_instalment_due_date,
											scenario_id,
											scenario_name,
											scenario_description,
											scenario_day_offset,
											scenario_status_id,
											last_payment_amount,
											last_payment_paid_date,
											overdue_amount_from_1_30,
											overdue_amount_from_30_60,
											overdue_amount_from_60_90,
											overdue_amount_from_90_on,
											collection_event_invocation_id,
											balance,
											overdue_balance,
											collection_status
										)
							(
								{$sSelectQuery}
							);";
		$mInsertResult = $oQuery->Execute($sInsert);
		if ($mInsertResult === false)
		{
			throw new Exception_Database("Error inserting data into temporary table. Database Error = ".$oQuery->Error());
		}
	}

	private static function _buildAccountLedgerQueryWhereClause($aFilter)
	{
		$aWherePieces = array("a.Archived = 0");
		foreach ($aFilter as $sFilter => $mValue)
		{
			$sValue = $mValue;
			$sField	= $sFilter;
			switch ($sFilter)
			{
				case 'account_id':
					$sField = "a.Id";
					break;

				case 'customer_group_id':
					$sField = "a.CustomerGroup";
					break;

				case 'collection_scenario_id':
					$sField = "cs.id";
					break;

				case 'collection_status':
					$sField	= "	(
									CASE
										WHEN next_promise_instalment.id is NOT NULL
										THEN 'PROMISE_TO_PAY'
										WHEN current_suspension.id IS NOT NULL
										THEN 'SUSPENDED'
										WHEN (latest_event_history.id IS NOT NULL)
											 AND (latest_event_type_implementation.system_name <> 'EXIT_COLLECTIONS')
										THEN 'IN_COLLECTIONS'
										ELSE 'NOT_IN_COLLECTIONS'
									END
								)";
					$sValue	= "'{$mValue}'";
					break;
			}

			$aWherePieces[] = "{$sField} = {$sValue}";
		}
		return "WHERE ".implode(' AND ', $aWherePieces);
	}

	private static function _buildAccountLedgerQuery($aFilter)
	{
		$sWhereClause = self::_buildAccountLedgerQueryWhereClause($aFilter);
		return "SELECT		a.Id AS account_id,
							latest_event_history.id AS account_collection_event_history_id,
							latest_event_history.account_collection_event_status_id,
							latest_event_status.name AS account_collection_event_status_name,
							a.BusinessName AS account_name,
							latest_event.name AS collection_event_name,
							latest_event.collection_event_type_id,
							latest_event_type.collection_event_type_implementation_id,
							latest_event_history.completed_datetime AS completed_datetime,
							current_suspension.proposed_end_datetime AS current_suspension_end_datetime,
							a.CustomerGroup AS customer_group_id,
							cg.internal_name AS customer_group_name,
							next_promise_instalment.due_date AS next_promise_instalment_due_date,
							previous_promise_instalment.due_date AS previous_promise_instalment_due_date,
							cs.id AS scenario_id,
							cs.name AS scenario_name,
							cs.description AS scenario_description,
							cs.day_offset AS scenario_day_offset,
							cs.working_status_id AS scenario_status_id,
							last_payment.amount AS last_payment_amount,
							last_payment.paid_date AS last_payment_paid_date,
							SUM(IF(NOW() BETWEEN (c.due_date + INTERVAL 1 DAY) AND (c.due_date + INTERVAL 30 DAY), c.balance, 0)) AS overdue_amount_from_1_30,
							SUM(IF(NOW() BETWEEN (c.due_date + INTERVAL 30 DAY) AND (c.due_date + INTERVAL 60 DAY), c.balance, 0)) AS overdue_amount_from_30_60,
							SUM(IF(NOW() BETWEEN (c.due_date + INTERVAL 60 DAY) AND (c.due_date + INTERVAL 90 DAY), c.balance, 0)) AS overdue_amount_from_60_90,
							SUM(IF(NOW() > (c.due_date + INTERVAL 90 DAY), c.balance, 0)) AS overdue_amount_from_90_on,
							(
								CASE
									WHEN	latest_event_type_implementation.enforced_collection_event_invocation_id IS NOT NULL	THEN	latest_event_type_implementation.enforced_collection_event_invocation_id
									WHEN	latest_event_type.collection_event_invocation_id IS NOT NULL							THEN	latest_event_type.collection_event_invocation_id
									WHEN	latest_scenario_event.collection_event_invocation_id IS NOT NULL						THEN	latest_scenario_event.collection_event_invocation_id
									WHEN	latest_event.collection_event_invocation_id IS NOT NULL									THEN	latest_event.collection_event_invocation_id
									ELSE	NULL
								END
							) AS collection_event_invocation_id,
							(
								COALESCE(SUM(c.amount), 0)
								+
								COALESCE(total_payments.total_amount, 0)
								+
								COALESCE(
									(
										SELECT 	SUM(adj.amount * tn.value_multiplier * adjn.value_multiplier)
										FROM	adjustment adj
										JOIN	adjustment_type adjt ON (adjt.id = adj.adjustment_type_id)
										JOIN	transaction_nature tn ON (tn.id = adjt.transaction_nature_id)
										JOIN  	adjustment_review_outcome aro ON (aro.id = adj.adjustment_review_outcome_id)
										JOIN  	adjustment_review_outcome_type arot ON (arot.id = aro.adjustment_review_outcome_type_id AND arot.system_name = 'APPROVED')
										JOIN	adjustment_nature adjn ON (adjn.id = adj.adjustment_nature_id)
										JOIN	adjustment_status adjs ON (adjs.id = adj.adjustment_status_id AND adjs.system_name = 'APPROVED')
										WHERE	adj.account_id = a.Id
									)
								, 0)
							) AS balance,
							(
								COALESCE(SUM(IF(c.due_date < NOW(), c.amount, 0)), 0)
								+
								COALESCE(total_payments.total_amount, 0)
								+
								COALESCE(
									(
										SELECT 	SUM(adj.amount * tn.value_multiplier * adjn.value_multiplier)
										FROM	adjustment adj
										JOIN	adjustment_type adjt ON (adjt.id = adj.adjustment_type_id)
										JOIN	transaction_nature tn ON (tn.id = adjt.transaction_nature_id)
										JOIN  	adjustment_review_outcome aro ON (aro.id = adj.adjustment_review_outcome_id)
										JOIN  	adjustment_review_outcome_type arot ON (arot.id = aro.adjustment_review_outcome_type_id AND arot.system_name = 'APPROVED')
										JOIN	adjustment_nature adjn ON (adjn.id = adj.adjustment_nature_id)
										JOIN	adjustment_status adjs ON (adjs.id = adj.adjustment_status_id AND adjs.system_name = 'APPROVED')
										WHERE	adj.account_id = a.Id
									)
								, 0)
							) AS overdue_balance,
							(
								CASE
									WHEN next_promise_instalment.id is NOT NULL
									THEN 'PROMISE_TO_PAY'
									WHEN current_suspension.id IS NOT NULL
									THEN 'SUSPENDED'
									WHEN (latest_event_history.id IS NOT NULL)
										 AND (latest_event_type_implementation.system_name <> 'EXIT_COLLECTIONS')
									THEN 'IN_COLLECTIONS'
									ELSE 'NOT_IN_COLLECTIONS'
								END
							) AS collection_status
				FROM		Account	a
				JOIN		CustomerGroup cg ON (cg.Id = a.CustomerGroup)
				JOIN		collectable c ON (c.account_id = a.Id)
				JOIN        (
			                    SELECT      a.Id AS account_id, COALESCE(SUM(p.amount * pn.value_multiplier), 0) AS total_amount
			                    FROM        Account a
			                    LEFT JOIN   payment p ON (p.account_id = a.Id)
			                    LEFT JOIN   payment_nature pn ON (pn.id = p.payment_nature_id)
			                    GROUP BY    a.Id
			                ) total_payments ON (total_payments.account_id = a.Id)
				LEFT JOIN	account_collection_scenario acs ON (
								acs.id = (
									SELECT	id
									FROM	account_collection_scenario USE INDEX (fk_account_collection_scenario_account_id)
									WHERE	account_id = a.Id
									AND		NOW() BETWEEN start_datetime AND end_datetime
									ORDER BY created_datetime DESC
									LIMIT	1
								)
							)
				LEFT JOIN	collection_scenario cs ON (cs.id = acs.collection_scenario_id)
				LEFT JOIN	account_collection_event_history latest_event_history ON (
								latest_event_history.id = (
									SELECT	id
									FROM	account_collection_event_history USE INDEX (fk_account_collection_event_history_account_id)
									WHERE	account_id = a.Id
									ORDER BY scheduled_datetime DESC, completed_datetime DESC
									LIMIT	1
								)
							)
				LEFT JOIN	account_collection_event_status latest_event_status ON (latest_event_status.id = latest_event_history.account_collection_event_status_id)
				LEFT JOIN	collection_event latest_event ON (latest_event.id = latest_event_history.collection_event_id)
				LEFT JOIN	collection_scenario_collection_event latest_scenario_event ON (latest_scenario_event.id = latest_event_history.collection_scenario_collection_event_id)
				LEFT JOIN	collection_event_type latest_event_type ON (latest_event_type.id = latest_event.collection_event_type_id)
				LEFT JOIN	collection_event_type_implementation latest_event_type_implementation ON (latest_event_type_implementation.id = latest_event_type.collection_event_type_implementation_id)
				LEFT JOIN	payment last_payment ON (
								last_payment.id = (
									SELECT	    id
									FROM        payment USE INDEX (fk_payment_tbl_account_id)
									WHERE       account_id = a.Id
									ORDER BY    paid_date DESC
									LIMIT	1
								)
							)
				LEFT JOIN	collection_suspension current_suspension ON (
								current_suspension.id = (
									SELECT	id
									FROM	collection_suspension USE INDEX (fk_collection_suspension_account_id)
									WHERE	account_id = a.Id
									AND		effective_end_datetime IS NULL
									AND		NOW() BETWEEN start_datetime AND proposed_end_datetime
									LIMIT	1
								)
							)
				LEFT JOIN	collection_promise_instalment next_promise_instalment ON (
								next_promise_instalment.id = (
									SELECT	cpi.id
									FROM	collection_promise cp USE INDEX (fk_collection_promise_account_id)
									JOIN	collection_promise_instalment cpi ON (cp.id = cpi.collection_promise_id)
									WHERE	cp.account_id = a.Id
									AND		cp.completed_datetime IS NULL
									AND		cpi.due_date >= NOW()
									ORDER BY cpi.due_date
									LIMIT	1
								)
							)
				LEFT JOIN	collection_promise_instalment previous_promise_instalment ON (
								previous_promise_instalment.id = (
									SELECT	cpi.id
									FROM	collection_promise cp USE INDEX (fk_collection_promise_account_id)
									JOIN	collection_promise_instalment cpi ON (cp.id = cpi.collection_promise_id)
									WHERE	cp.account_id = a.Id
									AND		cp.completed_datetime IS NULL
									AND		cpi.due_date < NOW()
									ORDER BY cpi.due_date DESC
									LIMIT	1
								)
							)
				{$sWhereClause}
				GROUP BY a.Id";
	}
}



?>
