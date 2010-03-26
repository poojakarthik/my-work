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
	protected static	$_strStaticTableName			= "ChargeType";
	
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
	
	protected static function addToCache($mObjects)
	{
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false)
	{
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}
	
	public static function getAll($bForceReload=false)
	{
		return parent::getAll($bForceReload, __CLASS__);
	}
	
	public static function getLastSearchPaginationDetails()
	{
		return self::$lastSearchPaginationDetails;
	}
	
	// Performs a search for ChargeType
	// It is assumed that none of the arguments are escaped yet
	// This will just return the TotalRecordCount if $bGetTotalRecordCountOnly == true
	public static function searchFor($aFilter=null, $aSort=null, $iLimit=null, $iOffset=null, $bGetTotalRecordCountOnly=false)
	{
		$aWhereParts		= array();
		$aOrderByParts	= array();
		
		// Build WHERE clause
		$aWhereClauseParts = array();
		if (is_array($aFilter))
		{
			foreach ($aFilter as $aConstraint)
			{
				switch ($aConstraint['Type'])
				{
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_ARCHIVED:
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_NATURE:
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_AUTOMATIC_ONLY:
					case self::SEARCH_CONSTRAINT_CHARGE_TYPE_VISIBILITY_ID:
						$aWhereClauseParts[] = self::_prepareSearchConstraint(str_replace( '|', '.', $aConstraint['Type']), $aConstraint['Value']);
						break;
				}
			}
		}
		$sWhereClause = (count($aWhereClauseParts))? implode(" AND ", $aWhereClauseParts) : "1";
		
		// Build OrderBy Clause
		if (is_array($aSort))
		{
			foreach ($aSort as $sColumn=>$bAsc)
			{
				switch ($sColumn)
				{
					case self::ORDER_BY_CHARGE_TYPE:
					case self::ORDER_BY_DESCRIPTION:
					case self::ORDER_BY_NATURE:
					case self::ORDER_BY_AMOUNT:
						$aOrderByParts[] = str_replace('|', '.', $sColumn) . ($bAsc ? " ASC" : " DESC");
						break;
					default:
						throw new Exception(__METHOD__ ." - Illegal sorting identifier: $sColumn");
						break;
				}
			}
		}
		$sOrderByClause = (count($aOrderByParts) > 0)? implode(", ", $aOrderByParts) : NULL;
		
		// Build LIMIT clause
		if ($iLimit !== NULL)
		{
			$sLimitClause = intval($iLimit);
			if ($iOffset !== NULL)
			{
				$sLimitClause .= " OFFSET ". intval($iOffset);
			}
			else
			{
				$iOffset = 0;
			}
		}
		else
		{
			$sLimitClause = "";
		}
		
		// Build SELECT statement
		$sFromClause = self::$_strStaticTableName;
		$sSelectClause = '*';
		
		// Create query to find out how many rows there are in total
		$sRowCountQuery = "SELECT COUNT(".self::$_strStaticTableName.".Id) as row_count FROM $sFromClause WHERE $sWhereClause;";
		
		// Check how many rows there are
		$oQuery = new Query();
		
		$mResult = $oQuery->Execute($sRowCountQuery);
		if ($mResult === FALSE)
		{
			throw new Exception("Failed to retrieve total record count for 'Charge Search' query - ". $objQuery->Error());
		}
		
		$iTotalRecordCount = intval(current($mResult->fetch_assoc()));
		
		if ($bGetTotalRecordCountOnly)
		{
			// return the total record count
			return $iTotalRecordCount;
		}
		
		// Create the proper query
		$oRecords = new StatementSelect($sFromClause, $sSelectClause, $sWhereClause, $sOrderByClause, $sLimitClause);
				
		if ($oRecords->Execute() === FALSE)
		{
			throw new Exception("Failed to retrieve records for '{self::$_strStaticTableName} Search' query - ". $oCharges->Error());
		}
		
		// Create the Charge objects (these objects will also include the fields accountName and serviceFNN)
		$aChargeTypeObjects = array();
		$iCurrentIndex = $iOffset;
		
		while ($aRecord = $oRecords->Fetch())
		{
			$aChargeTypeObjects[$iCurrentIndex] = new self($aRecord);
			$iCurrentIndex++;
		}
		
		// Create the pagination details, if a Limit clause was used
		if ($iLimit === NULL || count($aChargeTypeObjects) == 0)
		{
			// Don't bother calulating pagination details
			self::$lastSearchPaginationDetails = null;
		}
		else
		{
			self::$lastSearchPaginationDetails = new PaginationDetails($iTotalRecordCount, $iLimit, intval($iOffset));
		}
		
		return $aChargeTypeObjects;
	}
	
	// Note that this currently only handles "prop IS NULL", "prop IN (list of unquoted values)", "prop = unquoted value"
	private static function _prepareSearchConstraint($sProp, $mValue)
	{
		$sSearch = "";
		if ($mValue === NULL)
		{
			$sSearch = "$sProp IS NULL";
		}
		elseif (is_array($mValue))
		{
			$sSearch = "$sProp IN (". implode(", ", $mValue) .")";
		}
		else
		{
			$sSearch = "$sProp = $mValue";
		}
		return $sSearch;
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
	static public function getByCode($sCode)
	{
		$oByCode	= self::_preparedStatement("selByCode");
		if ($oByCode->Execute(Array('ChargeType'=>$sCode)))
		{
			return new Charge_Type($oByCode->Fetch());
		}
		elseif ($oByCode->Error())
		{
			throw new Exception($oByCode->Error());
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
		$oContractExitFee	= self::_preparedStatement("selContractExitFee");
		if ($oContractExitFee->Execute())
		{
			return new Charge_Type($oContractExitFee->Fetch());
		}
		elseif ($oContractExitFee->Error())
		{
			throw new Exception($oContractExitFee->Error());
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
		$oContractPayoutFee	= self::_preparedStatement("selContractPayoutFee");
		if ($oContractPayoutFee->Execute())
		{
			return new Charge_Type($oContractPayoutFee->Fetch());
		}
		elseif ($oContractPayoutFee->Error())
		{
			throw new Exception($oContractPayoutFee->Error());
		}
		else
		{
			return NULL;
		}
	}
	
	public function save()
	{
		// Set the Defaults
		$this->charge_type_visibility_id = (Charge_Type_Visibility::getForId($this->charge_type_visibility_id)) ? $this->charge_type_visibility_id : Charge_Type_Visibility::getForSystemName('VISIBLE');
		
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
	protected static function _preparedStatement($sStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$sStatement]))
		{
			return $arrPreparedStatements[$sStatement];
		}
		else
		{
			switch ($sStatement)
			{
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(	"ChargeType", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selByCode':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(	"ChargeType", "*", "ChargeType = <ChargeType> AND Archived = 0", NULL, 1);
					break;
				case 'selContractPayoutFee':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(	"contract_terms JOIN ChargeType ON ChargeType.Id = contract_terms.payout_charge_type_id", "ChargeType.*", "1", NULL, 1);
					break;
				case 'selContractExitFee':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(	"contract_terms JOIN ChargeType ON ChargeType.Id = contract_terms.exit_fee_charge_type_id", "ChargeType.*", "1", NULL, 1);
					break;
				case 'selAll':
					$arrPreparedStatements[$sStatement]	= new StatementSelect(self::$_strStaticTableName, "*", "1");
					break;
				// INSERTS
				case 'insSelf':
					$arrPreparedStatements[$sStatement]	= new StatementInsert("ChargeType");
					break;
				
				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$sStatement]	= new StatementUpdateById("ChargeType");
					break;
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$sStatement} does not exist!");
			}
			return $arrPreparedStatements[$sStatement];
		}
	}
}
?>