<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_unitel
//----------------------------------------------------------------------------//
/**
 * module_unitel
 *
 * Unitel Module for the provisioning engine
 *
 * Unitel Module for the provisioning engine
 *
 * @file		module_unitel.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleUnitel
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleUnitel
 *
 * Unitel Module for the provisioning engine
 *
 * Unitel Module for the provisioning engine.  There is one per carrier.
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleUnitel
 */
 class ProvisioningModuleUnitel extends ProvisioningModule
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleUnitel
	 *
	 * Constructor method for ProvisioningModuleUnitel
	 *
	 * @return		ProvisioningModuleUnitel
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		parent::__construct($ptrDB);
		
				
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
		
		$arrDefine ['OrderId']		['Start']		= 6;
		$arrDefine ['OrderId']		['Length']		= 9;

		$arrDefine ['OrderType']	['Start']		= 15;
		$arrDefine ['OrderType']	['Length']		= 2;
		
		$arrDefine ['OrderDate']	['Start']		= 17;
		$arrDefine ['OrderDate']	['Length']		= 8;
		
		$arrDefine ['ServiceNo']	['Start']		= 25;
		$arrDefine ['ServiceNo']	['Length']		= 29;
		
		$arrDefine ['Basket']		['Start']		= 54;
		$arrDefine ['Basket']		['Length']		= 3;
		
		$arrDefine ['EffectiveDate']['Start']		= 57;
		$arrDefine ['EffectiveDate']['Length']		= 8;
		
		$arrDefine ['NewNo']		['Start']		= 65;
		$arrDefine ['NewNo']		['Length']		= 29;
		
		$arrDefine ['ReasonCode']	['Start']		= 94;
		$arrDefine ['ReasonCode']	['Length']		= 3;
		
		$arrDefine ['LostTo']		['Start']		= 97;
		$arrDefine ['LostTo']		['Length']		= 3;
		
		$arrDefine ['RSLReference']	['Start']		= 100;
		$arrDefine ['RSLReference']	['Length']		= 9;					
		
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
	 * @return		int				Error/Success Code
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
		
		// ServiceId
		$arrRequestData	['ServiceId']	= $this->_GetServiceId(RemoveAusCode($arrLineData['ServiceNo']));
		$arrLogData		['ServiceId']	= $arrRequestData['ServiceId'];
		
		// Date
		$arrRequestData	['Date']		= $this->_ConvertDate($arrLineData['EffectiveDate']);
		$arrLogData		['Date']		= date("Y-m-d");
		
		// Carrier
		$arrLogData		['Carrier']		= CARRIER_UNITEL;
		$arrRequestData	['Carrier']		= CARRIER_UNITEL;
		$arrServiceData	['Carrier']		= CARRIER_UNITEL;
		
		// Request Type
		switch ($arrLineData['OrderType'])
		{
			case "11":	// Migration Request
			case "12":	// Churn to eBill
				$arrRequestData['RequestType']	= REQUEST_FULL_SERVICE;
				break;
			case "13":	// Virtual PreSelection
				$arrRequestData['RequestType']	= REQUEST_PRESELECTION;
				break;
			case "00":
			default:
				// Either unhandled or not required
				break;
		}
		
		// Default value for Log's Type field is "Other"
		$arrLogData['Type']						= LINE_ACTION_OTHER;
		
		switch ($arrLineData['RecordType'])
		{
			case "S":	// Gain - new service
			case "G":	// Gain - reversal
				$arrRequestData	['RequestType']	= REQUEST_FULL_SERVICE;
				$arrServiceData	['LineStatus']	= LINE_ACTIVE;
				$arrLogData		['Type']		= LINE_ACTION_GAIN;
				
				// Attempt to match request
				break;
			case "E":	// Loss - commercial churn
			case "O":	// Loss - other ASD
			case "L":	// Loss - other CSP
				$arrServiceData	['LineStatus']	= LINE_ACTIVE;
				$arrLogData		['Type']		= LINE_ACTION_LOSS;
				$arrLogData		['Description']	= DESCRIPTION_LOST_TO.$this->_GetCarrierName($arrLineData['LostTo']);
				break;
			case "X":	// Loss - cancellation
				$arrServiceData	['LineStatus']	= LINE_DEACTIVATED;
				$arrLogData		['Type']		= LINE_ACTION_LOSS;
				$arrLogData		['Description']	= DESCRIPTION_CANCELLED;
				break;
			case "N":	// Change - number
			case "M":	// Change - address
			case "B":	// Change - number & address
				$arrLogData		['Type']		= LINE_ACTION_OTHER;
				break;
			case "P":	// Order pending with Telstra
			case "W":	// Order waiting to be processed
			case "A":	// Order actioned by WeBill
				$arrRequestData	['Status']		= REQUEST_STATUS_PENDING;
				break;
			case "D":	// Order disqualified by WeBill
			case "R":	// Order rejected by Telstra
				$arrRequestData	['Status']		= REQUEST_STATUS_REJECTED;
				break;
			case "C":	// Order completed by Telstra
				$arrRequestData	['Status']		= REQUEST_STATUS_COMPLETED;
				break;
			default:	// Unknown Record Type
				return PRV_BAD_RECORD_TYPE;
		}
		
		// Basket
		$arrServiceData['Basket']	= (int)$arrLineData['Basket'];
				
		// Add split line to File data array
		$this->_arrRequest	= $arrRequestData;
		$this->_arrService	= $arrServiceData;
		$this->_arrLog		= $arrLogData;
		
		return PRV_SUCCESS;
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
			$arrResult['Status']	= $this->_arrRequest['Status'];
			
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
		$arrData['FNN']	= $this->_arrService['ServiceId'];
		$this->_selMatchService->Execute();
		
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
				
				// Run the query
				return $this->_ubiService->Execute();
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
			return FALSE;
		}
	}
 
 
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
 	function BuildRequest()
	{
		// TODO
	} 	
 	
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
 	function SendRequest()
	{
		// TODO
	} 	
 }

?>
