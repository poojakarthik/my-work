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
	 * Loads a Constant Group from a database table
	 *
	 * @param	string		$strTableName				Name of the Table
	 * @param	[boolean	$bolRegisterConstants	]	TRUE	: Register as PHP constants
	 * 													FALSE	: Don't register as PHP constants (default)
	 * 													NULL	: Register if not already registered (on a constant-by-constant basis)
	 * @param	boollean	$bolSilentFail				Optional, defaults to FALSE
	 * @param	boolean		$bolForceReload				Optional, defaults to FALSE.  If TRUE then the constants will be reloaded from the database
	 * 													If FALSE then it will only load them from the database if they haven't already been loaded.
	 *
	 * @return	Constant_Group
	 *
	 * @method
	 */
	public static function loadFromTable($strTableName, $bolRegisterConstants=false, $bolSilentFail=false, $bolForceReload=false)
	{
		static	$qryQuery;
		$qryQuery	= ($qryQuery) ? $qryQuery : new Query();
		
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
			$strLoadSQL	= "SELECT * FROM `{$strTableName}` WHERE 1";
			$resLoad	= $qryQuery->Execute($strLoadSQL);
			if ($resLoad === false)
			{
				throw new Exception($qryQuery->Error());
			}
			elseif ($resLoad->num_rows)
			{
				$arrFields		= $resLoad->fetch_fields();
				$arrFieldList	= array();
				foreach ($arrFields as $objField)
				{
					$arrFieldList[strtolower($objField->name)]	= $objField->name;
				}
				
				$strIdField	= (in_array('id', $arrFieldList)) ? 'id' : ((in_array('Id', $arrFieldList)) ? 'Id' : false);
				if ($strIdField !== false && array_key_exists('const_name', $arrFieldList) && array_key_exists('name', $arrFieldList) && array_key_exists('description', $arrFieldList))
				{
					// Has the required fields
					$GLOBALS['*arrConstant'][$strTableName]	= array();
					while ($arrRecord = $resLoad->fetch_assoc())
					{
						$GLOBALS['*arrConstant'][$strTableName][$arrRecord[$strIdField]]				= array();
						$GLOBALS['*arrConstant'][$strTableName][$arrRecord[$strIdField]]['Name']		= $arrRecord[$arrFieldList['name']];
						$GLOBALS['*arrConstant'][$strTableName][$arrRecord[$strIdField]]['Description']	= $arrRecord[$arrFieldList['description']];
						$GLOBALS['*arrConstant'][$strTableName][$arrRecord[$strIdField]]['Constant']	= $arrRecord[$arrFieldList['const_name']];

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
					throw new Exception("'{$strTableName}' is not a Constant Table!");
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