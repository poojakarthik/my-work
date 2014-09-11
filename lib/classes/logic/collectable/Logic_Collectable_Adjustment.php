<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Collectable_Logic_Adjustment
 *
 * @author JanVanDerBreggen
 */
class Logic_Collectable_Adjustment 
{

	static $aInsertRecords = array();



	public static function create($oAdjustment, $oCollectable, $fMaxAmount = null)
	{
		$iValueMultiplier = $oAdjustment->getMultiplier();
		$oDO = new Collectable_Adjustment();
		$oCollectableAdjustment = new self ($oDO, $oAdjustment, $oCollectable );
		$iAdjustment_id = $oAdjustment->id;
		$iCollectable_id  = $oCollectable->id;
		if ($oAdjustment->isCredit())
		{
			 $fBalance = $oCollectable->balance >= $oAdjustment->balance ? $oAdjustment->balance *$iValueMultiplier : $oCollectable->balance * Rate::roundToRatingStandard($iValueMultiplier, 4);
		}
		else
		{
			 $fBalance = ($oCollectable->amount - $oCollectable->balance) >= $oAdjustment->balance ? $oAdjustment->balance *$iValueMultiplier : ($oCollectable->amount - $oCollectable->balance) * $iValueMultiplier;

		}

		 if ($fMaxAmount !== null && abs($oDO->balance)> $fMaxAmount)
				 $fBalance = $fMaxAmount*$iValueMultiplier;

		 self::$aInsertRecords[] = "(NULL,$iAdjustment_id, $iCollectable_id, $fBalance, NOW() )";
		 
		return $fBalance;
	}

	public static function createRecords() {
		Collectable_Adjustment::batchInsert(self::$aInsertRecords);
		self::$aInsertRecords = array();
	}
}
?>
