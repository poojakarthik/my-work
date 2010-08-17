<?php
/**
 * Motorpass_Trade_Reference
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Motorpass_Trade_Reference
 */
class Motorpass_Trade_Reference extends ORM_Cached
{
	protected 			$_strTableName			= "motorpass_trade_reference";
	protected static	$_strStaticTableName	= "motorpass_trade_reference";

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

	public static function getFor($sWhere)
	{

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

				case 'selByAccountId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "motorpass_account_id = <account_id>");
					break;
				case 'getActiveId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "id", "motorpass_account_id = <motorpass_account_id> and company_name = <company_name> and contact_person = <contact_person> and phone_number =<phone_number> and status_id = 1");
					break;

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}

	public static function getForAccountId($iAccountId, $bSecondLatest=false)
	{
		$oSelect	= self::_preparedStatement('selByAccountId');
		$oSelect->Execute(array('account_id' => $iAccountId));
		$aResults = $oSelect->FetchAll();
		$aObjects = array();
		foreach ($aResults as $aResult)
		{
			$aObjects[]= new self($aResult);
		}
		return $aObjects;

	}

	public static function getActiveId($aWhereClause)
	{
		$oSelect	= self::_preparedStatement('getActiveId');
		$oSelect->Execute($aWhereClause);
		$aResult = $oSelect->Fetch();
		return $aResult?$aResult['id']:null;

	}

	public function isValidValue($propertyName, $value)
	{


		switch ($propertyName)
		{

			case 'id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));

			case 'motorpass_account_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getAccount(true) !== null));

			case 'company_name':
				return (is_string($value) && trim($value)  && strlen($value) <= 100);

			case 'contact_person':
				return (is_string($value) && trim($value)  && strlen($value) <= 100);

			case 'phone_number':
				return (is_string($value) && trim($value)  && strlen($value) <= 25);

			case 'status_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getStatus(true) !== null));

			case 'created':
				return ($value === null) || (preg_match("/^(2[0-1]|19)[0-9]{2,2}\-((0[469]|11)\-(0[1-9]|[12][0-9]|30)|(0[13578]|1[02])\-(0[1-9]|[12][0-9]|3[01])|02\-(0[1-9]|[12][0-9])) (?:[01][0-9]|2[0-3])\:[0-5][0-9](?:|\:[0-5][0-9](?:|\.[0-9]{1,6}))$/", $value) && (substr($value, 5, 2) != "02" || substr($value, 8, 2) != "29" || date("L", mktime(0,0,0,1,1,substr($value, 0, 4))) == "1"));

			case 'created_employee_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getCreatedDealer(true) !== null));

			default:
				// No validation - assume is correct already as is not for data source
				return true;

		}
	}
}
?>