<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_import_unitel_status
//----------------------------------------------------------------------------//
/**
 * module_import_unitel_status
 *
 * Unitel Import Module for the provisioning engine (Preselection)
 *
 * Unitel Import Module for the provisioning engine (Preselection)
 *
 * @file		module_import_unitel_preselection.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImportUnitelPreselection
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImportUnitelPreselection
 *
 * Unitel Module for the provisioning engine (Preselection)
 *
 * Unitel Module for the provisioning engine.  (Preselection)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleImportUnitelPreselection
 */
 class ProvisioningModuleImportUnitelPreselection extends ProvisioningModuleImport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImportUnitelPreselection
	 *
	 * Constructor method for ProvisioningModuleImportUnitelPreselection
	 *
	 * @return		ProvisioningModuleImportUnitelPreselection
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName 					= "Unitel";
		$this->_strDelimiter					= "\t";
		
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
		$arrDefine ['FNN']			['Index']		= 0;
		
		$arrDefine ['Action']		['Index']		= 1;
		
		$arrDefine ['Failed']		['Index']		= 2;	// 0 is pass, 1 is fail

		$arrDefine ['Description']	['Index']		= 3;

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
		if($arrLineData['FNN'] == "RecCount =")
		{
			return PRV_TRAILER_RECORD;
		}
		
		// Grab data for request, service and log updates
		$this->_arrLog['Description']		= $arrLineData['Description'];
		$this->_arrRequest['FNN']			= $arrLineData['FNN'];
		$this->_arrRequest['Status']		= ((int)$arrLineData['Failed'] == 0) ? REQUEST_STATUS_REJECTED : REQUEST_STATUS_COMPLETED;
		$this->_selMatchService->Execute(Array('FNN' => $arrLineData['FNN']));
		if (!$this->_arrRequest['Service']	= $this->_selMatchService->Fetch())
		{
			// This FNN doesn't belong to us
			return PRV_NO_SERVICE;
		}
		
		switch ($arrLineData['Action'])
		{
			case "Bar":
				$this->_arrService['LineStatus']			= LINE_SOFT_BARRED;
				$this->_arrRequest['RequestType']			= REQUEST_BAR_SOFT;
				$this->_arrLog['Type']						= REQUEST_BAR_SOFT;
				break;
				
			case "UnBar":
				$this->_arrService['LineStatus']			= LINE_ACTIVE;
				$this->_arrRequest['RequestType']			= REQUEST_UNBAR_SOFT;
				$this->_arrLog['Type']						= REQUEST_UNBAR_SOFT;
				break;
				
			case "Preselect":
				$this->_arrService['CarrierPreselect']		= CARRIER_UNITEL;
				$this->_arrRequest['RequestType']			= REQUEST_PRESELECTION;
				$this->_arrLog['Type']						= REQUEST_PRESELECTION;
				break;
				
			case "PSReversal":
				$this->_selPreselectCarrier->Execute(Array('FNN' => $arrLineData['FNN']));
				if ($this->_selPreselectCarrier->Fetch() == CARRIER_UNITEL)
				{
					$this->_arrService['CarrierPreselect']	= NULL;
				}
				$this->_arrRequest['RequestType']			= REQUEST_PRESELECTION_REVERSE;
				$this->_arrLog['Type']						= REQUEST_PRESELECTION_REVERSE;
				break;
				
			case "Activate":
				// TODO: Possibly at a later date
				$this->_arrRequest['RequestType']			= REQUEST_ACTIVATION;
				$this->_arrLog['Type']						= REQUEST_ACTIVATION;
				break;
				
			case "Deactivate":
				// TODO: Possibly at a later date
				$this->_arrRequest['RequestType']			= REQUEST_DEACTIVATION;
				$this->_arrLog['Type']						= REQUEST_DEACTIVATION;
				break;
				
			default:
				// Unknown Record Type
				return PRV_BAD_RECORD_TYPE;
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
		
		$this->_arrLog['Service']	= $this->_arrRequest['Service'];
		$this->_arrLog['Date']		= date("Y-m-d");
		
		// Is there a request match?
		if ($arrResult = $this->_selMatchRequest->Fetch())
		{
			// Found a match, so update
			$this->_arrLog['Request']	= $arrResult['Id'];
			$arrResult = array_merge($arrResult, $this->_arrRequest);
			return $this->_ubiRequest->Execute($arrResult);
		}
		else
		{
			// There is no request, but there is a service match
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
