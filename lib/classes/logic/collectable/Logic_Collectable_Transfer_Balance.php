<?php
class Logic_Collectable_Transfer_Balance {
	static $aInsertRecords = array();

	public static function create($oSource, $oTarget, $fMaxAmount=null) {
		$fBalance = ($oTarget->balance >= abs($oSource->balance)) ? $oSource->balance : 0 - $oTarget->balance;
		if ($fMaxAmount !== null && abs($iBalance) > $fMaxAmount) {
			$fBalance = 0 - $fMaxAmount;
		}

		self::$aInsertRecords[] = "(NULL, {$oSource->id}, {$oTarget->id},  NOW(), {$fBalance})";
		return $fBalance;
	}

	public static function createRecords() {
		Collectable_Transfer_Balance::batchInsert(self::$aInsertRecords);
		self::$aInsertRecords = array();
	}
}
?>
