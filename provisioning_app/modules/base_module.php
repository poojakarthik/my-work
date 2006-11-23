<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// base_module
//----------------------------------------------------------------------------//
/**
 * base_module
 *
 * Module for the provisioning engine
 *
 * Module for the provisioning engine.  There is one per carrier.
 *
 * @file		base_module.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModule
//----------------------------------------------------------------------------//
/**
 * ProvisioningModule
 *
 * Module for the provisioning engine
 *
 * Module for the provisioning engine.  There is one per carrier.
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModule
 */
 abstract class ProvisioningModule
 {
	protected $_arrData;
	protected $_arrDefineInput;
	
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModule
	 *
	 * Constructor method for ProvisioningModule
	 *
	 * @return		ProvisioningModule
	 *
	 * @method
	 */
 	function __construct($ptrDB)
 	{
		// Set up this->db
		$this->db = $ptrDB;
		
		// Default delimeter is NULL (fixedwidth)
		$this->_strDelimiter;
 	}
 	
 	//------------------------------------------------------------------------//
	// Add()
	//------------------------------------------------------------------------//
	/**
	 * Add()
	 *
	 * Adds a line to the module from a file
	 *
	 * Parses and adds a "line" to the module from a line status file.
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function Add($strLine);
 	
 	//------------------------------------------------------------------------//
	// NextLine()
	//------------------------------------------------------------------------//
	/**
	 * NextLine()
	 *
	 * Advances to next "line" in the module
	 *
	 * Advances to next "line" in the module
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	function NextLine()
 	{
		return next($this->_arrLines);
 	}

 	//------------------------------------------------------------------------//
	// NewFile()
	//------------------------------------------------------------------------//
	/**
	 * NewFile()
	 *
	 * Clears the FileData array
	 *
	 * Clears the FileData array
	 *
	 * @method
	 */
 	function NewFile()
 	{
		$this->_arrData = Array();
 	}

 	//------------------------------------------------------------------------//
	// UpdateRequests()
	//------------------------------------------------------------------------//
	/**
	 * UpdateRequests()
	 *
	 * Updates the Request table
	 *
	 * Updates the Request table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function UpdateRequests();
 	
 	//------------------------------------------------------------------------//
	// UpdateService()
	//------------------------------------------------------------------------//
	/**
	 * UpdateService()
	 *
	 * Updates the Service table
	 *
	 * Updates the Service table based on the data
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function UpdateService();
 
 
  	//------------------------------------------------------------------------//
	// BuildRequest()
	//------------------------------------------------------------------------//
	/**
	 * BuildRequest()
	 *
	 * Builds a request file
	 *
	 * Builds a request file to be sent off, based on info from the DB
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function BuildRequest();
 	
  	//------------------------------------------------------------------------//
	// SendRequest()
	//------------------------------------------------------------------------//
	/**
	 * SendRequest()
	 *
	 * Sends the current request
	 *
	 * Sends the current request
	 *
	 * @return		boolean
	 *
	 * @method
	 */
 	abstract function SendRequest();	 	


	//------------------------------------------------------------------------//
	// _SplitLine
	//------------------------------------------------------------------------//
	/**
	 * _SplitLine()
	 *
	 * Split a Line into an array
	 *
	 * Split a Line into an array
	 * 
	 * @param	string		strLine		Line to split
	 *
	 * @return	array					Split data					
	 *
	 * @method
	 */
	 protected function _SplitLine($strLine)
	 {
		// build the array
	 	if ($this->_strDelimiter)
		{
			// delimited record
			$arrRawData = explode($this->_strDelimiter, rtrim($strLine, "\n"));
			foreach($this->_arrDefineInput as $strKey=>$strValue)
			{
				$_arrData[$strKey] = $arrRawData[$strValue['Index']];
				// delimited fields may have fixed width contents
				if (isset($strValue['Start']) && $strValue['Length'])
				{
					$_arrData[$strKey] = substr($_arrData[$strKey], $strValue['Start'], $strValue['Length']);
				}
			}
		}
		else
		{
			// fixed width record
			foreach($this->_arrDefineInput as $strKey=>$strValue)
			{
				$_arrData[$strKey] = trim(substr($strLine, $strValue['Start'], $strValue['Length']));
			}
		}
		
		return $_arrData;
	 }
 }
?>
