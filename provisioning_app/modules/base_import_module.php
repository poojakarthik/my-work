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
	protected $_arrLog;
	
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
		
		$this->_intCarrier = NULL;
		
		$arrRequestColumns['ExportFile']		= NULL;
		$arrRequestColumns['GainDate']			= NULL;
		$arrRequestColumns['LossDate']			= NULL;
		$arrRequestColumns['Status']			= NULL;
		$arrServiceColumns['LineStatus']	 	= NULL;
		$arrServiceColumns['Carrier']		 	= NULL;
		$arrServiceColumns['CarrierPreselect']	= NULL;
				
		$this->_selMatchRequest					= new StatementSelect("Request", "*",
			"Service = <Service> AND Carrier = <Carrier> AND RequestType = <RequestType> AND Status = 301", "RequestDateTime DESC", "1");
		$this->_ubiRequest						= new StatementUpdateById("Request", $arrRequestColumns);
		$this->_selMatchService 				= new StatementSelect("Service", "*", "FNN = <FNN>", "CreatedOn DESC", "1");
		$this->_ubiService						= new StatementUpdateById("Service", $arrServiceColumns);
		$this->_selMatchLog					= new StatementSelect("ProvisioningLog", "Id", "Date > <Date>");
		$this->_selGetSequence				= new StatementSelect("Config", "Name, Value", "Application = ".APPLICATION_PROVISIONING." AND Module = <Module>");
		$this->_selGetFullServiceRequests	= new StatementSelect("Request JOIN Service ON Request.Service = Service.Id", "Request.*, Service.FNN", "Request.Carrier = <Carrier> AND Request.Status = ".REQUEST_STATUS_WAITING." AND Request.RequestType = ".REQUEST_FULL_SERVICE);
		$this->_selGetPreselectRequests		= new StatementSelect("Request JOIN Service ON Request.Service = Service.Id", "Request.*, Service.FNN", "Request.Carrier = <Carrier> AND Request.Status = ".REQUEST_STATUS_WAITING." AND Request.RequestType = ".REQUEST_PRESELECTION);
		$this->_insAddToLog					= new StatementInsert("ProvisioningLog");
		
	 	$arrColumns = Array();
	 	$arrColumns['Account']		= "Account.Id";
	 	$arrColumns['BusinessName']	= "Account.BusinessName";
	 	$arrColumns['FirstName']	= "Employee.FirstName";
	 	$arrColumns['Email']		= "Employee.Email";
	 	$arrColumns['RequestDate']	= "Request.RequestDateTime";
	 	$arrColumns['Status']		= "Request.Status";
	 	$this->_selEmailReportDetails	= new StatementSelect(	"Request JOIN Employee ON Employee.Id = Request.Employee, " .
	 															"Service JOIN Account ON Service.Account = Account.Id",
	 															$arrColumns,
	 															"Request.Id = <Request>");
		
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
	// AddToProvisioningLog
	//------------------------------------------------------------------------//
	/**
	 * AddToProvisioningLog()
	 *
	 * Adds the record to the log
	 *
	 * Adds the record to the log
	 * 
	 * @return	boolean					
	 *
	 * @method
	 */
	 function AddToProvisioningLog()
	 {
		// If there is an FNN and no Service specified, then attempt to match
		if (isset($this->_arrLog['FNN']) && !isset($this->_arrLog['Service']))
		{
			$this->_selMatchService->Execute(Array('FNN' => trim($this->_arrLog['FNN'])));
			if (!$this->_arrLog['Service'] = $this->_selMatchService->Fetch())
			{
				// This request doesn't belong to us
				Debug("Cannot find the service");
				return FALSE;
			}
		}
		
		// Write to the Provisioning Log
		//$this->_arrLog['Carrier']	= $this->_intCarrier;
		$this->_arrLog['Direction']	= REQUEST_DIRECTION_INCOMING;
		$mixResult =  $this->_insAddToLog->Execute($this->_arrLog);
		if ($mixResult === FALSE)
		{
			Debug($this->_insAddToLog->Error());
		}
		return $mixResult;
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
	 
	//------------------------------------------------------------------------//
	// EmailReport
	//------------------------------------------------------------------------//
	/**
	 * EmailReport()
	 *
	 * Emails a report about the request response to the employee who requested it
	 *
	 * Emails a report about the request response to the employee who requested it
	 *
	 * @return	bool			
	 *
	 * @method
	 */
	 protected function EmailReport()
	 {
	 	// Make sure we've matched to a request
	 	if (!$this->_arrLog['Request'])
	 	{
	 		// We don't need to send an email
	 		return TRUE;
	 	}	 	
	 	
	 	// Get Email Report Details
	 	$arrWhere = Array();
	 	$arrWhere['Request']	= $this->_arrLog['Request'];
	 	if ($this->_selEmailReportDetails->Execute($arrWhere) === FALSE)
	 	{
	 		// ERROR
	 		return FALSE;
	 	}
	 	if (($arrDetails = $this->_selEmailReportDetails->Fetch()) === FALSE)
	 	{
	 		// No match -> Error
	 		return FALSE;
	 	}
	 	
	 	$intCount = 0;
	 	if ($arrDetails['Email'])
	 	{
		 	// Generate and send off the report
		 	$arrVariables	= Array();
		 	$arrVariables['<Employee>']		= $arrDetails['FirstName'];
			$arrVariables['<RequestDate>']	= $arrDetails['RequestDate'];
			$arrVariables['<FNN>']			= $this->_arrLog['FNN'];
			$arrVariables['<Account>']		= $arrDetails['Account'];
			$arrVariables['<BusinessName>']	= $arrDetails['BusinessName'];
			$arrVariables['<ResponseDate>']	= $this->_arrLog['Date'];
			$arrVariables['<RequestType>']	= $this->_arrLog['Type'];
			$arrVariables['<Carrier>']		= $this->_arrLog['Carrier'];
			$arrVariables['<Status>']		= $arrDetails['Status'];
			$arrVariables['<Description>']	= $this->_arrLog['Description'];
		 	$strReport		= ReplaceAliases(REQUEST_EMAIL_MESSAGE, $arrVariables);
		 	$strHeaders 	= "From: automated@voiptelsystems.com.au";
		 	$intCount		=  mail(	$arrDetails['Email'], 
										"Provisioning response for ".$this->_arrLog['FNN']." (Automated Message)",
										$strReport,
										$strHeaders);
	 	}
			
		// Send an email to the admins, too
		if (REQUEST_EMAIL_ADMIN && !DEBUG_MODE)
		{
		 	$intCount		+=  mail(	REQUEST_EMAIL_ADMIN, 
										"Provisioning response for ".$this->_arrLog['FNN']." (Automated Message)",
										$strReport,
										$strHeaders);
		}
	 	
	 	return (bool)$intCount;
	 }
 }
?>
