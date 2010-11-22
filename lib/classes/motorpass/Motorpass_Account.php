<?php
/**
 * Motorpass_Account
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Motorpass_Account
 */
class Motorpass_Account extends ORM_Cached
{
	protected 			$_strTableName			= "motorpass_account";
	protected static	$_strStaticTableName	= "motorpass_account";

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
	
	public static function getForAccount($mAccount)
	{
		$oForAccount	= self::_preparedStatement('selForAccount');
		if ($oForAccount->Execute(array('account_id'=>ORM::extractId($mAccount))) === false)
		{
			throw new Exception_Database($oForAccount->Error());
		}
		$aResults	= array();
		while ($aResult = $oWaitingForAccount->Fetch())
		{
			$aResults[$aResult['id']]	= new self($aResult);
		}
		return $aResults;
	}
	
	public static function getCurrentForAccount($mAccount)
	{
		$oForAccount	= self::_preparedStatement('selCurrentForAccount');
		if ($oForAccount->Execute(array('account_id'=>ORM::extractId($mAccount))) === false)
		{
			throw new Exception_Database($oForAccount->Error());
		}
		return new self($aResult);
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
				case 'selForAccount':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"	motorpass_account ma
																						JOIN rebill_motorpass rm ON (ma.id = rm.motorpass_account_id)
																						JOIN rebill r ON (r.id = rm.rebill_id)
																						JOIN Account a ON (a.Id = r.account_id)",
																					"ma.*",
																					"	r.account_id = <account_id>",
																					"rm.id ASC");
				case 'selCurrentForAccount':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"	motorpass_account ma
																						JOIN rebill_motorpass rm ON (ma.id = rm.motorpass_account_id)
																						JOIN rebill r ON (r.id = rm.rebill_id)
																						JOIN Account a ON (a.Id = r.account_id)",
																					"ma.*",
																					"	r.account_id = <account_id>",
																					"r.id ASC",
																					1);
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


	public function isValidValue($propertyName, $value)
	{

		switch ($propertyName)
		{

			case 'id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));

			case 'motorpass_promotion_code_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getPromotionCode(true) !== null));

			case 'account_number':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value")) ;

			case 'account_name':
				return (is_string($value) && trim($value)  && strlen($value) <= 100);

			case 'business_commencement_date':
				return (preg_match("/^(2[0-1]|19)[0-9]{2,2}\-((0[469]|11)\-(0[1-9]|[12][0-9]|30)|(0[13578]|1[02])\-(0[1-9]|[12][0-9]|3[01])|02\-(0[1-9]|[12][0-9]))$/", $value) && (substr($value, 5, 2) != "02" || substr($value, 8, 2) != "29" || date("L", mktime(0,0,0,1,1,substr($value, 0, 4))) == "1"));

			case 'motorpass_business_structure_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getBusinessStructure(true) !== null));

			case 'business_structure_description':
				return (is_string($value)   && strlen($value) <= 128);

			case 'street_address_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getStreetAddress(true) !== null));

			case 'postal_address_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getPostalAddress(true) !== null));

			case 'motorpass_contact_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getContact(true) !== null));

			case 'card_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getCard(true) !== null));

			case 'modified':
				return ($value === null) || (preg_match("/^(2[0-1]|19)[0-9]{2,2}\-((0[469]|11)\-(0[1-9]|[12][0-9]|30)|(0[13578]|1[02])\-(0[1-9]|[12][0-9]|3[01])|02\-(0[1-9]|[12][0-9])) (?:[01][0-9]|2[0-3])\:[0-5][0-9](?:|\:[0-5][0-9](?:|\.[0-9]{1,6}))$/", $value) && (substr($value, 5, 2) != "02" || substr($value, 8, 2) != "29" || date("L", mktime(0,0,0,1,1,substr($value, 0, 4))) == "1"));

			case 'modified_employee_id':
				return ($value === null) || (preg_match("/^(\-?[0-9]+|[0-9]+\-?)$/", "$value"));// && ($this->getModifiedDealer(true) !== null));

			default:
				// No validation - assume is correct already as is not for data source
				return true;

		}

	}
}
?>