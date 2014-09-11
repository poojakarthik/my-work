<?php

/**
 * Description of Collectable_Logic_Adjustment
 *
 * @author JanVanDerBreggen
 */
class Logic_Collectable_Payment
{
	protected static $aInsertRecords = array();

	public static function create($oPayment, $oCollectable, $fMaxAmount = null)
	{
		$iValueMultiplier = $oPayment->getPaymentNature()->value_multiplier;
		//$oDO = new Collectable_Payment();
		$iPayment_id = $oPayment->id;
		$iCollectable_id = $oCollectable->id;

		if ($oPayment->isCredit())
		{
			 $fBalance = $oCollectable->balance <= $oPayment->balance ? $oCollectable->balance*$iValueMultiplier : $oPayment->balance*$iValueMultiplier;
		}
		else
		{
			$fBalance = ($oCollectable->amount - $oCollectable->balance) >= $oPayment->balance ? $oPayment->balance *$iValueMultiplier : ($oCollectable->amount - $oCollectable->balance) * $iValueMultiplier;
		}

		if ($fMaxAmount !== null && abs($fBalance)> $fMaxAmount)
			$fBalance = $fMaxAmount*$iValueMultiplier;

		//$sCreated_datetime = DataAccess::getDataAccess()->getNow();

		self::$aInsertRecords[] = "(NULL,$iPayment_id, $iCollectable_id, $fBalance, NOW() )";

		//Query::run("INSERT INTO collectable_payment values (NULL,$iPayment_id, $iCollectable_id, $fBalance, NOW() )");
		return $fBalance;
	}

	public static function createRecords()
	{
		Collectable_Payment::batchInsert(self::$aInsertRecords);
		self::$aInsertRecords = array();
	}


	
}
?>
