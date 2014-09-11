<?php
/**
 * Correspondence_Template
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Correspondence_Template
 */
class Email_Template_Type extends ORM_Cached
{
	protected 			$_strTableName			= "email_template_type";
	protected static	$_strStaticTableName	= "email_template_type";

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


	public static function getForAllCustomerGroups($bCountOnly=false, $iLimit=null, $iOffset=null, $sSortDirection='DESC')
	{

		if ($bCountOnly)
		{
			// Count records only
			$oQuery	= new Query();
			$sQuery	= "	SELECT	count(*) as template_count
						FROM	email_template_type
						";
			$mResult	= $oQuery->Execute($sQuery);
			if ($mResult === false)
			{
				// Most likely a sql or connectivity error
				throw new Exception_Database("Unable to count email template type records, SQL Error. ".$oQuery->Error());
			}
			$aRow	= $mResult->fetch_assoc();
			return $aRow['template_count'];
		}
		else
		{

			// Return all records
			$sLimit	= StatementSelect::generateLimit($iLimit, $iOffset);
			$oQuery	= new Query();
			$sQuery	= "	SELECT	*
						FROM	email_template_type
						ORDER BY name {$sSortDirection}
						LIMIT {$sLimit}";
			$mResult	= $oQuery->Execute($sQuery);
			if ($mResult === false)
			{
				// Most likely a sql or connectivity error
				throw new Exception_Database("Unable to retrieve correspondence_run_batch records, SQL Error. ".$oQuery->Error());
			}

			// Create ORM objects and return
			$aTeamplates	= array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$oTemplate = new stdClass();
				$oTemplate->id = $aRow['id'];
				$oTemplate->name = $aRow['name'];
				$oTemplate->description = $aRow['description'];
				$oTemplate->customerGroupInstances = array();


				$oCustmerGroupQuery	= new Query();
				$sCustomerGroupQuery	= "	SELECT	e.id, c.external_name
											FROM	email_template e , CustomerGroup c
											where e.customer_group_id = c.Id
											AND email_template_type_id = ".$aRow['id'];
				$mCustomerGroupResult	= $oCustmerGroupQuery->Execute($sCustomerGroupQuery);
				if ($mCustomerGroupResult === false)
				{
					// Most likely a sql or connectivity error
					throw new Exception_Database("Unable to retrieve correspondence_run_batch records, SQL Error. ".$oQuery->Error());
				}
				while ($aNestedRow = $mCustomerGroupResult->fetch_assoc())
				{
					//$oOrm = Email_Template::getForId($aNestedRow['id']);
					$oTemplate->customerGroupInstances[]= $aNestedRow;//$oOrm->toArray();
				}

				$aTeamplates[]	= $oTemplate;
			}
			return $aTeamplates;



		}

	}

	public function getTemplateVersionDetailsForCustomerGroup($iCustomerGroup)
	{

		$sSql = 'SELECT id
				 FROM email_template_type e';
		$oTemplateTypeQuery = new Query();
		$mResult = $oTemplateTypeQuery->Execute($sSql);
		while ($aRow = $mResult->fetch_assoc())
		{
			try
			{
				Email_Template::getForCustomerGroupAndType($iCustomerGroup, $aRow[id]);
			}
			catch(Exception $e)
			{
				if (strstr($e->getMessage(), 'Failed to get Email_Template for customer group & type.'))
				{
					$oTemplate = new Email_Template();
					$oTemplate->customer_group_id = $iCustomerGroup;
					$oTemplate->email_template_type_id = $aRow[id];
					$oTemplate->save();

				}
				else
				{
					throw $e;
				}
			}
		}




		$sSql = 'SELECT et.id,
					e.name,
					ed.effective_datetime,
					COALESCE(ed.description, "There is No Current Version for this Template") as description
					FROM email_template_type e
					JOIN email_template et ON (et.email_template_type_id = e.id)
					LEFT JOIN email_template_details ed ON (ed.email_template_id = et.id AND ed.effective_datetime<NOW() AND ed.end_datetime>NOW())
					WHERE et.customer_group_id ='.$iCustomerGroup;
		$oCustmerGroupQuery	= new Query();
		$mCustomerGroupResult	= $oCustmerGroupQuery->Execute($sSql);
		$aTemplateVersionDetails = array();
		while ($aRow = $mCustomerGroupResult->fetch_assoc())
		{
			$aTemplateVersionDetails[]= $aRow;//$oOrm->toArray();
		}

		return $aTemplateVersionDetails;

	}



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