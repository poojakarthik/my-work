<?php
/**
 * Constant_Group
 *
 * Handles Constant Groups in the $GLOBALS['**arrConstants'] array
 *
 * @class	Constant_Group
 */
class Constant_Group
{
	protected	$_strConstantGroupName;
	
	protected	$_arrAliasToValueMap	= array();
	
	/**
	 * __construct()
	 *
	 * Private Constructor
	 *
	 * @param	string	$strConstantGroupName			Name of the Constant Group
	 *
	 * @return	void
	 *
	 * @constructor
	 */
	private function __construct($strConstantGroupName)
	{
		$this->_strConstantGroupName	= $strConstantGroupName;
		
		// Map alias's to their values (so the values can be referenced by their alias's even if they are not defined as php constants)
		if (array_key_exists($strConstantGroupName, $GLOBALS['*arrConstant']))
		{
			foreach ($GLOBALS['*arrConstant'][$strConstantGroupName] as $mixValue=>$arrDetails)
			{
				// It is assumed $arrDetails is in the correct format and no more checks are required
				$this->_arrAliasToValueMap[$arrDetails['Constant']] = $mixValue;
			}
		}
		else
		{
			throw new exception("Constant Group '$strConstantGroupName' is not currently in \$GLOBALS['*arrConstant']");
		}
	}

	/**
	 * getValue()
	 *
	 * Retrieves the Value associated with the Constant Alias specified
	 *
	 * @param	string	$strAlias			Constant alias for the value (must be within this Constant_Group)
	 *
	 * @return	mix		the value that corresponds to the constant alias
	 *
	 * @method
	 */
	public function getValue($strAlias)
	{
		if (array_key_exists($strAlias, $this->_arrAliasToValueMap))
		{
			return $this->_arrAliasToValueMap[$strAlias];
		}
		else
		{
			throw new exception("Constant Alias, '$strAlias', could not be found in the ConstantGroup, {$this->_strConstantGroupName}");
		}
	}

	
	/**
	 * getConstantGroup()
	 *
	 * Retrieves the Name of a Constant with a given index/value
	 *
	 * @param	mixed	$mixIndex			Index/Value of the Constant in this Group
	 *
	 * @return	string
	 *
	 * @method
	 */
	public function getConstantName($mixIndex)
	{
		if (array_key_exists($mixIndex, $GLOBALS['*arrConstant'][$this->_strConstantGroupName]))
		{
			if (array_key_exists('Name', $GLOBALS['*arrConstant'][$this->_strConstantGroupName][$mixIndex]))
			{
				return $GLOBALS['*arrConstant'][$this->_strConstantGroupName][$mixIndex]['Name'];
			}
			else
			{
				return $GLOBALS['*arrConstant'][$this->_strConstantGroupName][$mixIndex]['Description'];
			}
		}
		else
		{
			throw new Exception("Constant with index/value '{$mixIndex}' is not defined in Constant Group '{$this->_strConstantGroupName}'!");
		}
	}
	
	/**
	 * getConstantDescription()
	 *
	 * Retrieves the Description of a Constant with a given index/value
	 *
	 * @param	mixed	$mixIndex			Index/Value of the Constant in this Group
	 *
	 * @return	string
	 *
	 * @method
	 */
	public function getConstantDescription($mixIndex)
	{
		if (array_key_exists($mixIndex, $GLOBALS['*arrConstant'][$this->_strConstantGroupName]))
		{
			return $GLOBALS['*arrConstant'][$this->_strConstantGroupName][$mixIndex]['Description'];
		}
		else
		{
			throw new Exception("Constant with index/value '{$mixIndex}' is not defined in Constant Group '{$this->_strConstantGroupName}'!");
		}
	}
	
	/**
	 * getConstantAlias()
	 *
	 * Retrieves the Alias (eg. CONSTANT_ALIAS) of a Constant with a given index/value
	 *
	 * @param	mixed	$mixIndex			Index/Value of the Constant in this Group
	 *
	 * @return	string
	 *
	 * @method
	 */
	public function getConstantAlias($mixIndex)
	{
		if (array_key_exists($mixIndex, $GLOBALS['*arrConstant'][$this->_strConstantGroupName]))
		{
			return $GLOBALS['*arrConstant'][$this->_strConstantGroupName][$mixIndex]['Constant'];
		}
		else
		{
			throw new Exception("Constant with index/value '{$mixIndex}' is not defined in Constant Group '{$this->_strConstantGroupName}'!");
		}
	}
	
	/**
	 * getConstantGroup()
	 *
	 * Retrieves a Constant_Group object by its Name
	 *
	 * @param	string	$strConstantGroupName			Name of the Constant Group
	 *
	 * @return	Constant_Group
	 *
	 * @method
	 */
	public static function getConstantGroup($strConstantGroupName, $bolSilentFail=false)
	{
		if (array_key_exists($strConstantGroupName, $GLOBALS['*arrConstant']))
		{
			return new self($strConstantGroupName);
		}
		elseif ($bolSilentFail)
		{
			return null;
		}
		else
		{
			throw new Exception("Constant Group '{$strConstantGroupName}' is not defined!");
		}
	}

	/**
	 * loadFromTable()
	 *
	 * Loads a Constant Group from a table of the supplied data source (because we use various data sources)
	 *
	 * @param	Data_Source_MDB2_Wrapper	$ds			Data_Source_MDB2_Wrapper or Query object.  If set to NULL then a Query object will be 
	 * 													used, connecting to the default data source for Query objects
	 * @param	string		$strTableName				Name of the Table
	 * @param	boolean		$bolRegisterConstants		TRUE	: Register as PHP constants
	 * 													FALSE	: Don't register as PHP constants (default)
	 * 													NULL	: Register if not already registered (on a constant-by-constant basis)
	 * @param	boollean	$bolSilentFail				Optional, defaults to FALSE
	 * @param	boolean		$bolForceReload				Optional, defaults to FALSE.  If TRUE then the constants will be reloaded from the database
	 * 													If FALSE then it will only load them from the database if they haven't already been loaded.
	 *
	 * @return	Constant_Group
	 * @method
	 */
	public static function loadFromTable($ds, $strTableName, $bolRegisterConstants=false, $bolSilentFail=false, $bolForceReload=false)
	{
		static	$qryQuery;
		if (!isset($qryQuery))
		{
			$qryQuery = new Query();
		}
		if ($ds === NULL)
		{
			$ds = $qryQuery;
		}
		
		if (array_key_exists($strTableName, $GLOBALS['*arrConstant']) && !$bolForceReload)
		{
			if ($bolSilentFail)
			{
				return false;
			}
			else
			{
				throw new Exception("Constant Group '{$strTableName}' is already defined!");
			}
		}
		else
		{
			$strLoadSQL = "SELECT * FROM {$strTableName} WHERE 1";
			if ($ds instanceof Data_Source_MDB2_Wrapper)
			{
				// MDB2 data source
				$mixRecordSet = $ds->queryAll($strLoadSQL, null, MDB2_FETCHMODE_ASSOC);
				if (PEAR::isError($mixRecordSet))
				{
					throw new Exception($mixRecordSet->getMessage());
				}
			}
			elseif ($ds instanceof Query)
			{
				// mysqli data source
				$result	= $ds->Execute($strLoadSQL);
				if ($result === false)
				{
					throw new Exception($ds->Error());
				}
				$mixRecordSet = array();
				
				while ($arrRecord = $result->fetch_assoc())
				{
					$mixRecordSet[] = $arrRecord;
				}
			}
			else
			{
				throw new exception(__METHOD__ ." : Supplied data source object is not of type Data_Source_MDB2_Wrapper or Query");
			}
			
			if (count($mixRecordSet))
			{
				// There is at least 1 record
				// Check that the required fields are present
				$arrRecord		= $mixRecordSet[0];
				$arrFieldList	= array();
				foreach ($arrRecord as $strField=>$mixValue)
				{
					$arrFieldList[strtolower($strField)] = $strField;
				}

				if 	(	!(	array_key_exists('id', $arrFieldList) && 
							array_key_exists('name', $arrFieldList) && 
							array_key_exists('description', $arrFieldList) && 
							array_key_exists('const_name', $arrFieldList)
						)
					)
				{
					// The required fields don't exist
					if ($bolSilentFail)
					{
						return false;
					}
					else
					{
						throw new Exception("'{$strTableName}' is not a Constant Table!");
					}
				}
				
				// It has the required fields
				$strIdField = $arrFieldList['id'];
				$GLOBALS['*arrConstant'][$strTableName]	= array();
				foreach ($mixRecordSet as $arrRecord)
				{
					// It is assumed that the id Field is an integer
					$intId = intval($arrRecord[$strIdField]);
					$GLOBALS['*arrConstant'][$strTableName][$intId]					= array();
					$GLOBALS['*arrConstant'][$strTableName][$intId]['Name']			= $arrRecord[$arrFieldList['name']];
					$GLOBALS['*arrConstant'][$strTableName][$intId]['Description']	= $arrRecord[$arrFieldList['description']];
					$GLOBALS['*arrConstant'][$strTableName][$intId]['Constant']		= $arrRecord[$arrFieldList['const_name']];

					if (defined($GLOBALS['*arrConstant'][$strTableName][$arrRecord[$strIdField]]['Constant']))
					{
						if ($bolRegisterConstants === true)
						{
							if ($bolSilentFail)
							{
								return false;
							}
							else
							{
								throw new Exception("Constant {$GLOBALS['*arrConstant'][$strTableName][$arrRecord[$strIdField]]['Constant']} is already defined!");
							}
						}
					}
					elseif ($bolRegisterConstants === null || $bolRegisterConstants === true)
					{
						define($GLOBALS['*arrConstant'][$strTableName][$arrRecord[$strIdField]]['Constant'], $arrRecord[$strIdField]);
					}
				}
			}
			elseif ($bolSilentFail)
			{
				return false;
			}
			else
			{
				throw new Exception("Table '{$strTableName}' does not have any records!");
			}
		}
		
		return new self($strTableName);
	}

}
?>