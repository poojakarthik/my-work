<?php

//----------------------------------------------------------------------------//
// Statement
//----------------------------------------------------------------------------//
/**
 * Statement
 *
 * Statement Abstract Base Class
 *
 * Statement Abstract Base Class
 *
 *
 * @prefix		bst
 *
 * @package		framework
 * @class		Statement
 */
 abstract class Statement extends DatabaseAccess
 {
  	//------------------------------------------------------------------------//
	// stmtSqlStatement
	//------------------------------------------------------------------------//
	/**
	 * stmtSqlStatement
	 *
	 * Stores our statement
	 *
	 * Stores our statement
	 *
	 * @type		mysql_stmt
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	protected $_stmtSqlStatment;
	
	//------------------------------------------------------------------------//
	// arrPlaceholders
	//------------------------------------------------------------------------//
	/**
	 * arrPlaceholders
	 *
	 * Indexed array of placeholders used in the prepared statement, stored in the order in which they are found in the statement
	 *
	 * Indexed array of placeholders used in the prepared statement, stored in the order in which they are found in the statement
	 *
	 * @type		array
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	private $_arrPlaceholders;
	
	//------------------------------------------------------------------------//
	// strTable
	//------------------------------------------------------------------------//
	/**
	 * strTable
	 *
	 * Name of the table we're working with (if UPDATE or INSERT)
	 *
	 * Name of the table we're working with (if UPDATE or INSERT)
	 *
	 * @type		string
	 *
	 * @property
	 * @see			<MethodName()||typePropertyName>
	 */
	private $_strTable;

 	//------------------------------------------------------------------------//
	// Statement() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * Statement()
	 *
	 * Constructor for Statement
	 *
	 * Constructor for Statement Abstract Base Class
	 *
	 * @return		void
	 *
	 * @method
	 */
	 function __construct($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
	 {
	 	$this->aProfiling['fPreparationStart']	= microtime(true);
	 	
	 	$this->intSQLMode = SQL_STATEMENT;
		parent::__construct($strConnectionType);
	 }
	 
	/**
	 * _prepare()
	 *
	 * Prepares the Statement
	 *
	 * @return		void
	 *
	 * @param		string	$sQuery
	 *
	 * @method
	 */
	 protected function _prepare($sQuery)
	 {
		$this->Trace("Query: {$sQuery}");
		
		$this->_strQuery		= $sQuery;
	 	$this->_stmtSqlStatment	= $this->db->refMysqliConnection->stmt_init();
		
	 	if (!$this->_stmtSqlStatment->prepare($sQuery))
	 	{
	 		// There was problem preparing the statment
	 		//throw new Exception("Could not prepare statement : $strQuery\n");
			// Trace
			
			$this->Trace("Error: ".$this->Error());
			Debug($this->Error());
			
			//throw new Exception($this->Error());
	 	}
		
		$this->aProfiling['fPreparationTime']	= microtime(true) - $this->aProfiling['fPreparationStart'];
		$this->aProfiling['sQuery']				= $sQuery;
	 }
	 
	
	
	//------------------------------------------------------------------------//
	// GetDBInputType()
	//------------------------------------------------------------------------//
	/**
	 * GetDBInputType()
	 *
	 * Determines the type of a passed variable
	 *
	 * Determines the type of a passed variable.
	 * Returns:		"s" - String
	 * 				"i" - Integer
	 * 				"d" - Float/Double
	 * 				"b" - Binary
	 *
	 * @param		mixed	$mixData		Data to be checked
	 *
	 * @return		string					"s" : String
	 * 										"i" : Integer
	 * 										"d" : Float/Double
	 * 										"b" : Binary
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */
	function GetDBInputType($mixData)
	{
		// Special case for mysql functions
		
		
		//print_r($mixData);
		if ($mixData instanceOf MySQLFunction)
		{
			return "i";
		}
		elseif (is_int($mixData))
 		{
 			// It's an integer
 			// Must be specified as a float, because 'i' has incompatibilities with BIGINTs
 			return "d";
 		}
 		elseif (is_float($mixData))
 		{
 			// It's a float/double
 			return "d";
 		}
		/*
		 * this was commented on nov. 2 2006 because of conflicts with string
 		elseif (!is_scalar($mixData))
 		{
 			// It's a binary object
 			return "b";
 		}
		*/
 		
 		// Else, it's a string
 		return "s";
	}
	
	/**
	 * Wraps the Statement's Execute around lock-checking logic
	 */
	protected function _execute()
	{
		// Execute
		if ($this->_stmtSqlStatment->execute())
		{
			// Pass
			return true;
		}
		elseif ($this->_stmtSqlStatment->errno == DatabaseAccess::ER_LOCK_DEADLOCK)
		{
			// Failure -- Deadlock
			throw new Exception_Database_Deadlock($this->Error());
		}
		elseif ($this->_stmtSqlStatment->errno == DatabaseAccess::ER_LOCK_WAIT_TIMEOUT)
		{
			// Failure -- Lock wait timeout
			throw new Exception_Database_LockTimeout($this->Error());
		}
		else
		{
			return false;
		}
	}
	
	public static function generateWhere($aAliases=array(), $aConstraints=null)
	{
		$aWhereParts	= array();
		$aResult		= array('sClause' => '','aValues' => array());
		
		if ($aConstraints)
		{
			foreach($aConstraints as $sAlias => $mValue)
			{
				if (isset($aAliases[$sAlias]))
				{
					$sAlias	= $aAliases[$sAlias];
				}
				
				self::processWhereConstraint($sAlias, $mValue, $aWhereParts, $aResult);
				
				/*if (is_array($mValue))
				{
					// AND (array of constraints)
					foreach ($mValue as $mVal)
					{
						self::processWhereConstraint($sAlias, $mVal, $aWhereParts, $aResult);
					}
				}
				else if (is_object($mValue))
				{
					if ($mValue->mFrom || $mValue->mTo)
					{
						// Range of values
						if ($mValue->mFrom && $mValue->mTo)
						{
							// BETWEEN
							$sFromAlias							= "{$sAlias}0";
							$sToAlias							= "{$sAlias}1";
							$aResult['aValues'][$sFromAlias]	= $mValue->mFrom;
							$aResult['aValues'][$sToAlias]		= $mValue->mTo;
							$aWhereParts[]						= "{$sAlias} BETWEEN <{$sFromAlias}> AND <{$sToAlias}>";
						}
						else if ($mValue->mFrom)
						{
							// > (Greater than)
							$sValueAlias						= $sAlias;
							$aResult['aValues'][$sValueAlias]	= $mValue->mFrom;
							$aWhereParts[]						= "{$sAlias} > <{$sValueAlias}>";
						}
						else if ($mValue->mTo)
						{
							// < (Less than)
							$sValueAlias						= $sAlias;
							$aResult['aValues'][$sValueAlias]	= $mValue->mTo;
							$aWhereParts[]						= "{$sAlias} < <{$sValueAlias}>";
						}
					}
					else if ($mValue->sStartsWith)
					{
						// LIKE, starting with...
						$sValueAlias						= $sAlias;
						$aResult['aValues'][$sValueAlias]	= "'{$mValue->sStartsWith}%'";
						$aWhereParts[]						= "{$sAlias} LIKE <{$sValueAlias}>";
					}
					else if ($mValue->sEndsWith)
					{
						// LIKE, ending with...
						$sValueAlias						= $sAlias;
						$aResult['aValues'][$sValueAlias]	= "'%{$mValue->sEndsWith}'";
						$aWhereParts[]						= "{$sAlias} LIKE <{$sValueAlias}>";
					}
					else if ($mValue->sContains)
					{
						// LIKE, containing...
						$sValueAlias						= $sAlias;
						$aResult['aValues'][$sValueAlias]	= "'%{$mValue->sContains}%'";
						$aWhereParts[]						= "{$sAlias} LIKE <{$sValueAlias}>";
					}
					else if ($mValue->aValues)
					{
						// An array of values, convert to IN (?,?,?)
						$iValueIndex	= 0;
						$aValueAliases	= array();
						foreach ($mValue->aValues as $mVal)
						{
							$sValueAlias						= "{$sAlias}{$iValueIndex}";
							$aResult['aValues'][$sValueAlias]	= $mVal;
							$aValueAliases[]					= "<{$sValueAlias}>";
							$iValueIndex++;
						}
						
						$aWhereParts[]	= "{$sAlias} IN (".implode(', ', $aValueAliases).")";
					}
				}
				else if (strtolower($mValue) == 'null')
				{
					// Value is a null comparison
					$sValueAlias						= $sAlias;
					$aResult['aValues'][$sValueAlias]	= $mValue;
					$aWhereParts[]						= "{$sAlias} IS NULL";
				}
				else
				{
					// Value is a single value
					$sValueAlias						= $sAlias;
					$aResult['aValues'][$sValueAlias]	= $mValue;
					$aWhereParts[]						= "{$sAlias} = <{$sValueAlias}>";
				}*/
			}
		}
		
		$aResult['sClause']	= implode(' AND ', $aWhereParts);
		return $aResult;
	}
	
	private static function processWhereConstraint($sAlias, $mValue, &$aWhereParts, &$aResult, $sPlaceHolderSuffix='')
	{
		if (is_array($mValue))
		{
			// AND (array of constraints)
			$iSuffix	= 1;
			foreach ($mValue as $mVal)
			{
				self::processWhereConstraint($sAlias, $mVal, $aWhereParts, $aResult, $iSuffix);
				$iSuffix++;
			}
		}
		else if (is_object($mValue))
		{
			if ($mValue->mFrom || $mValue->mTo)
			{
				// Range of values
				if ($mValue->mFrom && $mValue->mTo)
				{
					// BETWEEN
					$sFromPlaceHolder						= "{$sAlias}{$sPlaceHolderSuffix}0";
					$sToPlaceHolder							= "{$sAlias}{$sPlaceHolderSuffix}1";
					$aResult['aValues'][$sFromPlaceHolder]	= $mValue->mFrom;
					$aResult['aValues'][$sToPlaceHolder]	= $mValue->mTo;
					$aWhereParts[]							= "{$sAlias} BETWEEN <{$sFromPlaceHolder}> AND <{$sToPlaceHolder}>";
				}
				else if ($mValue->mFrom)
				{
					// > (Greater than)
					$sPlaceHolder						= $sAlias.$sPlaceHolderSuffix;
					$aResult['aValues'][$sPlaceHolder]	= $mValue->mFrom;
					$aWhereParts[]						= "{$sAlias} >= <{$sPlaceHolder}>";
				}
				else if ($mValue->mTo)
				{
					// < (Less than)
					$sPlaceHolder						= $sAlias.$sPlaceHolderSuffix;
					$aResult['aValues'][$sPlaceHolder]	= $mValue->mTo;
					$aWhereParts[]						= "{$sAlias} <= <{$sPlaceHolder}>";
				}
			}
			else if ($mValue->sStartsWith)
			{
				// LIKE, starting with...
				$sPlaceHolder						= $sAlias.$sPlaceHolderSuffix;
				$aResult['aValues'][$sPlaceHolder]	= "'{$mValue->sStartsWith}%'";
				$aWhereParts[]						= "{$sAlias} LIKE <{$sPlaceHolder}>";
			}
			else if ($mValue->sEndsWith)
			{
				// LIKE, ending with...
				$sPlaceHolder						= $sAlias.$sPlaceHolderSuffix;
				$aResult['aValues'][$sPlaceHolder]	= "'%{$mValue->sEndsWith}'";
				$aWhereParts[]						= "{$sAlias} LIKE <{$sPlaceHolder}>";
			}
			else if ($mValue->sContains)
			{
				// LIKE, containing...
				$sPlaceHolder						= $sAlias.$sPlaceHolderSuffix;
				$aResult['aValues'][$sPlaceHolder]	= "'%{$mValue->sContains}%'";
				$aWhereParts[]						= "{$sAlias} LIKE <{$sPlaceHolder}>";
			}
			else if ($mValue->aValues && count($mValue->aValues) > 0)
			{
				// An array of values, convert to IN (?,?,?)
				$iValueIndex	= 0;
				$aPlaceHolders	= array();
				foreach ($mValue->aValues as $mVal)
				{
					$sPlaceHolder						= "{$sAlias}{$sPlaceHolderSuffix}{$iValueIndex}";
					$aResult['aValues'][$sPlaceHolder]	= $mVal;
					$aPlaceHolders[]					= "<{$sPlaceHolder}>";
					$iValueIndex++;
				}
				
				$aWhereParts[]	= "{$sAlias} IN (".implode(', ', $aPlaceHolders).")";
			}
		}
		else if (strtolower($mValue) == 'null')
		{
			// Value is a null comparison
			$sPlaceHolder						= $sAlias.$sPlaceHolderSuffix;
			$aResult['aValues'][$sPlaceHolder]	= $mValue;
			$aWhereParts[]						= "{$sAlias} IS NULL";
		}
		else
		{
			// Value is a single value
			$sPlaceHolder						= $sAlias.$sPlaceHolderSuffix;
			$aResult['aValues'][$sPlaceHolder]	= $mValue;
			$aWhereParts[]						= "{$sAlias} = <{$sPlaceHolder}>";
		}
	}
	
	public static function generateOrderBy($aAliases=array(), $aOrderBy=null)
	{
		$sOrderBy	= '';
		
		if ($aOrderBy && is_array($aOrderBy))
		{
			$aSortFields	= array();
			foreach ($aOrderBy as $sAlias => $sDirection)
			{
				if (isset($aAliases[$sAlias]))
				{
					$sAlias	= $aAliases[$sAlias];
				}
				
				$aSortFields[]	= "{$sAlias} {$sDirection}";
			}
			
			if (count($aSortFields))
			{
				$sOrderBy	= implode(', ', $aSortFields);
			}
		}
		
		return $sOrderBy;
	}
	
	public static function generateLimit($iLimit=null, $iOffset=null)
	{
		$sLimit	= '';
		
		if ($iLimit !== NULL)
		{
			$sLimit	= intval($iLimit);
			
			if ($iOffset !== NULL)
			{
				$sLimit	.= " OFFSET ". intval($iOffset);
			}
		}
		
		return $sLimit;
	}
}

?>