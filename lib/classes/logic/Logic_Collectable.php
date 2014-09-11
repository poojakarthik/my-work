<?php

/**
 * Description of Collectable_Logic
 *
 * @author JanVanDerBreggen
 */
class Logic_Collectable extends Logic_Distributable implements DataLogic, Logic_Payable
{
	const DEBIT = 0;
	const CREDIT = 1;


	protected $oDO;	  
	protected $oPromise;

	protected static $aInstances = array();


	public static function getInstance($mDefinition, $bRefreshCache = FALSE)
	{
		$oDO = null;
		if (is_numeric($mDefinition))
		{
			$oDO = Collectable::getForId($mDefinition);
		}
		else if (get_class($mDefinition) == 'Collectable')
		{
			$oDO = $mDefinition;
		}

		if ($oDO !== null && $oDO->id !== null && in_array($oDO->id, array_keys(self::$aInstances)))
		{
			if ($bRefreshCache)
				self::$aInstances[$oDO->id]->refreshData($oDO);
			return self::$aInstances[$oDO->id];
		}
		else
		{
			$oLogic = new self($oDO);
			self::$aInstances[$oDO->id]	= $oLogic;
			return $oLogic;
		}
	}



	public static function refreshCache($aCollectableIds = NULL)
	{
		Logic_Stopwatch::getInstance()->lap();
		$aArray = $aCollectableIds === NULL ? self::$aInstances : $aCollectableIds;
		foreach($aArray as $oInstance)
		{
			self::getInstance($oInstance->id, TRUE);
		}
		//Log::getLog()->log("Collectable Instances Data Refresh: ".Logic_Stopwatch::getInstance()->lap());
	}

	public static function getInstances()
	{
		return self::$aInstances;
	}
	private function __construct($mDefinition)
	{
		$this->oDO = is_numeric($mDefinition) ? Collectable::getForId($mDefinition) : (get_class($mDefinition) == 'Collectable' ? $mDefinition : null);
	}

	public function getBalance()
	{
		return $this->oDO->balance;
	}

	public function getAmount() {
		return $this->oDO->amount;
	}


	public static function getForAccount($oAccount, $bOnlyWithBalanceOwing = true, $iBalanceType = self::DEBIT, $bBypassCache = false)
	{
		$aORMs = Collectable::getForAccount($oAccount->Id, $bOnlyWithBalanceOwing, $iBalanceType);
		$aResult = array();
		foreach ($aORMs as $oORM)
		{
			$aResult[$oORM->id] = self::getInstance($oORM, $bBypassCache);
		}

		return $aResult;
	}
	
	public function getAccount()
	{
		return Logic_Account::getInstance($this->account_id);
	}

	/**
	 * If the due date is in the past and the balance > 0 and this is not part of an active promise to pay: return true
	 * else return false
	 * @todo: $iOffset is currently the way to calculate the due date against a point in time that is not today.
	 * It currently gets subtracted from the due date, which is the same as adding days to today.
	 * To make the code clearer, and to also cater for pushing the 'today' point of reference into the past, the function parameter should simply be $sToday = null
	 */
	public function isOverDue($sNow = null)
	{		
		if ($this->balance == 0)
		  return false;

		$bNoPromise		= !($this->belongsToPromise() && $this->getPromise()->isActive());
		$iDueDateTime		  =   strtotime($this->oDO->due_date." 23:59:59");
		$iNow = $sNow === null ? time() : strtotime($sNow);
		$bOverdue		= ($iDueDateTime < $iNow);
		return ($bNoPromise && $bOverdue);
	}

	public function processDistributable($mBalanceTransferItem, $fMaxAmount = null) {
		//Log::getLog()->log('before_create_transfer for '.$mBalanceTransferItem->id.", ".memory_get_usage(true));
		if ($mBalanceTransferItem instanceof Logic_Collectable) {
			$fBalance = Logic_Collectable_Transfer_Balance::create($mBalanceTransferItem, $this, $fMaxAmount);
		} else if ($mBalanceTransferItem instanceof Logic_Adjustment) {
			$fBalance = Logic_Collectable_Adjustment::create($mBalanceTransferItem, $this, $fMaxAmount);
		} else {
			$fBalance = Logic_Collectable_Payment::create($mBalanceTransferItem, $this, $fMaxAmount);
		}

		$this->balance += $fBalance;
		if ($mBalanceTransferItem->isDebit() || $mBalanceTransferItem instanceof Logic_Collectable) {
			$mBalanceTransferItem->balance -= $fBalance;
		} else {
			$mBalanceTransferItem->balance += $fBalance;
		}

		$mBalanceTransferItem->save();
		$this->save();
		return $fBalance;
	}

	public function belongsToActivePromise()
	{
		return $this->belongsToPromise() && $this->getPromise()->isActive();
	}

	public function getPromise()
	{
		if ($this->oPromise === null && $this->belongsToPromise())
				$this->oPromise = Logic_Collection_Promise::getForCollectable($this);
		return $this->oPromise;
	}
	
	public function belongsToPromise()
	{
		return ($this->oDO->collection_promise_id !== null);
	}   

	public function save()
	{
		return $this->oDO->save();
	}

	public function toArray()
	{
		$aArray =  $this->oDO->toArray();
		return $aArray;
	}
	
	public function __get($sField)
	{

	   switch($sField)
	   {
		   case 'balance':
		   case 'amount':
			   return Rate::roundToRatingStandard($this->oDO->$sField, 4);
			default:
				return $this->oDO->$sField;
	   }

	}
	 
	public function __set($sField, $mValue) {

		switch($sField)
	   {
		   case 'balance':
		   case 'amount':
			   $this->oDO->$sField = Rate::roundToRatingStandard($mValue, 4);
			default:
			   $this->oDO->$sField = $mValue;
	   }
	}

	public function display()
	{

	}

	public function isCredit() {
		return $this->amount < 0;
	}

	public function isDebit() {
		return $this->amount > 0;
	}

	private function refreshData(Collectable $oFreshDataObject)
	{
		$this->oDO = $oFreshDataObject;
		$this->oPromise = NULL;
	}

	public static function clearCache() {
		self::$aInstances = array();
		Collectable::clearCache();
	}

	
}
?>
