<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logic_Collectable_Transfer
 *
 * @author JanVanDerBreggen
 */
abstract class Logic_Transfer_Balance {

	protected $oDO;
	protected $oSource;
	protected $oTarget;



	public function __construct($mDefinition, $oSource = null, $oTarget = null)
	{
		$this->oDO = $mDefinition;
		$this->oSource = $oSource;
		$this->oTarget = $oTarget;
	}

	public function getValue($bUnsigned = false)
	{
		return $bUnsigned ? abs($this->balance) : $this->balance;
	}

	public abstract function getSource();

	public abstract function getTarget();

	 public function apply()
	{
		

		$oSource = $this->getSource();
		$oTarget = $this->getTarget();
		////Log::getLog()->log("	Appying Balance Transfer to Collectable ID: $oTarget->id (Due Date: $oTarget->due_date, Amount: $oTarget->amount). ");
		////Log::getLog()->log("Collectable Balance prior to transfer: $oTarget->balance");
		$oTarget->balance += $this->balance;
		if ($oSource->isDebit() || $oSource instanceof Logic_Collectable)
		{
			$oSource->balance -= $this->balance;
		}
		else
		{
			$oSource->balance += $this->balance;
			
		}
	   // $oSource->balance   = Rate::roundToRatingStandard($oSource->balance, 4);
	   // $oTarget->balance   = Rate::roundToRatingStandard( $oTarget->balance, 4);

		 ////Log::getLog()->log("   Collectable Balance after transfer: $oTarget->balance . ");
		 //Log::getLog()->log("before_save_transfer'.$oSource->id, ".memory_get_usage(true));
		 Logic_Account::$aMemory['before_save_transfer'.$oSource->id] = memory_get_usage(true);

		$oSource->save();
		Logic_Account::$aMemory['after_save_source'.$oSource->id] = memory_get_usage(true);
		 //Log::getLog()->log("after_save_source'.$oSource->id, ".memory_get_usage(true));
		$oTarget->save();
		Logic_Account::$aMemory['after_save_target'.$oSource->id] = memory_get_usage(true);
		 //Log::getLog()->log("after_save_target'.$oSource->id, ".memory_get_usage(true));
		$this->save();
		Logic_Account::$aMemory['after_save_source_target_link'.$oSource->id] = memory_get_usage(true);
		 //Log::getLog()->log("after_save_source_target_link'.$oSource->id, ".memory_get_usage(true));
	}

	public function save()
	{
		$this->oDO->save();
	}

   public function __get($sField) {

	   switch($sField)
	   {
		   case 'balance':
			   return Rate::roundToRatingStandard($this->oDO->$sField, 4);
			default:
				return $this->oDO->$sField;
	   }

	}

	public function __set($sField, $mValue) {

		switch($sField)
	   {
		   case 'balance':
			   $this->oDO->$sField = Rate::roundToRatingStandard($mValue, 4);
			default:
			   $this->oDO->$sField = $mValue;
	   }
	}


}

?>
