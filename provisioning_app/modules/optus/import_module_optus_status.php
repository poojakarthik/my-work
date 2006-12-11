
<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_import_optus_status
//----------------------------------------------------------------------------//
/**
 * module_import_optus_status
 *
 * Optus Import Module for the provisioning engine (Status)
 *
 * Optus Import Module for the provisioning engine (Status)
 *
 * @file		module_import_optus_status.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.12
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImportOptusStatus
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImportOptusStatus
 *
 * Optus Module for the provisioning engine (Status)
 *
 * Optus Module for the provisioning engine.  (Status)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleImportOptusStatus
 */
 class ProvisioningModuleImportOptusStatus extends ProvisioningModuleImport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImportOptusStatus
	 *
	 * Constructor method for ProvisioningModuleImportOptusStatus
	 *
	 * @return		ProvisioningModuleImportOptusStatus
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName 					= "Optus";
		$this->_strDelimiter					= ",";
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'PreselectionFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceRecordSequence'", Array('Value' => NULL));
		$this->_selPreselectCarrier				= new StatementSelect("Service", "CarrierPreselect", "FNN = <FNN>", "CreatedOn DESC", "1");
		
				
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 1;
		
		
		// define the carrier input format
		$arrDefine ['ReportDate']		['Index']		= 0;	// YYYYMMDD
		$arrDefine ['CorpNo']			['Index']		= 1;
		$arrDefine ['AccountNo']		['Index']		= 2;
		$arrDefine ['FNN']				['Index']		= 3;	// "07 33531912" format
		$arrDefine ['ASDCode']			['Index']		= 4;	// Full Service Carrier ???
		$arrDefine ['CarrierCode']		['Index']		= 5;	// Preselection Carrier
		$arrDefine ['CarrierName']		['Index']		= 6;	// Name of Preselection Carrier
		$arrDefine ['ChoiceDate']		['Index']		= 7;	// Date entered into system
		$arrDefine ['ConfirmDate']		['Index']		= 8;	// Date preselection was accepted/rejected
		$arrDefine ['Status']			['Index']		= 9;	// Status of the line
		$arrDefine ['RejectCode']		['Index']		= 10;	// Reject Error code
		$arrDefine ['EndDate']			['Index']		= 11;	// END DATE???
		$arrDefine ['LossCode']			['Index']		= 12;	// If the line was lost, how was it lost?
		$arrDefine ['LossRptPsd']		['Index']		= 13;	// ???
		$arrDefine ['NewFNN']			['Index']		= 14;	// If the line has changed FNNs, this is the new number
		

		$this->_arrDefineInput = $arrDefine;
		
		//##----------------------------------------------------------------##//
		
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
 	function Normalise($strLine)
	{
		// Split the line
		$arrLineData = $this->_SplitLine($strLine);
		
		// Ignore header and trailer line
		if($arrLineData['ReportDate'] == "")
		{
			return PRV_TRAILER_RECORD;
		}
		
		// Select the correct FNN
		if ($arrLineData['NewFNN'])
		{
			$this->_arrRequest['FNN']	= str_replace(" ", "", $arrLineData['NewFNN']);
		}
		else
		{
			$this->_arrRequest['FNN']	= str_replace(" ", "", $arrLineData['FNN']);
		}
		
		switch (strtoupper($arrLineData['Status']))
		{
			case "SUCCESSFUL":
				// We've gained a service
				$this->_arrRequest['GainDate']		= $this->_ConvertDate($arrLineData['ConfirmDate']);
				$this->_arrRequest['Status']		= REQUEST_STATUS_COMPLETED;
				$this->_arrService['LineStatus']	= LINE_ACTIVE;
				break;
			case "PENDING":
				// Service activation is pending
				$this->_arrService['LineStatus']	= LINE_PENDING;
				$this->_arrRequest['Status']		= REQUEST_STATUS_PENDING;
				break;
			case "":
				// Line activated, but not churned to Optus, and no churn history
				// TODO: What to do?
				break;
			default:
				// The request has been rejected
				
				// Get the error description
				$this->_arrLog['Description'] = $this->_GetErrorDescription($arrLineData['RejectCode']);
				break;
		}

		
		return TRUE;
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
 	function UpdateRequests()
	{
		// Try to match a request
		$arrData['Service']		= $this->_arrRequest['Service'];
		$arrData['RequestType']	= $this->_arrRequest['RequestType'];
		$arrData['Carrier']		= CARRIER_UNITEL;
		$this->_selMatchRequest->Execute();
		
		// Is there a request match?
		if ($arrResult = $this->_selMatchRequest->Fetch())
		{
			// Found a match, so update
			$arrResult = array_merge($arrResult, $this->_arrRequest);
			return $this->_ubiRequest->Execute($arrResult);
		}
		
		// There is no match, so return TRUE
		return TRUE;
	}
 	
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
 	function UpdateService()
	{
		$arrData['FNN']	= $this->_arrRequest['FNN'];
		$this->_selMatchService->Execute($arrData);
		
		// Match to an entry in the Service table
		if($arrResult = $this->_selMatchService->Fetch())
		{
			// Make sure our status is up to date
			$arrData = Array('Date' => $this->_arrRequest['Date']);
			$this->_selMatchLog->Execute($arrData);
			
			// If this is the most up to date status
			if (!$this->_selMatchLog->Fetch())
			{
				// Actually update the service
				$arrResult = array_merge($arrResult, $this->_arrService);
				
				// <DEBUG>
				// A hack to get around the fact that next to no services have a Line Status atm
				if (!$arrResult['LineStatus'])
				{
					$arrResult['LineStatus'] = LINE_ACTIVE;
				}
				// </DEBUG>

				// Run the query
				if($this->_ubiService->Execute($arrResult) === FALSE)
				{
					return FALSE;
				}
				else
				{
					return TRUE;
				}
			}
			else
			{
				// Our status is old, so lets just return TRUE
				return TRUE;
			}
		}
		else
		{
			// We have received a status for a status that doesn't belong to us
			return PRV_NO_SERVICE;
		}
	}
 
 	//------------------------------------------------------------------------//
	// _ConvertDate()
	//------------------------------------------------------------------------//
	/**
	 * _ConvertDate()
	 *
	 * Converts from Unitel to Internal date format
	 *
	 * Converts from YYYYMMDD to YYYY-MM-DD format
	 *
	 * @param		string		$strDate		Date to convert
	 *
	 * @return		string
	 *
	 * @method
	 */
 	function _ConvertDate($strDate)
	{
		$strReturn = substr($strDate, 0, 4)."-".substr($strDate, 3, 2)."-".substr($strDate, 5, 2);
		return $strReturn;
	} 

	//------------------------------------------------------------------------//
	// _GetCarrierName()
	//------------------------------------------------------------------------//
	/**
	 * _GetCarrierName()
	 *
	 * Gets the name of a carrier from a carrier code
	 *
	 * Gets the name of a carrier from a carrier code
	 *
	 * @param		string		$strCode		Code to match
	 *
	 * @return		string
	 *
	 * @method
	 */
 	function _GetCarrierName($strCode)
	{
		// TODO: waiting for codes from Scott
		return "Undefined Carrier (Internal Code: ".$strCode.")";
	} 
	
 }

?>
