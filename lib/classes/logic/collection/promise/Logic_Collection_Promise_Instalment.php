<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Collection_Promise_Instalment
 *
 * @author JanVanDerBreggen
 */
class Logic_Collection_Promise_Instalment implements DataLogic, Logic_Payable {
	const DEBUG_LOGGING = true;

	//put your code here
	protected $oDO;

	public function __construct($mDefinition) {
		if ($mDefinition instanceof Collection_Promise_Instalment) {
			$this->oDO = $mDefinition;
		}
	}

	public function getBalance() {
		$oPromise = Logic_Collection_Promise::getForId($this->collection_promise_id);
		$aCollectables	= $oPromise->getCollectables();

		// Calculate total paid off on the Promise's Collectables
		$fPaid = 0;
		foreach ($aCollectables as $oCollectable) {
			$fPaid += $oCollectable->amount - $oCollectable->balance;
		}

		// Reduce "paid" value by each instalment's amount until we reach ourselves
		$aInstalments = $oPromise->getInstalments();
		foreach ($aInstalments as $oInstalment) {
			if ($oInstalment->id == $this->id) {
				// Instalment is me: return my Amount - paid remaining
				return Rate::roundToRatingStandard(max(($oInstalment->amount - max($fPaid, 0)), 0), 4);
			} else {
				// Instalment prior to me: reduce the "paid" amount by its value
				$fPaid -= $oInstalment->amount;
			}
		}

		// This should really never EVER happen, unless there is a logic error somewhere
		Flex::assert(false, "Promise Instalment #{$this->id} is not listed as one of its Promise's Instalments");
	}



	 public function __get($sField) {
	   switch($sField) {
		   case 'amount':
			   return Rate::roundToRatingStandard($this->oDO->$sField, 4);
			case 'balance':
				return $this->getBalance();
			default:
				return $this->oDO->$sField;
	   }
	}


	public function __set($sField, $mValue) {
		switch($sField) {
		   case 'amount':
			   $this->oDO->$sField = Rate::roundToRatingStandard($mValue, 4);
			default:
			   $this->oDO->$sField = $mValue;
	   }
	}



	public function save() {
		return $this->oDO->save();
	}

	public function toArray() {
		return $this->oDO->toArray();
	}

	public function display() {
		////Log::getLog()->log('Details of Promise Instalment: '.$this->id);
		////Log::getLog()->log('Promise: '.$this->promise_id);
		////Log::getLog()->log('Due Date: '.$this->due_date);
		////Log::getLog()->log('Amount: '.$this->amount);
	}

	public function getAmount() {
		return $this->oDO->amount;
	}


	public function processDistributable($mDistributable) {
		$oPromise = Logic_Collection_Promise::getForId($this->collection_promise_id);
		if ($mDistributable->isCredit())
		{
			$oCollectable = $oPromise->getOldestOpenCollectable();
			while ($mDistributable->balance > 0 && $this->getBalance() > 0 && $oCollectable!== null)
			{
			   $oCollectable->processDistributable($mDistributable, $this->getBalance());
			   $oCollectable = $oCollectable->balance > 0 ? $oCollectable : $oPromise->getOldestOpenCollectable();
			}
		}
		else
		{
			$oCollectable = $oPromise->getNewestCollectableWithRoomForDebit();
			while ($oCollectable != null && $mDistributable->balance!=null && ($this->amount - $this->getBalance()) > 0)
			{
				////Log::getLog()->log("Applying Balance to Promise Instalment: $oInstalment->id with due date: $oInstalment->due_date");
				////Log::getLog()->log("Amount: ".Rate::roundToRatingStandard($oInstalment->amount, 4).", Balance: ".Rate::roundToRatingStandard($oInstalment->getBalance(), 4));
				$oCollectable->processDistributable($mDistributable, $this->amount - $this->getBalance());
				$oCollectable = $oPromise->getNewestCollectableWithRoomForDebit();
			}

		}

	}
}
?>
