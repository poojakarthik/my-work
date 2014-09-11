<?php

//----------------------------------------------------------------------------//
// MySQLFunction
//----------------------------------------------------------------------------//
/**
 * MySQLFunction
 *
 * For Functions in MySQL
 *
 * Allows the usage of MySQL Functions in Prepared statements
 *
 * Can be used
 *
 *
 * @prefix		fnc
			("MySQL Function")
 *
 * @package		framework
 * @class		MySQLFunction
 */
class MySQLFunction
{

 	//------------------------------------------------------------------------//
	// strFunction
	//------------------------------------------------------------------------//
	/**
	 * strFunction
	 *
	 * The function we wish to pass to MySQL
	 *
	 * The function we wish to pass to MySQL
	 *
	 * @type	<type>
	 *
	 * @property
	 * @see	<MethodName()||typePropertyName>
	 */
	private $_strFunction;
	private $_arrParams;
	private $_arrOrderedParams;

	//------------------------------------------------------------------------//
	// MySQLFunction() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * MySQLFunction()
	 *
	 * Constructor for MySQLFunction object
	 *
	 * Constructor for MySQLFunction object
	 *
	 * @param		string	strFunction		The function we are passing, represented as a string
	 *
	 * @return		void
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */

	function __construct ($strFunction, $arrParams=null)
	{
		$this->_strFunction = $strFunction;
		$this->_arrParams = $arrParams;
	}

	//------------------------------------------------------------------------//
	// getFunction()
	//------------------------------------------------------------------------//
	/**
	 * getFunction()
	 *
	 * Gets the value of the function
	 *
	 * Gets the value of the function
	 *
	 * @return		string							The value of the MySQL Function
	 *
	 * @method
	 * @see			<MethodName()||typePropertyName>
	 */

	public function getFunction ()
	{
		return $this->_strFunction;
	}

	public function getParameters ()
	{
		return $this->_arrParams;
	}

	public function setParameters ($arrParams)
	{
		$this->_arrParams = $arrParams;
	}

	public function Prepare ()
	{
		$strFunction = $this->_strFunction;
		$this->_arrOrderedParams = Statement::FindAlias ($strFunction);

		return $strFunction;
	}

	public function Execute (&$strType, &$arrParams, $arrData)
	{
		foreach ($this->_arrOrderedParams as $mixColumn)
		{
			$strType .= Statement::GetDBInputType ($arrData [$mixColumn]);
			$arrParams [] = $arrData [$mixColumn];
		}
	}

	public function __toString() {
		return 'MySQL Function: ' . $this->_strFunction;
	}
}

?>
