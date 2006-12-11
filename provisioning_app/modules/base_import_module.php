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
	 
	//------------------------------------------------------------------------//
	// _GetErrorDescription
	//------------------------------------------------------------------------//
	/**
	 * _GetErrorDescription()
	 *
	 * Gets a description for an error code
	 *
	 * Returns a string desription of an error, based on the error code passed.
	 * Assumes error code meets "Australian Communications Industry Forum,
	 * Industry Code - Pre-Selection - Single Basket/Multi Service Deliverer,
	 * ACIF C515 June 1999 (the Code)" standards.
	 * 
	 * 
	 * @param	mixed	mixRejectCode		Error code to evaluate
	 *
	 * @return	string						Error description				
	 *
	 * @method
	 */
	 protected function _GetErrorDescription($mixRejectCode)
	 {
		// TODO~~~~~~~
		
		// Check against known error codes
		switch ((int)$mixRejectCode)
		{
			case 1:
				// Service number not found
				break;
			case 2:
				// Service number on diversion
				break;
			case 3:
				// Inactive Service
				break;
			case 4:
				// Disconnected Service
				break;
			case 5:
				// Incompatable Service
				break;
			case 6:
				// Enhanced Service found
				break;
			case 7:
				// Real-Time Metering found
				break;
			case 8:
				// Entire number block not present in Churn Notification Order file
				break;
			case 9:
				// Preselection already enabled
				break;
			case 10:
				// Service ported to another ASD
				break;
			case 11:
				// Requested service is owned by the ASD
				break;
			case 12:
				// Restricted access service
				break;
			case 13:
				// Point of presence not valid
				break;
			case 14:
				// Enhanced Service - ISDN
				break;
			case 16:
				// Incorrect ASD nominated
				break;
			case 21:
				// Reversal Error (Cannot find old PSD)
				break;
			case 25:
				// Indial Service
				break;
			case 26:
				// Incorrect PSD code submitted
				break;
			case 31:
				// Outside Allowable Timeframe
				break;
			case 40:
				// Dual Notification Same Day - Different PSD
				break;
			case 48:
				// Reversal Rejected Subsequent Churn
				break;
			default:
				// Unkown Error Code
				$strErrorDescription = "Unknown Error";
				break;
		}
		
		return $strErrorDescription;
	 }
	 
 }
?>
