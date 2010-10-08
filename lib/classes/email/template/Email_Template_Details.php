<?php
/**
 * Correspondence_Template
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Correspondence_Template
 */
class Email_Template_Details extends ORM_Cached
{
	protected 			$_strTableName			= "email_template_details";
	protected static	$_strStaticTableName	= "email_template_details";


	public static function getCurrentDetailsForTemplateId($iTemplateId)
	{
		$oSelect	= self::_preparedStatement('selCurrentForTemplateId');
		$oSelect->Execute(array('email_template_id' => $iTemplateId));
		$aResults = $oSelect->FetchAll();
		$aObjects = array();
		foreach ($aResults as $aResult)
		{
			$aObjects[]= new self($aResult);
		}
		return $aObjects[0];


	}

	public static function getFutureVersionsForId($iId)
	{
		$oThis = self::getForId($iId);
		$oQuery	= new Query();
		$sSql	= "SELECT id
					FROM email_template_details
					WHERE email_template_id = $oThis->email_template_id
					AND effective_datetime>NOW()
					AND end_datetime>effective_datetime";

		$oQuery	= new Query();
		$mResult	= $oQuery->Execute($sSql);
		$aFutureVersions = array();
		while ($aRow = $mResult->fetch_assoc())
		{
			$afutureVersions[]= self::getForId($aRow['id'])->toArray();//$oOrm->toArray();
		}

		return $afutureVersions;

	}


	public static function getForTemplateId($iTemplateId)
	{
		$sSql = "SELECT et.id as template_id, ed.id as template_version_id, e.name, ed.created_timestamp, ed.effective_datetime
				FROM email_template_type e, email_template et, email_template_details ed
				where et.email_template_type_id = e.id
				and et.id = $iTemplateId
				and ed.email_template_id = et.id
				order by effective_datetime";
		$oQuery	= new Query();
		$mCustomerGroupResult	= $oQuery->Execute($sSql);
		$aTemplateVersionDetails = array();
		while ($aRow = $mCustomerGroupResult->fetch_assoc())
		{
			$aTemplateVersionDetails[]= $aRow;//$oOrm->toArray();
		}

		return $aTemplateVersionDetails;
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

	public function setSaved()
	{
		$this->_bolSaved = true;
	}

	public function save()
	{
		if (!$this->_bolSaved)
			parent::save();
		$this->setSaved();
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
				//id, email_template_id, email_text, email_html, created_timestamp, created_employee_id, effective_datetime
				case 'selCurrentForTemplateId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "email_template_id = <email_template_id> AND effective_datetime <NOW() AND end_datetime>NOW()", NULL, 1);
					break;
				case 'selBySysName':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "system_name = <system_name> AND status_id = 1", NULL, 1);
					break;
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