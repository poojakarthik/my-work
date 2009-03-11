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
		if (array_key_exists($mixIndex, $GLOBALS['**arrConstant'][$this->_strConstantGroupName]))
		{
			if (array_key_exists('Name', $GLOBALS['**arrConstant'][$this->_strConstantGroupName][$mixIndex]))
			{
				return $GLOBALS['**arrConstant'][$this->_strConstantGroupName][$mixIndex]['Name'];
			}
			else
			{
				return $GLOBALS['**arrConstant'][$this->_strConstantGroupName][$mixIndex]['Description'];
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
		if (array_key_exists($mixIndex, $GLOBALS['**arrConstant'][$this->_strConstantGroupName]))
		{
			return $GLOBALS['**arrConstant'][$this->_strConstantGroupName][$mixIndex]['Description'];
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
		if (array_key_exists($mixIndex, $GLOBALS['**arrConstant'][$this->_strConstantGroupName]))
		{
			return $GLOBALS['**arrConstant'][$this->_strConstantGroupName][$mixIndex]['Constant'];
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
	public static function getConstantGroup($strConstantGroupName)
	{
		if (array_key_exists($strConstantGroupName, $GLOBALS['**arrConstant']))
		{
			return new self($strConstantGroupName);
		}
		else
		{
			throw new Exception("Constant Group '{$strConstantGroupName}' is not defined!");
		}
	}
}
?>