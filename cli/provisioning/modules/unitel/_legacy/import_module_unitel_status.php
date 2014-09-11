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
 * Unitel Import Module for the provisioning engine (Daily Status Changes)
 *
 * Unitel Import Module for the provisioning engine (Daily Status Changes)
 *
 * @file		module_import_unitel_status.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ProvisioningModuleImportUnitelStatus
//----------------------------------------------------------------------------//
/**
 * ProvisioningModuleImportUnitelStatus
 *
 * Unitel Module for the provisioning engine (Daily Status Changes)
 *
 * Unitel Module for the provisioning engine.  (Daily Status Changes)
 *
 * @prefix		prv
 *
 * @package		provisioning
 * @class		ProvisioningModuleExportUnitelDOF
 */
 class ProvisioningModuleImportUnitelStatus extends ProvisioningModuleImport
 {
	//------------------------------------------------------------------------//
	// __construct()
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor method for ProvisioningModuleImportUnitelStatus
	 *
	 * Constructor method for ProvisioningModuleImportUnitelStatus
	 *
	 * @return		ProvisioningModuleImportUnitelStatus
	 *
	 * @method
	 */
 	function  __construct($ptrDB)
 	{
		$this->_strModuleName	= "Unitel";
		$this->_intCarrier		= CARRIER_UNITEL;
		
		parent::__construct($ptrDB);
		
		$this->_updPreselectSequence			= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'PreselectionFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceFileSequence		= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceFileSequence'", Array('Value' => NULL));
		$this->_updFullServiceRecordSequence	= new StatementUpdate("Config", "Application = ".APPLICATION_PROVISIONING." AND Module = 'Unitel' AND Name = 'FullServiceRecordSequence'", Array('Value' => NULL));
		
				
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
		
		$this->_bolContinuable = FALSE;
		$this->_arrContinuableBaskets = Array();	
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
		
		// Check to see if our continuable request is finished
		$strFNN = RemoveAusCode($arrLineData['ServiceNo']);
		if ($this->_bolContinuable)
		{
			if ($strFNN != $this->_arrRequest['FNN'])
			{
				$this->_arrLog['Description']	.= " (Baskets ".implode(', ', $this->_arrContinuableBaskets).")";
				$this->_arrContinuableBaskets	= Array();
				$this->_bolContinuable			= FALSE;
				return CONTINUABLE_FINISHED;
			}
			
			$this->_arrContinuableBaskets[]	= (int)$arrLineData['Basket'];
			return CONTINUABLE_CONTINUE;
		}
		
		// FNN
		$arrRequestData	['FNN']	= $strFNN;
		$arrLogData		['FNN']	= $strFNN;
		
		// Date
		$strDate = trim($arrLineData['EffectiveDate']);
		if (!$strDate)
		{
			$strDate = date("Y-m-d", time());
		}
		$arrRequestData	['Date']		= $strDate;
		$arrLogData		['Date']		= $strDate;
		
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
				$arrLogData		['Description']	= "Service Gained";
				
				// This can span over multiple lines in the file
				$this->_bolContinuable = TRUE;
				$this->_arrContinuableBaskets[]	= (int)$arrLineData['Basket'];
				
				// Attempt to match request
				break;
			case "E":	// Loss - commercial churn
			case "O":	// Loss - other ASD
			case "L":	// Loss - other CSP
				$arrServiceData	['LineStatus']	= LINE_DEACTIVATED;
				$arrLogData		['Type']		= LINE_ACTION_LOSS;
				$arrLogData		['Description']	= DESCRIPTION_LOST_TO.$this->_GetCarrierName($arrLineData['LostTo']);
				
				// This can span over multiple lines in the file
				$this->_bolContinuable = TRUE;
				$this->_arrContinuableBaskets[]	= (int)$arrLineData['Basket'];
				break;
			case "X":	// Loss - cancellation
				$arrServiceData	['LineStatus']	= LINE_DEACTIVATED;
				$arrLogData		['Type']		= LINE_ACTION_LOSS;
				$arrLogData		['Description']	= DESCRIPTION_CANCELLED;
				
				// This can span over multiple lines in the file
				$this->_bolContinuable = TRUE;
				$this->_arrContinuableBaskets[]	= (int)$arrLineData['Basket'];
				break;
			case "N":	// Change - number
			case "M":	// Change - address
			case "B":	// Change - number & address
				$arrLogData		['Type']		= LINE_ACTION_OTHER;
				$arrLogData		['Description']	= "Address Changed";
				break;
			case "P":	// Order pending with Telstra
			case "W":	// Order waiting to be processed
			case "A":	// Order actioned by WeBill
				$arrRequestData	['Status']		= REQUEST_STATUS_PENDING;
				$arrLogData		['Description']	= "Order accepted by Unitel";
				break;
			case "D":	// Order disqualified by WeBill
			case "R":	// Order rejected by Telstra
				$arrRequestData	['Status']		= REQUEST_STATUS_REJECTED;
				$arrLogData		['Description']	= "Order Rejected by Telstra";
				break;
			case "C":	// Order completed by Telstra
				$arrRequestData	['Status']		= REQUEST_STATUS_COMPLETED;
				$arrLogData		['Description']	= "Order completed by Telstra";
				break;
			default:	// Unknown Record Type
				return PRV_BAD_RECORD_TYPE;
		}
		
		// Add split line to File data array
		$this->_arrRequest	= $arrRequestData;
		$this->_arrService	= $arrServiceData;
		$this->_arrLog		= $arrLogData;
		
		if ($this->_bolContinuable)
		{
			return CONTINUABLE_CONTINUE;
		}
		else
		{
			return TRUE;
		}
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
			$this->_arrLog['Request']	= $arrResult['Id'];
			$arrResult['LineStatus']	= $this->_arrRequest['LineStatus'];
			
			// If we've gained/lost then update the appropriate field
			if ($this->_arrLog['Type'] == LINE_ACTION_GAIN)
			{
				$arrResult['GainDate'] = $this->_arrRequest['Date'];
			}
			elseif ($this->_arrLog['Type'] == LINE_ACTION_LOSS)
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
		$arrData['FNN']	= trim($this->_arrRequest['FNN']);
		if ($this->_selMatchService->Execute($arrData) === FALSE)
		{
			Debug($this->_selMatchService->Error());
		}
		
		// Match to an entry in the Service table
		if($arrResult = $this->_selMatchService->Fetch())
		{
			$this->_arrLog['Service']	= $arrResult['Id'];
			
			// Make sure our status is up to date
			$arrData = Array('Date' => $this->_arrRequest['Date']);
			$this->_selMatchLog->Execute($arrData);
			
			// If this is the most up to date status
			if (!$this->_selMatchLog->Fetch())
			{
				// Actually update the service
				$arrResult['LineStatus']	= $this->_arrService['LineStatus'];
				$arrResult['ClosedOn']		= $this->_arrRequest['LossDate'];
				
				// Update the Carrier/CarrierPreselect fields if necessary
				if ($this->_arrLog['Type'] == LINE_ACTION_GAIN)
				{
					if (in_array(6, $this->_arrContinuableBaskets))
					{
						$arrResult['CarrierPreselect']	= CARRIER_UNITEL;
						if (count($this->_arrContinuableBaskets) > 1)
						{
							$arrResult['Carrier']		= CARRIER_UNITEL;
						}
					}
					else
					{
						$arrResult['Carrier']			= CARRIER_UNITEL;
					}
					break;
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
			
			// DEBUG - Output some details here
			$arrDebug['FNN']			= $arrData['FNN'];
			$arrDebug['RequestType']	= $this->_arrRequest['RequestType'];
			$arrDebug['Action']			= $this->_arrLog['Type'];
			//Debug($arrDebug);
			
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