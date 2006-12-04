<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// base_import_module
//----------------------------------------------------------------------------//
/**
 * base_import_module
 *
 * Import Module for the provisioning engine
 *
 * Import Module for the provisioning engine.  There is one per file type.
 *
 * @file		base_import_module.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImport
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImport
 *
 * Import Module for the provisioning engine
 *
 * Import Module for the provisioning engine.  There is one per file type.
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleImport
 */
 abstract class ProvisioningModuleImport
 {
	protected $_arrData;
	protected $_arrDefineInput;
	
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImport
	 *
	 * Constructor method for ProvisioningModuleImport
	 *
	 * @return		ProvisioningModuleImport
	 *
	 * @method
	 */
 	function __construct($ptrDB)
 	{
		// Set up this->db
		$this->db = $ptrDB;
		
		$this->_selMatchRequest				= new StatementSelect("Request", "*",
			"Service = <Service> AND Carrier = <Carrier> AND RequestType = <RequestType>", "RequestDate DESC", "1");
		$this->_ubiRequest					= new StatementUpdateById("Request");
		$this->_selMatchService 			= new StatementSelect("Service", "*", "FNN = <FNN>", "CreatedOn DESC", "1");
		$this->_ubiService					= new StatementUpdateById("Service");
		$this->_selMatchLog					= new StatementSelect("ProvisioningLog", "Id", "Date > <Date>");
		$this->_selGetSequence				= new StatementSelect("Config", "Name, Value", "Application = ".APPLICATION_PROVISIONING." AND Module = <Module>");
		$this->_selGetFullServiceRequests	= new StatementSelect("Request JOIN Service ON Request.Service = Service.Id", "Request.*, Service.FNN", "Request.Carrier = <Carrier> AND Request.Status = ".REQUEST_STATUS_WAITING." AND Request.RequestType = ".REQUEST_FULL_SERVICE);
		$this->_selGetPreselectRequests		= new StatementSelect("Request JOIN Service ON Request.Service = Service.Id", "Request.*, Service.FNN", "Request.Carrier = <Carrier> AND Request.Status = ".REQUEST_STATUS_WAITING." AND Request.RequestType = ".REQUEST_PRESELECTION);
		
		// Default delimeter is NULL (fixedwidth)
		$this->_strDelimiter	= NULL;
 	}
 	
 	//------------------------------------------------------------------------//
	// Normalise()
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises a line
	 *
	 * Normalises a line, and sets it as the "current" line
	 *
	 * @return		mixed				TRUE: pass
	 * 									int	: Error code
	 *
	 * @method
	 */
 	abstract function Normalise($strLine);
 	
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
	// AddToLog
	//------------------------------------------------------------------------//
	/**
	 * AddToLog()
	 *
	 * Adds the record to the log
	 *
	 * Adds the record to the log
	 * 
	 * @return	boolean					
	 *
	 * @method
	 */
	 function AddToLog()
	 {
		// TODO
		
		return true;
	 }

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
				$_arrData[$strKey] = trim($_arrData[$strKey]);
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
