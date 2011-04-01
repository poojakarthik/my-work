<?php
class Correspondence_Template_Carrier_Module extends ORM_Cached
{
	protected 			$_strTableName			= "correspondence_template_carrier_module";
	protected static	$_strStaticTableName	= "correspondence_template_carrier_module";

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

	public static function getForTemplateId($iTemplateId)
	{
		$oSelect	= self::_preparedStatement('selByTemplateId');
		$oSelect->Execute(array('correspondence_template_id' => $iTemplateId));
		$aResults	= $oSelect->FetchAll();
		$aObjects 	= array();
		foreach ($aResults as $aResult)
		{
			$x 			= new self($aResult);
			$aObjects[]	= $x;
		}
		return $aObjects;
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
	
	public function getForCarrierModuleAndTemplateCode($iCarrierModuleId, $sTemplateCode)
	{
		$aRow = Query::run("SELECT	*
							FROM	correspondence_template_carrier_module
							WHERE	carrier_module_id = <carrier_module_id>
									AND template_code = <template_code>",
							array('carrier_module_id' => $iCarrierModuleId, 'template_code' => $sTemplateCode))->fetch_assoc();
		return ($aRow ? new self($aRow) : null);
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
				case 'selByTemplateId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "correspondence_template_id = <correspondence_template_id>", NULL, 1);
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