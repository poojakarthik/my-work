<?php
/**
 * Correspondence
 *
 * This is an example of a class that extends ORM_Cached
 *
 * @class	Correspondence
 */
class Correspondence extends ORM_Cached
{
	protected 			$_strTableName			= "correspondence";
	protected static	$_strStaticTableName	= "correspondence";

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

	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function getFieldNames()
	{
		$arrTableDefine		= DataAccess::getDataAccess()->FetchTableDefine(self::$_strStaticTableName);
		return array_keys($arrTableDefine['Column']);
	}

	/*public function toArray()
	{
		return $this->_arrProperties;
	}*/

	public static function getForRunId($iRunId)
	{
		$oSelect	= self::_preparedStatement('selByRunId');
		$oSelect->Execute(array('correspondence_run_id' => $iRunId));
		$aResults = $oSelect->FetchAll();
		$aObjects = array();
		foreach ($aResults as $aResult)
		{
			$x =new self($aResult);
			$x->setSaved();
			$aObjects[]= $x;
		}
		return $aObjects;
	}

	public static function getForAccountId($iAccountId)
	{
		$oSelect	= self::_preparedStatement('selByAccountId');
		$oSelect->Execute(array('account_id' => $iAccountId));
		$aResults = $oSelect->FetchAll();
		$aObjects = array();
		foreach ($aResults as $aResult)
		{
			$x =new self($aResult);
			$x->setSaved();
			$aObjects[]= $x;
		}
		return $aObjects;
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




	public static function getLedgerInformation($bCountOnly=false, $iLimit=null, $iOffset=0, $aFilter=null, $aSort=null, $bDelivered=false)
	{
		$sFrom			= "	correspondence c
							JOIN CustomerGroup cg ON cg.id = c.customer_group_id
							JOIN correspondence_delivery_method cdm ON cdm.id = c.correspondence_delivery_method_id
							JOIN correspondence_run cr ON cr.id = c.correspondence_run_id
							JOIN correspondence_template ct ON ct.id = cr.correspondence_template_id";
		if ($bCountOnly)
		{
			$sSelect	= "	count(c.id) AS record_count";
		}
		else
		{
			$sSelect	= "	c.*,
							cg.internal_name AS customer_group_name,
							cdm.name AS correspondence_delivery_method_name,
							ct.name AS correspondence_template_name,
							ct.template_code AS correspondence_template_code,
							cr.delivered_datetime AS correspondence_run_delivered_datetime";
		}

		$aWhereAlias	=	array(
								'id'									=> 'c.id',
								'correspondence_run_id'					=> 'c.correspondence_run_id',
								'account_id'							=> 'c.account_id',
								'customer_group_id'						=> 'c.customer_group_id',
								'correspondence_delivery_method_id'		=> 'c.correspondence_delivery_method_id',
								'account_name'							=> 'c.account_name',
								'email'									=> 'c.email',
								'mobile'								=> 'c.mobile',
								'landline'								=> 'c.landline',
								'title'									=> 'c.title',
								'first_name'							=> 'c.first_name',
								'last_name'								=> 'c.last_name',
								'address_line_1'						=> 'c.address_line_1',
								'address_line_2'						=> 'c.address_line_2',
								'suburb'								=> 'c.suburb',
								'postcode'								=> 'c.postcode',
								'state'									=> 'c.state',
								'correspondence_delivery_method_name'	=> 'cdm.name',
								'correspondence_template_name'			=> "ct.template_code",
								"correspondence_run_delivered_datetime"	=> "cr.delivered_datetime"
							);
		$aWhere			= StatementSelect::generateWhere($aWhereAlias, $aFilter);
		$aSortAlias		= $aWhereAlias;
		$sOrderByClause	= StatementSelect::generateOrderBy($aSortAlias, $aSort);
		$sLimitClause	= StatementSelect::generateLimit($iLimit, $iOffset);
		$sWhereClause	= $aWhere['sClause'].($bDelivered ? " AND cr.delivered_datetime IS NOT NULL" : "");

		$oStmt	=	new StatementSelect(
						$sFrom,
						$sSelect,
						$sWhereClause,
						($bCountOnly ? '' : $sOrderByClause),
						($bCountOnly ? '' : $sLimitClause)
					);

		if ($oStmt->Execute($aWhere['aValues']) === false)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ".$oStmt->Error());
		}

		if ($bCountOnly)
		{
			// Count only
			$aRow	= $oStmt->Fetch();
			return $aRow['record_count'];
		}
		else
		{
			//throw new Exception($oStmt->_strQuery);

			// Results required
			$aResults	= array();
			while ($aRow = $oStmt->Fetch())
			{
				$aResults[$aRow['id']]	= $aRow;
			}
			return $aResults;
		}
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
				case 'selByAccountId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "account_id = <account_id>");
					break;
				case 'selByRunId':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "correspondence_run_id = <correspondence_run_id>");
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