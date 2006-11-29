<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_import_unitel_order
//----------------------------------------------------------------------------//
/**
 * module_import_unitel_order
 *
 * Unitel Import Module for the provisioning engine (Daily Order Report)
 *
 * Unitel Import Module for the provisioning engine (Daily Order Report)
 *
 * @file		module_import_unitel_order.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImportUnitelOrder
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImportUnitelOrder
 *
 * Unitel Module for the provisioning engine (Daily Order Report)
 *
 * Unitel Module for the provisioning engine.  (Daily Order Report)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleImportUnitelOrder
 */
 class ProvisioningModuleImportUnitelOrder extends ProvisioningModuleImport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImportUnitelOrder
	 *
	 * Constructor method for ProvisioningModuleImportUnitelOrder
	 *
	 * @return		ProvisioningModuleImportUnitelOrder
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName = "Unitel";
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'PreselectionFileSequence'", "Value");
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceFileSequence'", "Value");
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceRecordSequence'", "Value");
		
				
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 2;
		
		
		// define the carrier input format
		$arrDefine ['RecordType']	['Start']		= 0;
		$arrDefine ['RecordType']	['Length']		= 1;
		
		$arrDefine ['Sequence']		['Start']		= 1;
		$arrDefine ['Sequence']		['Length']		= 5;
		
		$arrDefine ['Description']	['Start']		= 6;
		$arrDefine ['Description']	['Length']		= 9;
		
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
		if($arrLineData['RecordType'] == "T")
		{
			return PRV_TRAILER_RECORD;
		}
		elseif($arrLineData['RecordType'] == "H")
		{
			return PRV_HEADER_RECORD;
		}
		
		// Grab the data we need from the line
		// TODO
		
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
			$arrResult['LineStatus']	= $this->_arrRequest['LineStatus'];
			
			// If we've gained/lost then update the appropriate field
			if ($this->arrLog['Type'] == LINE_ACTION_GAIN)
			{
				$arrResult['GainDate'] = $this->_arrRequest['Date'];
			}
			elseif ($this->arrLog['Type'] == LINE_ACTION_LOSS)
			{
				$arrResult['LossDate'] = $this->_arrRequest['Date'];
			}
			
			// Run the query
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
		$arrData['FNN']	= $this->_arrRequest['ServiceId'];
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
				$arrResult['LineStatus'] = $this->_arrService['LineStatus'];
				
				// Update the Carrier/CarrierPreselect fields if necessary
				if ($this->_arrLog['Type'] == LINE_ACTION_GAIN)
				{
					switch ($this->_arrService['Basket'])
					{
						case BASKET_PRESELECT:
							$arrResult['CarrierPreselect']	= CARRIER_UNITEL;
							break;
						default:
							$arrResult['Carrier']			= CARRIER_UNITEL;
							break;
					}
				}
				
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
