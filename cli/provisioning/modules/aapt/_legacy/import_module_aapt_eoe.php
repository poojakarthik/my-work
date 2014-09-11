<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_import_aapt_eoe
//----------------------------------------------------------------------------//
/**
 * module_import_aapt_eoe
 *
 * AAPT Import Module for the provisioning engine (EOE)
 *
 * AAPT Import Module for the provisioning engine (EOE)
 *
 * @file		module_import_aapt_eoe.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImportAAPTEOE
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImportAAPTEOE
 *
 * AAPT Module for the provisioning engine (EOE)
 *
 * AAPT Module for the provisioning engine.  (EOE)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleImportAAPTEOE
 */
 class ProvisioningModuleImportAAPTEOE extends ProvisioningModuleImport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImportAAPTEOE
	 *
	 * Constructor method for ProvisioningModuleImportAAPTEOE
	 *
	 * @return		ProvisioningModuleImportAAPTEOE
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
		$arrDefine ['ReturnCode']			['Start']	= 0;
		$arrDefine ['ReturnCode']			['Length']	= 1;
		
		$arrDefine ['ActionCode']			['Start']	= 1;
		$arrDefine ['ActionCode']			['Length']	= 1;
		
		$arrDefine ['FNN']					['Start']	= 2;
		$arrDefine ['FNN']					['Length']	= 30;

		$arrDefine ['CustomerRefNumber']	['Start']	= 32;
		$arrDefine ['CustomerRefNumber']	['Length']	= 12;
		
		$arrDefine ['ServiceTypeCode']		['Start']	= 44;
		$arrDefine ['ServiceTypeCode']		['Length']	= 1;
		
		$arrDefine ['ApplicationDate']		['Start']	= 45;
		$arrDefine ['ApplicationDate']		['Length']	= 8;
		
		$arrDefine ['Description']			['Start']	= 53;
		$arrDefine ['Description']			['Length']	= 30;
		
		$arrDefine ['RemoteLocation']		['Start']	= 83;	// Always "Y"
		$arrDefine ['RemoteLocation']		['Length']	= 1;
		
		$arrDefine ['WorkSpecFlag']			['Start']	= 84;	// Always "N"
		$arrDefine ['WorkSpecFlag']			['Length']	= 1;
		
		$arrDefine ['UserSurname']			['Start']	= 85;
		$arrDefine ['UserSurname']			['Length']	= 40;
		
		$arrDefine ['UserFirstName']		['Start']	= 125;
		$arrDefine ['UserFirstName']		['Length']	= 20;
		
		$arrDefine ['UserMiddleInitial']	['Start']	= 145;
		$arrDefine ['UserMiddleInitial']	['Length']	= 1;
		
		$arrDefine ['StreetName']			['Start']	= 146;
		$arrDefine ['StreetName']			['Length']	= 40;
		
		$arrDefine ['Suburb']				['Start']	= 186;
		$arrDefine ['Suburb']				['Length']	= 40;
		
		$arrDefine ['UserCity']				['Start']	= 226;
		$arrDefine ['UserCity']				['Length']	= 25;
		
		$arrDefine ['UserState']			['Start']	= 251;
		$arrDefine ['UserState']			['Length']	= 3;
		
		$arrDefine ['UserPostcode']			['Start']	= 254;
		$arrDefine ['UserPostcode']			['Length']	= 4;
		
		$arrDefine ['CustomerTitle']		['Start']	= 258;
		$arrDefine ['CustomerTitle']		['Length']	= 4;
		
		$arrDefine ['AttentionName']		['Start']	= 262;
		$arrDefine ['AttentionName']		['Length']	= 20;
		
		$arrDefine ['AttentionPosition']	['Start']	= 282;
		$arrDefine ['AttentionPosition']	['Length']	= 20;
		
		$arrDefine ['CountryCode']			['Start']	= 302;	// Always 0000
		$arrDefine ['CountryCode']			['Length']	= 4;
		
		$arrDefine ['TelephoneNumber']		['Start']	= 306;
		$arrDefine ['TelephoneNumber']		['Length']	= 30;
		
		$arrDefine ['FaxCountryCode']		['Start']	= 336;	// Always 000
		$arrDefine ['FaxCountryCode']		['Length']	= 4;
		
		$arrDefine ['FaxNumber']			['Start']	= 340;
		$arrDefine ['FaxNumber']			['Length']	= 30;
		
		$arrDefine ['FNNTerminationDate']	['Start']	= 370;
		$arrDefine ['FNNTerminationDate']	['Length']	= 8;
		
		$arrDefine ['ScopeOfBusiness']		['Start']	= 378;	// Always "I/N"
		$arrDefine ['ScopeOfBusiness']		['Length']	= 3;
		
		$arrDefine ['Filler']				['Start']	= 381;	// Unused
		$arrDefine ['Filler']				['Length']	= 95;
		
		$arrDefine ['FieldInError1']		['Start']	= 476;
		$arrDefine ['FieldInError1']		['Length']	= 3;
		
		$arrDefine ['ErrorCode1']			['Start']	= 479;
		$arrDefine ['ErrorCode1']			['Length']	= 5;
		
		$arrDefine ['FieldInError2']		['Start']	= 484;
		$arrDefine ['FieldInError2']		['Length']	= 3;
		
		$arrDefine ['ErrorCode2']			['Start']	= 487;
		$arrDefine ['ErrorCode2']			['Length']	= 5;
		
		$arrDefine ['FieldInError3']		['Start']	= 492;
		$arrDefine ['FieldInError3']		['Length']	= 3;
		
		$arrDefine ['ErrorCode3']			['Start']	= 495;
		$arrDefine ['ErrorCode3']			['Length']	= 5;

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
		if(substr($strLine, 0, 11) == "CPN HEADER ")
		{
			// TODO: Is there anything useful in here?
			return PRV_HEADER_RECORD;
		}
		elseif(substr($strLine, 0, 11) == "CPN TRAILER")
		{
			// TODO: Is there anything useful in here?
			return PRV_TRAILER_RECORD;
		}
		
		$this->_selMatchService->Execute(Array('FNN' => $arrLineData['FNN']));
		$this->_arrRequest['Service']			= $this->_selMatchService->Fetch();
		$this->_arrRequest['FNN']				= $arrLineData['FNN'];
		$this->_arrRequest['Status']			= REQUEST_STATUS_COMPLETED;
		$this->_arrService['CarrierPreselect']	= CARRIER_AAPT;
		$this->_arrRequest['RequestType']		= REQUEST_PRESELECTION;
		$this->_arrRequest['GainDate'] 			= $arrLineData['CreatedOn'];
		
		// Has this line been terminated?
		if ($arrLineData['TerminatedOn'] != "01/01/0001")
		{
			$this->_arrRequest['LossDate'] = $arrLineData['TerminatedOn'];
			$this->_arrService['CarrierPreselect']	= NULL;
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
