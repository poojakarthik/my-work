<?php

//----------------------------------------------------------------------------//
// DBObject
//----------------------------------------------------------------------------//
/**
 * DBObject
 *
 * Database Object
 *
 * Database Object
 *
 *
 * @prefix	dbo
 *
 * @package	framework_ui
 * @class	DBObject
 * @extends	DBObjectBase
 */
class DBObject extends DBObjectBase
{
	public $_intMode 		= DBO_RETURN;
	public $_strIdColumn 	= 'Id';
	public $_arrColumns 	= Array();
	public $_arrTables		= Array();
	public $_strName		= '';
	public $_arrResult		= Array();
	public $_arrRequest		= Array();
	public $_arrValid		= Array();
	public $_arrProperty	= Array();
	public $_intStatus		= 0;
	public $_arrOptions		= Array();
	public $_db				= NULL;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Construct a new Database Object
	 *
	 * construct a new Database Object
	 *
	 * @param	string	$strName					Name of the object to create
	 * @param	mixed	$mixTable		optional	Database table to connect the data object to 
	 * @param	mixed	$mixColumns		optional	Columns to include in the data object
	 * 
	 * @return	DBObject
	 *
	 * @method
	 */
	function __construct($strName, $mixTable=NULL, $mixColumns=NULL)
	{
		// set name
		$this->_strName = $strName;
		
		// get config
		$this->_arrOptions = Config()->Get('Dbo', $strName);
		
		// set table
		if (is_array($mixTable))
		{
			$this->_arrTables = $mixTable;
		}
		elseif ($this->_arrOptions['Table'])
		{
			$this->_arrTables = $this->_arrOptions['Table'];
		}
		elseif($mixTable)
		{
			//TODO!!!! convert table names into an array
		}
		else
		{
			// as a last resort use the dbo name as the table name
			$this->_arrTables[$strName]['Table'] = $strName;
		}

		// set columns
		if (is_array($mixColumns))
		{
			$this->_arrColumns = $mixColumns;
		}
		elseif ($mixColumns)
		{
			//TODO!!!! convert column names into an array
		}
		elseif ($this->_arrOptions['Columns'])
		{
			$this->_arrColumns = $this->_arrOptions['Columns'];
		}
		else
		{
			//TODO!!!! get * column names for tables
		}
		
		// set ID column name
		//TODO!!!! look harder to find this
		if ($this->_arrOptions['IdColumn'])
		{
			$this->_strIdColumn = $this->_arrOptions['IdColumn'];
		}
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strName	<description>
	 * @param	array	$arrArguments	<description>
	 * @return	<type>
	 *
	 * @method
	 */
	function __call($strName, $arrArguments)
	{
		return PropertyToken()->Method($this, $strName, $arrArguments);
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	string	$strName	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function __get($strName)
	{
		return PropertyToken()->Property($this, $strName);
	}
	
	//------------------------------------------------------------------------//
	// Clean
	//------------------------------------------------------------------------//
	/**
	 * Clean()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function Clean()
	{
		$this->dboObject->_arrProperty 	= Array();
		$this->dboObject->_arrRequest	= Array();
		$this->dboObject->_arrResult	= Array();
		$this->dboObject->_arrValid 	= Array();
		$_intStatus						= 0;
	}
	
	//------------------------------------------------------------------------//
	// SetMode
	//------------------------------------------------------------------------//
	/**
	 * SetMode()
	 *
	 * <short description>
	 *
	 * <long description>
	 *
	 * @param	integer	$intMode	<description>
	 * @param	array	$arrOptions	[optional] <description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	function SetMode($intMode, $arrOptions=NULL)
	{
		$this->_intMode 				= (int)$intMode;
		$this->_arrOptions['Output']	= $arrOptions;
	}
	

}


?>