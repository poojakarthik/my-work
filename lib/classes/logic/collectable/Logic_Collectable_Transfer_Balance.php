<?php

/**
 * Description of Collectable_Logic_Transfer
 *
 * @author JanVanDerBreggen
 */
class Logic_Collectable_Transfer_Balance
{

	static $aInsertRecords = array();


	public static function create ($oSource, $oTarget, $fMaxAmount = null)
	{
		$iFrom_collectable_id			= $oSource->id;
		$iTo_collectable_id				= $oTarget->id;
		$iCollectable_transfer_type_id	= $iType;
		$fBalance = $oTarget->balance	>= abs($oSource->balance) ? $oSource->balance : -$oTarget->balance;
		if ($fMaxAmount!== null && abs($iBalance ) > $fMaxAmount )
			$fBalance					= - $fMaxAmount;

		self::$aInsertRecords[] = "(NULL,$iFrom_collectable_id, $iTo_collectable_id,  NOW(), $fBalance )";
		return $fBalance;
	}

	public static function createRecords() {
		Collectable_Transfer_Balance::batchInsert(self::$aInsertRecords);
		self::$aInsertRecords = array();
	}
}
?>
