<?php
//----------------------------------------------------------------------------//
// Charge_Type
//----------------------------------------------------------------------------//
/**
 * Charge_Type
 *
 * Models a record of the ChargeType table
 *
 * Models a record of the ChargeType table
 *
 * @class	Charge_Type
 */
class Charge_Type extends ORM_Cached
{	
	protected 			$_strTableName					= "ChargeType";
	protected static	$_sStaticTableName				= "ChargeType";
	
	protected static	$lastSearchPaginationDetails	= null;
	
	const SEARCH_CONSTRAINT_CHARGE_TYPE_ARCHIVED		= "ChargeType|Archived";
	const SEARCH_CONSTRAINT_CHARGE_TYPE_NATURE			= "ChargeType|Nature";
	const SEARCH_CONSTRAINT_CHARGE_TYPE_AUTOMATIC_ONLY	= "ChargeType|Automatic_Only";
	const SEARCH_CONSTRAINT_CHARGE_TYPE_VISIBILITY_ID	= "ChargeType|Visibility_Id";

	const ORDER_BY_CHARGE_TYPE							= "ChargeType|ChargeType";
	const ORDER_BY_DESCRIPTION							= "ChargeType|Description";
	const ORDER_BY_NATURE								= "ChargeType|Nature";
	const ORDER_BY_AMOUNT								= "ChargeType|Amount";
	
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
	
	public static function getLastSearchPaginationDetails()
	{
		return self::$lastSearchPaginationDetails;
	}
	
	// Performs a search for ChargeType
	// It is assumed that none of the arguments are escaped yet
	// This will just return the TotalRecordCount if $bolGetTotalRecordCountOnly == true
	public static function searchFor($arrFilter=null, $arrSort=null, $intLimit=null, $intOffset=null, $bolGetTotalRecordCountOnly=false)
	{
		$arrWhereParts		= array();
		$arrOrderByParts	= array();
		
		// Build WHERE clause
		$arrWhereClauseParts = array();
		if (is_array($arrFilter))
		{
			foreach ($arrFilter as $arrConstraint)
			{
				switch ($arrConstraint['Type'])
				{
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_ARCHIVED:
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_NATURE:
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_AUTOMATIC_ONLY:
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_VISIBILITY_ID:
						$arrWhereClauseParts[] = self::_prepareSearchConstraint(str_replace( '|', '.', $arrConstraint['Type']), $arrConstraint['Value']);
						break;
				}
			}
		}
		$strWhereClause = (count($arrWhereClauseParts))? implode(" AND ", $arrWhereClauseParts) : "1";
		
		// Build OrderBy Clause
		if (is_array($arrSort))
		{
			foreach ($arrSort as $strColumn=>$bolAsc)
			{
				switch ($strColumn)
				{
					case self::ORDER_BY_CHARGE_TYPE:
					case self::ORDER_BY_DESCRIPTION:
					case self::ORDER_BY_NATURE:
					case self::ORDER_BY_AMOUNT:
						$arrOrderByParts[] = str_replace('|', '.', $strColumn) . ($bolAsc ? " ASC" : " DESC");
						break;
					default:
						throw new Exception(__METHOD__ ." - Illegal sorting identifier: $strColumn");
						break;
				}
			}
		}
		$strOrderByClause = (count($arrOrderByParts) > 0)? implode(", ", $arrOrderByParts) : NULL;
		
		// Build LIMIT clause
		if ($intLimit !== NULL)
		{
			$strLimitClause = intval($intLimit);
			if ($intOffset !== NULL)
			{
				$strLimitClause .= " OFFSET ". intval($intOffset);
			}
			else
			{
				$intOffset = 0;
			}
		}
		else
		{
			$strLimitClause = "";
		}
		
		// Build SELECT statement
		$strFromClause = self::$_sStaticTableName;
		
		// Create the SELECT clause
		/*$arrColumns = self::_getColumns();

		$arrColumnsForSelectClause = array();
		foreach ($arrColumns as $strTidyName=>$strName)
		{
			$arrColumnsForSelectClause[] = self::$_sStaticTableName.".{$strName} AS $strTidyName";
		}

		$strSelectClause = implode(',', $arrColumnsForSelectClause);
		*/
		
		$strSelectClause = '*';
		
		// Create query to find out how many rows there are in total
		$strRowCountQuery = "SELECT COUNT(".self::$_sStaticTableName.".Id) as row_count FROM $strFromClause WHERE $strWhereClause;";
		
		// Check how many rows there are
		$objQuery = new Query();
		
		$mixResult = $objQuery->Execute($strRowCountQuery);
		if ($mixResult === FALSE)
		{
			throw new Exception("Failed to retrieve total record count for 'Charge Search' query - ". $objQuery->Error());
		}
		
		$intTotalRecordCount = intval(current($mixResult->fetch_assoc()));
		
		if ($bolGetTotalRecordCountOnly)
		{
			// return the total record count
			return $intTotalRecordCount;
		}
		
		// Create the proper query
		$selRecords = new StatementSelect($strFromClause, $strSelectClause, $strWhereClause, $strOrderByClause, $strLimitClause);
				
		if ($selRecords->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve records for '{self::$_sStaticTableName} Search' query - ". $selCharges->Error());
		}

		// Create the Charge objects (these objects will also include the fields accountName and serviceFNN)
		$arrChargeTypeObjects = array();
		while ($arrRecord = $selRecords->Fetch())
		{
			$arrChargeTypeObjects[$arrRecord['Id']] = new self($arrRecord);
		}
		
		// Create the pagination details, if a Limit clause was used
		if ($intLimit === NULL || count($arrChargeTypeObjects) == 0)
		{
			// Don't bother calulating pagination details
			self::$lastSearchPaginationDetails = null;
		}
		else
		{
			self::$lastSearchPaginationDetails = new PaginationDetails($intTotalRecordCount, $intLimit, intval($intOffset));
		}
		
		return $arrChargeTypeObjects;
	}
	
	// Note that this currently only handles "prop IS NULL", "prop IN (list of unquoted values)", "prop = unquoted value"
	private static function _prepareSearchConstraint($strProp, $mixValue)
	{
		$strSearch = "";
		if ($mixValue === NULL)
		{
			$strSearch = "$strProp IS NULL";
		}
		elseif (is_array($mixValue))
		{
			$strSearch = "$strProp IN (". implode(", ", $mixValue) .")";
		}
		else
		{
			$strSearch = "$strProp = $mixValue";
		}
		return $strSearch;
	}
	
	// Retrieves a list of column names (array[tidyName] = 'ActualColumnName')
	private static function _getColumns()
	{
		static $arrColumns;
		if (!isset($arrColumns))
		{
			$arrTableDefine = DataAccess::getDataAccess()->FetchTableDefine(self::$_sStaticTableName);
			
			foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
			{
				$arrColumns[self::tidyName($strName)] = $strName;
			}
			$arrColumns[self::tidyName($arrTableDefine['Id'])] = $arrTableDefine['Id'];
		}
		
		return $arrColumns;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	//------------------------------------------------------------------------//
	// getByCode()
	//------------------------------------------------------------------------//
	/**
	 * getByCode()
	 *
	 * Rerieves the ChargeType by its Code
	 *
	 * Rerieves the ChargeType by its Code
	 * 
	 * @param	string	$strCode		The ChargeType Code
	 * 
	 * @return	mixed					Charge_Type on Success
	 * 									NULL on Failure
	 *
	 * @method
	 */
	static public function getByCode($strCode)
	{
		$selByCode	= self::_preparedStatement("selByCode");
		if ($selByCode->Execute(Array('ChargeType'=>$strCode)))
		{
			return new Charge_Type($selByCode->Fetch());
		}
		elseif ($selByCode->Error())
		{
			throw new Exception($selByCode->Error());
		}
		else
		{
			return NULL;
		}
	}
	
	//------------------------------------------------------------------------//
	// getContractExitFee()
	//------------------------------------------------------------------------//
	/**
	 * getContractExitFee()
	 *
	 * Rerieves the Contract Exit Fee Charge Type
	 *
	 * Rerieves the Contract Exit Fee Charge Type
	 * 
	 * @return	Charge_Type
	 *
	 * @method
	 */
	static public function getContractExitFee()
	{
		$selContractExitFee	= self::_preparedStatement("selContractExitFee");
		if ($selContractExitFee->Execute())
		{
			return new Charge_Type($selContractExitFee->Fetch());
		}
		elseif ($selContractExitFee->Error())
		{
			throw new Exception($selContractExitFee->Error());
		}
		else
		{
			return NULL;
		}
	}
	
	//------------------------------------------------------------------------//
	// getContractPayoutFee()
	//------------------------------------------------------------------------//
	/**
	 * getContractPayoutFee()
	 *
	 * Rerieves the Contract Payout Fee Charge Type
	 *
	 * Rerieves the Contract Payout Fee Charge Type
	 * 
	 * @return	Charge_Type
	 *
	 * @method
	 */
	static public function getContractPayoutFee()
	{
		$selContractPayoutFee	= self::_preparedStatement("selContractPayoutFee");
		if ($selContractPayoutFee->Execute())
		{
			return new Charge_Type($selContractPayoutFee->Fetch());
		}
		elseif ($selContractPayoutFee->Error())
		{
			throw new Exception($selContractPayoutFee->Error());
		}
		else
		{
			return NULL;
		}
	}
	
	public function save()
	{
		// Set the Defaults
		$this->charge_type_visibility_id	= (Charge_Type_Visibility::getForId($this->charge_type_visibility_id)) ? $this->charge_type_visibility_id : Charge_Type_Visibility::getForSystemName('VISIBLE');
		
		parent::save();
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by this Class
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ChargeType", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selByCode':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ChargeType", "*", "ChargeType = <ChargeType> AND Archived = 0", NULL, 1);
					break;
				case 'selContractPayoutFee':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"contract_terms JOIN ChargeType ON ChargeType.Id = contract_terms.payout_charge_type_id", "ChargeType.*", "1", NULL, 1);
					break;
				case 'selContractExitFee':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"contract_terms JOIN ChargeType ON ChargeType.Id = contract_terms.exit_fee_charge_type_id", "ChargeType.*", "1", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(self::$_sStaticTableName, "*", "1");
					break;
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("ChargeType");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("ChargeType");
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