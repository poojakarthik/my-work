<?php

/**
 * Description of Account_Logic
 *
 * @author JanVanDerBreggen
 */
class Logic_Account implements DataLogic
{
	const DEBUG_LOGGING = false;

	const			SCENARIO_OFFSET_USED_TO_DETERMINE_EXIT_COLLECTIONS = FALSE;
	const			CACHE_MODE_BYPASS = 'bypass';
	const			CACHE_MODE_REFRESH = 'refresh';

	protected		$oDO;
	protected		$aActiveScenarioInstances;

	protected		$aCollectables;
	protected		$aPayments;
	protected		$aAdjustments;
	protected		$_aPayables;
	protected		$oActivePromise;

	public static   $_aTime = array();
	public static   $aMemory = array();

	protected		$bPreviousEventNotCompleted = false;
	protected		$bNoNextEventFound = false;
	protected		$oException;

	protected static $aInstances = array();


	public static function getInstance($mDefinition, $sCacheMode = NULL)
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

		if ($sCacheMode === self::CACHE_MODE_BYPASS)
			return new self($oDO);

		if (in_array($oDO->Id, array_keys(self::$aInstances)))
		{
			if ($sCacheMode === self::CACHE_MODE_REFRESH)
				self::$aInstances[$oDO->Id]->refreshData($oDO);
			return self::$aInstances[$oDO->Id];
		}
		else
		{
			$oInstance = new self($oDO);
			self::$aInstances[$oDO->Id]	= $oInstance;
			return $oInstance;
		}

	}

	private static function importCache($aCache)
	{
		self::$aInstances = $aCache;
	}

	private function refreshData(Account $oDO)
	{
		$this->oDO = $oDO;
		$this->aActiveScenarioInstances = NULL;
		$this->aCollectables = NULL;
		$this->aPayments  = NULL;
		$this->aAdjustments = NULL;
		$this->_aPayables = NULL;
		$this->oActivePromise = NULL;
		$this->bPreviousEventNotCompleted = NULL;
		$this->bNoNextEventFound = FALSE;
		$this->oException = NULL;
	}

	private function __construct($mDefinition)
	{
		$this->oDO = is_numeric($mDefinition) ? Account::getForId($mDefinition) : (get_class($mDefinition) == 'Account' ? $mDefinition : null);
	}



	/**
	 * Returns the sum of all collectable balance for this account
	 * only collectables that are currently due are taken into account
	 * collectables that are part of an active promise are excluded
	 * */
	public function getOverdueCollectableBalance($sNow = null, $bBypassCache = false)
	{

	   $aCollectables = $this->getCollectables(Logic_Collectable::DEBIT, $bBypassCache);

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
	 * $bIgnoreDayOffsetRules should be set to TRUE if you just want to see which event is 'next in line', whether or not is qualifies to run today or not
	 */

//	public function getNextCollectionScenarioEvent($bIgnoreDayOffsetRules = FALSE)
//	{
//		$oMostRecentEventInstance 		= $this->getMostRecentCollectionEventInstance();
//		if (!$bIgnoreDayOffsetRules && ($oMostRecentEventInstance!== null && $oMostRecentEventInstance->completed_datetime === null) )
//		{
//		   $this->bPreviousEventNotCompleted = true;
//		   return null;
//		}
//		else
//		{
//		   $this->bPreviousEventNotCompleted = false;
//		}
//		$oScenario	= $this->getCurrentScenarioInstance();
//		$oNextEvent = $oScenario->getNextScheduledEvent($oMostRecentEventInstance,  $this->getCollectionsStartDate(), $bIgnoreDayOffsetRules);
//
//		$this->bNoNextEventFound = $oNextEvent === null ? true : false;
//
//		return $oNextEvent;
//
//	}


		public function getNextCollectionScenarioEvent($bIgnoreDayOffsetRules=false) {
			$oMostRecentEventInstance = $this->getMostRecentCollectionEventInstance();
			if (!$bIgnoreDayOffsetRules && ($oMostRecentEventInstance !== null && $oMostRecentEventInstance->completed_datetime === null)) {
			   $this->bPreviousEventNotCompleted = true;
			   return null;
			} else if (!$bIgnoreDayOffsetRules) {
			   $this->bPreviousEventNotCompleted = false;
			}

			//grab the account's current scenario, and determine the next event, based on the most recent scenario event
			$oMostRecentScenarioEvent = $oMostRecentEventInstance !== null ? $oMostRecentEventInstance->getScenarioEvent() : null;
			$oNextEvent = $this->getCurrentScenarioInstance()->getScenario()->getScenarioEventAfter($oMostRecentScenarioEvent);

			if ($oNextEvent === null) {
				$this->bNoNextEventFound = true;
				return null;
			}

			//if we're simply curious about what the next event will be, return it now
			if ($bIgnoreDayOffsetRules) {
				return $oNextEvent;
			}

			//otherwise, check if it is eligible to be scheduled before returning it
			
			//if we have a previous event, and it belongs to the current scenario, we calculate the day offset relative to its completion date. In other cases we use the source collectable's due date
			if ($oMostRecentEventInstance !== null && $oMostRecentEventInstance->collection_scenario_collection_event_id && $oNextEvent->collection_scenario_id === $oMostRecentEventInstance->getScenario()->id) {
				$sMostRecentCompletedDateTime = date('Y-m-d', Flex_Date::truncate($oMostRecentEventInstance->completed_datetime, 'd', false));
			} else {
				$sMostRecentCompletedDateTime = $this->getCurrentDueDate();
			}

			$iDayOffset = Flex_Date::difference( $sMostRecentCompletedDateTime,  Data_Source_Time::currentDate(), 'd');
			if ($oNextEvent !== null && ($iDayOffset >= $oNextEvent->day_offset)) {
				$this->bNoNextEventFound = false;
				return $oNextEvent;
			} else {
				$this->bNoNextEventFound = true;
				return null;
			}
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

	/**
	 * Determines whether an account should be in collections
	 * @return <type> 
	 */
	public function shouldCurrentlyBeInCollections()
	{
		//if an account is in suspension, it should not be part of the collections process
		if ($this->isInSuspension())
			return false;

		 $oScenario  = $this->getCurrentScenarioInstance()->getScenario();

		 //if they are not in collections, we evaluate the scenario threshold criterion against the overdue amount and balance on (today + the scenario offset).
		 if (!$this->isCurrentlyInCollections())
		 {
			 $mNow = date('Y-m-d', strtotime("+$oScenario->day_offset days", time()));
			 return $oScenario->evaluateThresholdCriterion($this->getOverDueCollectableAmount($mNow),$this->getOverdueBalance($mNow));
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

			 //now apply the scenario threshold criterion to the overdue amount/balance on that date
			 if ($oScenario->evaluateThresholdCriterion($this->getOverDueCollectableAmount($sNow),$this->getOverdueBalance($sNow)))
				 return true;

			 //If this is not the case, we will now check if there are any other collectables that are overdue, which taken into account will keep the account in collections
			 $mNow = self::SCENARIO_OFFSET_USED_TO_DETERMINE_EXIT_COLLECTIONS ? date('Y-m-d', strtotime("+$oScenario->day_offset days", time())) : Data_Source_Time::currentDate();
			 return $oScenario->evaluateThresholdCriterion($this->getOverDueCollectableAmount($mNow),$this->getOverdueBalance($mNow));
		 }
	}


	/**
	 * @method Account_Logic::scheduleNextScheduledScenarioEvent
	 * If the account is currently in collections or should be in collections, the next scenario event is triggered,
	 * based on the following rules:
	 * - An event will be scheduled for accounts that are not in suspension AND are either currently in collections or have an overdue balance on or above the entry threshold
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
	 * Also, this method assumes that all distribution of balances from payments, adjustments, and credit collectables has been done.
	 *
	 */
	public function queueNextScenarioEvent()
	{
		$bShouldBeInCollections = $this->shouldCurrentlyBeInCollections();
		$bIsCurrentlyInCollections = $this->isCurrentlyInCollections();

		if ($bIsCurrentlyInCollections && !$bShouldBeInCollections)
		{
			$oEventInstance = Logic_Collection_Event_Instance::queueForScheduling($this, COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS);
			//$oEventInstance->_registerWithArray();
		}
		else if ($bShouldBeInCollections)
		{			
			// Schedule the next scheduled event. Get the Logic_Collection_Scenario_Event object that should be scheduled
			$oNextScenarioEvent = $this->getNextCollectionScenarioEvent();
			if ($oNextScenarioEvent !== null)
			{
				$oEventInstance = Logic_Collection_Event_Instance::queueForScheduling($this, $oNextScenarioEvent);
				if ($oEventInstance->getInvocationId() != COLLECTION_EVENT_INVOCATION_MANUAL)
				{
					Log::getLog()->log("Queueing ".$oEventInstance->getEventName().". The event will be invoked and completed automatically");					
				}
				else
				{
					Log::getLog()->log("Queueing ".$oEventInstance->getEventName().".The event will require manual completion through Flex");					
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
		else
		{
			$this->bNoNextEventFound = TRUE;
			Log::getLog()->log("This account is currently not in collections and neither should it be. No event was scheduled.");
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
	public function getCollectables($iType = Logic_Collectable::DEBIT, $bRefreshCache = false)
	{
		if ($this->aCollectables === null || $bRefreshCache)
		{
			$this->aCollectables[Logic_Collectable::DEBIT] = Logic_Collectable::getForAccount($this, true, Logic_Collectable::DEBIT, $bRefreshCache);
			$this->aCollectables[Logic_Collectable::CREDIT] = Logic_Collectable::getForAccount($this, true,Logic_Collectable::CREDIT, $bRefreshCache);
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

	/**
	 * Returns an array of Logic_Adjustment objects. NOTE: ONLY APPROVED ADJUSTMENTS ARE RETURNED BY THIS METHOD
	 * @param <type> $iSignType
	 * @param <type> $bRefreshFromDatabase
	 * @return <type> 
	 */
	public function getAdjustments($iSignType = null,  $bRefreshFromDatabase = false)
	{
		if ($this->aAdjustments === null || $bRefreshFromDatabase )
		{
			$this->aAdjustments[Logic_Adjustment::DEBIT] = Logic_Adjustment::getForAccount($this,  Logic_Adjustment::DEBIT, ADJUSTMENT_STATUS_APPROVED);
			$this->aAdjustments[Logic_Adjustment::CREDIT] = Logic_Adjustment::getForAccount($this, Logic_Adjustment::CREDIT, ADJUSTMENT_STATUS_APPROVED );
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

		foreach ($aPayables as $oPayable)
		//for ($i=0;$i<count($aPayables);$i++)
		{
			if ($mDistributable->balance == 0)
				break;
			//$oPayable = $aPayables[$i];

			if ($bDistributableIsCredit && $oPayable->getBalance() > 0)
			{
				$oPayable->processDistributable($mDistributable);
			}
			else if (!$bDistributableIsCredit && $oPayable->getBalance() < $oPayable->getAmount())
			{
				$oPayable->processDistributable($mDistributable);
			}

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
		foreach ($aCollectables as $oCollectable) {
			if ($oCollectable->amount > 0 && ($oCollectable->amount - $oCollectable->balance) > 0) {
				return true;
			}
		}

		return false;

	}

	public function processDistributables()
	{
		$iIterations = 0;

		while (($this->getPayableBalance(TRUE) > 0 && $this->getDistributableCreditBalance() > 0) || ($this->hasPayablesWithBalanceBelowAmount() && $this->getDistributableDebitBalance() > 0)) {
			$iIterations++;
			Log::getLog()->logIf(self::DEBUG_LOGGING, "[+] Iteration #{$iIterations}");
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [*] Payable Balance: ".$this->getPayableBalance(TRUE));
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [*] Distributable Credit Balance: ".$this->getDistributableCreditBalance());
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [*] Has Payables with Balance below Amount?: ".(($this->hasPayablesWithBalanceBelowAmount()) ? 'Yes' : 'No'));
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [*] Distributable Debit Balance: ".$this->getDistributableDebitBalance());

			$aPayables = $this->getPayables();
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [+] ".count($aPayables)." Payables");
			foreach ($aPayables as $oPayable) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] ".get_class($oPayable)." #{$oPayable->id} (".$oPayable->balance."/".$oPayable->amount.")");
			}

			$aCreditCollectables = $this->getCollectables(Logic_Collectable::CREDIT);
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [+] ".count($aCreditCollectables)." CR Collectables");
			foreach ($aCreditCollectables as $oCollectable) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] #{$oCollectable->id} (".$oCollectable->balance."/".$oCollectable->amount.")");
				$oCollectable->distributeToPayables($aPayables);
			}

			$aPayments = $this->getPayments(PAYMENT_NATURE_PAYMENT);
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [+] ".count($aPayments)." CR Payments");
			foreach ($aPayments as $oPayment) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] #{$oPayment->id} (".$oPayment->balance."/".$oPayment->amount.")");
				$oPayment->distributeToPayables($aPayables);
			}

			$aAdjustments = $this->getAdjustments(Logic_Adjustment::CREDIT);
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [+] ".count($aAdjustments)." CR Adjustments");
			foreach ($aAdjustments as $oAdjustment) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] #{$oAdjustment->id} (".$oAdjustment->balance."/".$oAdjustment->amount.")");
				$oAdjustment->distributeToPayables($aPayables);
			}

			$aPayables = array_reverse($this->getPayables());
			$aReversedPayments = $this->getPayments(PAYMENT_NATURE_REVERSAL);
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [+] ".count($aReversedPayments)." DR Payments");
			foreach($aReversedPayments as $oPayment) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] #{$oPayment->id} (".$oPayment->balance."/".$oPayment->amount.")");
				$oPayment->distributeToPayables($aPayables);
			}

			$aDebitAdjustments = $this->getAdjustments(Logic_Adjustment::DEBIT, ADJUSTMENT_STATUS_APPROVED);
			Log::getLog()->logIf(self::DEBUG_LOGGING, "  [+] ".count($aDebitAdjustments)." DR Adjustments");
			foreach ( $aDebitAdjustments as $oAdjustment) {
				Log::getLog()->logIf(self::DEBUG_LOGGING, "    [+] #{$oAdjustment->id} (".$oAdjustment->balance."/".$oAdjustment->amount.")");
				$oAdjustment->distributeToPayables($aPayables);
			}

		}

		Logic_Collectable_Payment::createRecords();
		Logic_Collectable_Adjustment::createRecords();
		Logic_Collectable_Transfer_Balance::createRecords();
		Log::getLog()->logIf(self::DEBUG_LOGGING, "Distributing ({$iIterations} iterations): ".Logic_Stopwatch::getInstance()->lap());
		return array('iterations'=>$iIterations, 'Debit Collectables' =>count($this->getCollectables()), 'Credit Collectables'=> count($aCreditCollectables) , 'Credit Payments' =>count($aPayments) , 'Credit Adjustments'=> count($aAdjustments), 'Debit Payments' => count($aReversedPayments) , 'Debit Adjustments' =>count($aDebitAdjustments), 'Balance'=>$this->getPayableBalance() );

	}



	public function redistributeBalances()
	{
		$oDataAccess = DataAccess::getDataAccess();
		$oDataAccess->TransactionStart();
		try
		{
			Collectable_Adjustment::deleteForAccount($this->id);
			Collectable_Payment::deleteForAccount($this->id);
			Collectable_Transfer_Balance::deleteForAccount($this->id);
			Collectable::resetBalanceForAccount($this->id);
			Adjustment::resetBalanceForAccount($this->id);
			Payment::resetBalanceForAccount($this->id);
			//The preceding statements directly accessed the database and modified data, so we need to force a cache refresh on the following data members
			$this->aPayments		= NULL;
			$this->aAdjustments		= NULL;
			$this->_aPayables		= NULL;
			Log::getLog()->logIf(self::DEBUG_LOGGING, "Refreshing Logic_Collectable cache");
			Logic_Collectable::refreshCache(array_merge($this->getCollectables(Logic_Collectable::CREDIT, TRUE), $this->getCollectables(Logic_Collectable::DEBIT, TRUE)));
			$this->aCollectables = NULL;
			Logic_Stopwatch::getInstance()->lap();
			Log::getLog()->logIf(self::DEBUG_LOGGING, "Processing Distributables");
			$aStats = $this->processDistributables();
		}
		catch(Exception $e)
		{
			 $oDataAccess->TransactionRollback();
			 throw $e;
		}
		$oDataAccess->TransactionCommit();

		return $aStats;
		
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

		$sNow = DataAccess::getNow();
		if ($bEndCurrentScenario)
		{

			$iNow = strtotime($sNow);
			$iEndDateTime = strtotime("-1 second", $iNow);
			$sEndDateTime = date ('Y-m-d H:i:s', $iEndDateTime);
			$oCurrentScenario = $this->getBaseScenarioInstance();
			$oCurrentScenario->end_datetime = $sEndDateTime;
			$oCurrentScenario->save();
			$this->aActiveScenarioInstances = array();
		}

		$oNewInstance 				= new Account_Collection_Scenario();
		$oNewInstance->account_id 			= $this->oDO->Id;
		$oNewInstance->collection_scenario_id 	= $iCollectionScenarioId;
		$oNewInstance->created_datetime		= $sNow;
		$oNewInstance->start_datetime		= $sNow;
		$oNewInstance->end_datetime				 = Data_Source_Time::END_OF_TIME;
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
		Account_Collection_Scenario::resetScenarioForAccountId($this->id);
		//this will reset the scenarios that are cached on the account object
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
	 * @return Logic_Collectable -  If the account is currently in collections, and the last event was triggerred under the current scenario: return the collecatble that triggered it into collections
	 *								Else - the collectable with the oldest due date that has a balance > 0 and is not part of an active promise is returned
	 */
	public function getSourceCollectable()
	{
		$oCurrentScenario = $this->getCurrentScenarioInstance()->getScenario();
		$oMostRecentEventInstance = $this->getMostRecentCollectionEventInstance();
		$iMostRecentEventScenario	= $oMostRecentEventInstance!= null && $oMostRecentEventInstance->getScenario()!=null ? $oMostRecentEventInstance->getScenario()->id : null;

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


	public function getOldestOverDueCollectableRelativeToDate($sDate)
	{
		$iDate			= strtotime($sDate);
		$aCollectables	= $this->getCollectables();
		$oOldest		= null;
		$iOldestDueDate = null;
		foreach ($aCollectables as $oCollectable)
		{
			if (!$oCollectable->belongsToActivePromise() && $oCollectable->balance > 0)
			{
				$iDueDate		= strtotime($oCollectable->due_date." 23:59:59");
				if (($oOldest == null ||$iOldestDueDate > $iDueDate ) && ($iDueDate < $iDate))
				{
					$oOldest		= $oCollectable;
					$iOldestDueDate = $iDueDate;
				}
			}
		}
		return $oOldest;
	}

	/**
	 * @method Account_Logic::getCurrentDueDate
	 * Returns the due date of the source collectable.
	 * 	 *
	 */
	public function getCurrentDueDate()
	{
		$mSourceCollectable =  $this->getSourceCollectable();
		return $mSourceCollectable === null ? null : $mSourceCollectable->due_date;
	}

	/**
	 *	This method returns a date based on the source collectable due date and scenario day offset.
	 *	This function does not evaluate the source collectable amount and balance against the scenario threshold criteron, so in itself is not sufficient to determine whether collections should start.
	 * @return <type>
	 */
	public function getCollectionsStartDate()
	{
		//get the scenario day offset
		$iOffset = $this->getCurrentScenarioInstance()->getScenario()->day_offset;
		Log::getLog()->log("Scenario day offset: $iOffset");

		//get the current due date, or end of time if no collectables with balance > 0 exist
		$sDueDate = coalesce($this->getCurrentDueDate(), Data_Source_Time::END_OF_TIME);
		$iDueDate = strtotime($sDueDate);
		
		//add one day to determine when the account will be overdue
		$iOverDueDate = strtotime("+1 day", $iDueDate);

		//bring the overdue date forward by the scenario day offset, this will be the start date for the collections process
		$iStartDate = strtotime("-$iOffset day", $iOverDueDate);
		$sStartDate = date ("Y-m-d", $iStartDate);		
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

	public function getLatestActionedBarringLevel()
	{
		$aBarringDetails = Account_Barring_Level::getLastActionedBarringLevelForAccount($this->oDO->Id);
		if ($aBarringDetails === null)
		{
			// Details will include a service_count field
			$aBarringDetails = Service_Barring_Level::getLastActionedBarringLevelForAccount($this->oDO->Id);
		}
		return ($aBarringDetails ? $aBarringDetails : null);
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
		Log::getLog()->log("Redistributing ".count($aAccounts)." Accounts");
		Log::getLog()->log("Account, Time, Memory Usage, iterations, Debit Collectables, Credit Collectables, Credit Payments, Credit Adjustments, Debit Payments,Debit Adjustments, Account Balance (based on amounts) , Payable Balance (based on balances) ");

		$iProgress = 0;
		$iTotalAccounts = count($aAccounts);
		foreach ($aAccounts as $iIndex => $oAccountORM)
		{
			$iProgress++;
			Log::getLog()->log("  [+] ({$iProgress}/{$iTotalAccounts}) #{$oAccountORM->Id}");
			$oDataAccess = DataAccess::getDataAccess();
			$oDataAccess->TransactionStart();
			try
			{
				//get the Logic_Account object
				$oAccount = self::getInstance($oAccountORM);

				//the following is for process reporting purposes
				$iId = $oAccount->Id;
				$oStopwatch = Logic_Stopwatch::getInstance(true);
				$oStopwatch->start();
				Log::getLog()->log("Instantiated logic account $oAccountORM->Id,".  memory_get_usage(true));

				Log::getLog()->log("Calculating Account Balance");
				$fAccountBalance = $oAccount->getAccountBalance();

				//this is the actual balance redistribution
				Log::getLog()->log("Redistributing Account Balances");
				$aResult = $oAccount->redistributeBalances();
				$iRedistributeTime = Logic_Stopwatch::getInstance()->lap();
				Log::getlog()->log("Processing Distributables for Account {$oAccount->id},$iRedistributeTime");
				//further process reporting
				$fOverdueBalance = $oAccount->getOverdueCollectableBalance();
				$iTotalAccountTime = $oStopwatch->split();
				//self::$aMemory['after_before_cache_clear'] = memory_get_usage (TRUE );
				Log::getlog()->log("Total Time for {$oAccount->id},$iTotalAccountTime");
				//memory management
				$oAccount->reset();
				unset($oAccount);
				unset($oStopwatch);
				unset ($aAccounts[$iIndex]);
				self::clearCache();

				//output the process report to the commandline
				$iMemory = (memory_get_usage (TRUE ));
				Log::getLog()->log("$iId, $iTotalAccountTime,  $iMemory ,".$aResult['iterations'].",".$aResult['Debit Collectables'].",".$aResult['Credit Collectables'].",".$aResult['Credit Payments'].",".$aResult['Credit Adjustments'].",".$aResult['Debit Payments'].",".$aResult['Debit Adjustments'].",".$fAccountBalance.",".$aResult['Balance']);

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

	/**
	 * For each account passed into this method, if the account is in collections, or should be in collections, the next event is scheduled.
	 * If the next event is invoked automatically, it is also queued to be invoked and completed, which is done in a separate step after scheduling was done for each account.
	 * To to a complete batch process, this method must be called recursively for as long as automated events are scheduled, invoked and completed, because multimple events might have to be scheduled on one day (day offset of next event === 0)
	 * For this reason, when a next event was not found for an account, it is deleted from the accounts array, as it does not need inclusion in the next iteration of the batch process
	 * @param <type> $aAccounts
	 * @return <type> 
	 */
	public static function batchProcessCollections(&$aAccounts)
	{


		Log::getLog()->log("-------Starting Account Batch Collections Process-------------------------");
		Log::getLog()->log("Processing Collections for ".count($aAccounts)." Accounts.");
		foreach ($aAccounts as $oAccount)
		{
			//$oDataAccess	= DataAccess::getDataAccess();
			//$oDataAccess->TransactionStart();
			try
			{
				Log::getLog()->log("Trying to schedule next event for account $oAccount->Id ");
				Logic_Stopwatch::getInstance()->lap();
				//in case the account was part of the previous iteration of the batch process and an exception occurred in completing an event.
				//this is just a safety mechanism because these accounts should already have been taken out of the process at an earlier stage
				if ($oAccount->getException()=== NULL)
				{
					$oAccount->queueNextScenarioEvent();
				}

				//if no event was scheduled, no need to include this account in the next batch process iteration
				if ($oAccount->noNextEventFound() || $oAccount->previousEventNotCompleted() || $oAccount->getException()!== NULL)
					unset($aAccounts[$oAccount->id]);

				//Log::getlog()->log("Processed account $oAccount->Id in : ".Logic_Stopwatch::getInstance()->lap());
				//$oDataAccess->TransactionCommit();
			}
			catch (Exception $e)
			{
				// Exception caught, rollback db transaction
				//$oDataAccess->TransactionRollback();
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
		//this is where event invocation and completion happens
		$aResult =  Logic_Collection_Event_Instance::completeWaitingInstances(NULL, TRUE);
		//remove accounts with failed event instances from the array to avoid the process becoming endless
		foreach($aResult['failure'] as $oInstance)
		{
			unset($aAccounts[$oInstance->account_id]);
		}

		return count($aResult['success']);
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

	 public function __call($function, $args)
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
				$aAccounts[$oORM->Id] =self::getInstance($oORM, self::CACHE_MODE_BYPASS);
			}
		}

		self::importCache($aAccounts);

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
		Log::getLog()->log($sSelectQuery);
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
		$aWherePieces = array("a.Archived <> ".ACCOUNT_STATUS_ARCHIVED);
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
								COALESCE(
									SUM(
										IF(
											(
									            c.due_date < NOW()
									            OR c.amount < 0
									        )
											AND (c.collection_promise_id IS NULL OR c_cp.completed_datetime IS NOT NULL),
											c.amount,
											0
										)
									),
									0
								)
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
				JOIN		(
								SELECT	  a.Id AS account_id, COALESCE(SUM(p.amount * pn.value_multiplier), 0) AS total_amount
								FROM		Account a
								LEFT JOIN   payment p ON (p.account_id = a.Id)
								LEFT JOIN   payment_nature pn ON (pn.id = p.payment_nature_id)
								GROUP BY	a.Id
							) total_payments ON (total_payments.account_id = a.Id)
				LEFT JOIN 	collection_promise c_cp ON (c.collection_promise_id = c_cp.id)
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
									SELECT		id
									FROM		payment USE INDEX (fk_payment_tbl_account_id)
									WHERE	   account_id = a.Id
									ORDER BY	paid_date DESC
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
  