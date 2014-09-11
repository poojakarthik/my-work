<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_import_aapt_reject
//----------------------------------------------------------------------------//
/**
 * module_import_aapt_reject
 *
 * AAPT Import Module for the provisioning engine (Reject)
 *
 * AAPT Import Module for the provisioning engine (Reject)
 *
 * @file		module_import_aapt_reject.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImportAAPTReject
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImportAAPTReject
 *
 * Unitel Module for the provisioning engine (Reject)
 *
 * Unitel Module for the provisioning engine.  (Reject)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleImportAAPTReject
 */
 class ProvisioningModuleImportAAPTReject extends ProvisioningModuleImport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImportAAPTReject
	 *
	 * Constructor method for ProvisioningModuleImportAAPTReject
	 *
	 * @return		ProvisioningModuleImportAAPTReject
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName 					= "AAPT";
		$this->_strDelimiter					= "\t";
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'PreselectionFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceRecordSequence'", Array('Value' => NULL));
		$this->_selPreselectCarrier				= new StatementSelect("Service", "CarrierPreselect", "FNN = <FNN>", "Date DESC", "1");
		
				
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 2;
		
		
		// define the carrier input format
		$arrDefine ['Reason']				['Index'] = 0;
		
		$arrDefine ['FNN']					['Index'] = 1;
		
		$arrDefine ['Carrier']				['Index'] = 2;

		$arrDefine ['RebillInd']			['Index'] = 3;	// Unknown Field - no description given
		
		$arrDefine ['CustomerNumber']		['Index'] = 4;
		
		$arrDefine ['GroupCustomerNumber']	['Index'] = 5;
		
		$arrDefine ['RtType']				['Index'] = 6;	// Used by AAPT only
		
		$arrDefine ['SalesEmployee1']		['Index'] = 7;
		
		$arrDefine ['SalesEmployee2']		['Index'] = 8;
		
		$arrDefine ['ChurnSeqNo']			['Index'] = 9;
		
		$arrDefine ['DateLineSent']			['Index'] = 10;
		
		$arrDefine ['ReturnDate']			['Index'] = 11;
		
		$arrDefine ['ApplicationDate']		['Index'] = 12;
		
		$arrDefine ['TerminationDate']		['Index'] = 13;
		
		$arrDefine ['LastTollDate']			['Index'] = 14;
		
		$arrDefine ['ManagementChannel']	['Index'] = 15;	// Used by AAPT only

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
		if($arrLineData['CustomerNumber'] == "CUST NBR")
		{
			return PRV_HEADER_RECORD;
		}
		elseif(($arrLineData['CustomerNumber'] == "") || (!$arrLineData['CustomerNumber']))
		{
			return PRV_TRAILER_RECORD;
		}
		
		// TODO: WAITING FOR FILE FROM AAPT
		
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
		$arrData['Carrier']		= CARRIER_AAPT;
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
