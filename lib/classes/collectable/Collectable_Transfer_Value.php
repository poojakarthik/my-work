<?php
/**
 * Collectable_Transfer_Value
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Collectable_Transfer_Value
 */
class Collectable_Transfer_Value extends ORM_Cached
{
	protected 			$_strTableName			= "collectable_transfer_value";
	protected static	$_strStaticTableName	= "collectable_transfer_value";

	const	TRANSFER_MODE_BALANCE_ONLY	= 1;	// Only transfer Value when there is enough Balance as well
	const	TRANSFER_MODE_PREFER_DEBT	= 2;
	const	TRANSFER_MODE_PREFER_PAID	= 3;

	/**
	 * createForCollectables()
	 *
	 * Collectables must be saved to the Database prior to calling this
	 * NOTE: Does not save the Transfer or either Collectable
	 *
	 * @param mixed	$mSourceCollectable
	 * @param mixed	$mDestinationCollectable
	 * @param float	$fValue
	 * @param int	$iTransferMode
	 * @return Collectable_Transfer_Value
	 */
	public static function createForCollectables($mSourceCollectable, $mDestinationCollectable, $fValue, $iTransferMode=self::TRANSFER_MODE_BALANCE_ONLY) {
		$oSourceCollectable			= Collectable::getForId(ORM::extractId($mSourceCollectable));
		$oDestinationCollectable	= Collectable::getForId(ORM::extractId($mDestinationCollectable));

		// Verify the proposed transfer value
		$fValue	= Rate::roundToRatingStandard((float)$fValue, 4);
		if ($fValue < 0) {
			throw new Exception("Collectable Transfer amount cannot be negative (perhaps reverse the Source/Destination Collectables)");
		} elseif ($fValue == 0) {
			throw new Exception("Collectable Transfer amount must be greater than 0");
		} elseif ($fValue > $oSourceCollectable->amount) {
			throw new Exception("There is not enough value on the Source (Requested: {$fValue}; Available: {$oSourceCollectable->amount})");
		}

		// FIXME
		// Check to make sure neither of the Collectables have negative amounts or balances,
		// because the calculation code may not work in its current state.  Feel free to fix and remove this limitation!
		if ($oSourceCollectable->amount < 0 || $oDestinationCollectable->amount < 0 || $oSourceCollectable->balance < 0 || $oDestinationCollectable->balance < 0) {
			throw new Exception("Transfers between Collectables with negative amounts or balances are not supported yet.");
		}

		// Calculate Balance component
		$fBalanceToTransfer	= 0.0;
		switch ($iTransferMode) {
			case self::TRANSFER_MODE_BALANCE_ONLY:
				if ($fValue > $oSourceCollectable->balance) {
					throw new Exception("There is not enough balance on the Source (Requested: {$fValue}; Available: {$oSourceCollectable->balance})");
				}
				$fBalanceToTransfer	= $fValue;
				break;
			case self::TRANSFER_MODE_PREFER_DEBT:
				$fBalanceToTransfer	= min($oSourceCollectable->balance, $fValue);
				break;
			case self::TRANSFER_MODE_PREFER_PAID:
				$fBalanceToTransfer	= max(0.0, $fValue - ($oSourceCollectable->amount - $oSourceCollectable->balance));
				break;
			
			default:
				throw new Exception("'{$iTransferMode}' is not a valid Collectable Transfer Mode");
				break;
		}

		// Build the Transfer
		$oTransfer	= new Collectable_Transfer_Value();

		$oTransfer->from_collectable_id	= $oSourceCollectable->id;
		$oTransfer->to_collectable_id	= $oDestinationCollectable->id;
		$oTransfer->created_datetime	= DataAccess::getDataAccess()->getNow();	// Simply a default
		$oTransfer->amount				= $fValue;
		$oTransfer->balance				= $fBalanceToTransfer;

		// Update the Collectables
		$oSourceCollectable->amount		= Rate::roundToRatingStandard($oSourceCollectable->amount - $fValue, 4);
		$oSourceCollectable->balance	= Rate::roundToRatingStandard($oSourceCollectable->balance - $fBalanceToTransfer, 4);
		
		$oDestinationCollectable->amount	= Rate::roundToRatingStandard($oDestinationCollectable->amount + $fValue, 4);
		$oDestinationCollectable->balance	= Rate::roundToRatingStandard($oDestinationCollectable->balance + $fBalanceToTransfer, 4);

		return $oTransfer;
	}

	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mixObjects)
	{
		parent::addToCache($mixObjects, __CLASS__);
	}

	public static function getForId($intId, $bolSilentFail=false)
	{
		return parent::getForId($intId, $bolSilentFail, __CLASS__);
	}

	public static function getAll($bolForceReload=false)
	{
		return parent::getAll($bolForceReload, __CLASS__);
	}

	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
	 *
	 * @param	string		$strStatement						Name of the statement
	 *
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	protected static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "id = <Id>", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1", "id ASC");
					break;

				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert(self::$_strStaticTableName);
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById(self::$_strStaticTableName);
					break;

				// UPDATES

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>