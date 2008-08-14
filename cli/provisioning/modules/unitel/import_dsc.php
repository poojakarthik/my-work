<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import_dsc
//----------------------------------------------------------------------------//
/**
 * import_dsc
 *
 * Parses a Unitel Daily Status Change file
 *
 * Parses a Unitel Daily Status Change file
 *
 * @file		import_dsc.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ImportUnitelDSC
//----------------------------------------------------------------------------//
/**
 * ImportUnitelDSC
 *
 * Parses a Unitel Daily Status Change file
 *
 * Parses a Unitel Daily Status Change file
 *
 * @prefix		imp
 *
 * @package		provisioning
 * @class		ImportUnitelDSC
 */
 class ImportUnitelDSC extends ImportBase
 {
	
	public $intBaseCarrier	= CARRIER_UNITEL;
	public $intBaseFileType	= FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS;
	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor
	 *
	 * Constructor
	 * 
	 * @param	integer	$intCarrier				The Carrier using this Module
	 * 
	 * @return	ImportBase
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		// Parent Constructor
 		parent::__construct($intCarrier);
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		// Row numbers start at 1
		// for a file without any header row, set this to 1
		// for a file with 1 header row, set this to 2
		$this->_intStartRow = 2;
 		
 		// Delimiter
 		$this->_strDelimiter	= "";
 		$this->_strEndOfLine	= "\r\n";
 		
		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 1;
		
		$arrDefine['Sequence']		['Start']		= 1;
		$arrDefine['Sequence']		['Length']		= 5;
		
		$arrDefine['OrderId']		['Start']		= 6;
		$arrDefine['OrderId']		['Length']		= 9;

		$arrDefine['OrderType']		['Start']		= 15;
		$arrDefine['OrderType']		['Length']		= 2;
		
		$arrDefine['OrderYear']		['Start']		= 17;
		$arrDefine['OrderYear']		['Length']		= 4;
		
		$arrDefine['OrderMonth']	['Start']		= 21;
		$arrDefine['OrderMonth']	['Length']		= 2;
		
		$arrDefine['OrderDay']		['Start']		= 23;
		$arrDefine['OrderDay']		['Length']		= 2;
		
		$arrDefine['ServiceNo']		['Start']		= 25;
		$arrDefine['ServiceNo']		['Length']		= 29;
		
		$arrDefine['Basket']		['Start']		= 54;
		$arrDefine['Basket']		['Length']		= 3;
		
		$arrDefine['EffectiveYear']	['Start']		= 57;
		$arrDefine['EffectiveYear']	['Length']		= 4;
		
		$arrDefine['EffectiveMonth']['Start']		= 61;
		$arrDefine['EffectiveMonth']['Length']		= 2;
		
		$arrDefine['EffectiveDay']	['Start']		= 63;
		$arrDefine['EffectiveDay']	['Length']		= 2;
		
		$arrDefine['NewNo']			['Start']		= 65;
		$arrDefine['NewNo']			['Length']		= 29;
		
		$arrDefine['ReasonCode']	['Start']		= 94;
		$arrDefine['ReasonCode']	['Length']		= 3;
		
		$arrDefine['LostTo']		['Start']		= 97;
		$arrDefine['LostTo']		['Length']		= 3;
		
		$arrDefine['RSLReference']	['Start']		= 100;
		$arrDefine['RSLReference']	['Length']		= 9;
		
		$this->_arrDefine = $arrDefine;
 	}
 	
 	//------------------------------------------------------------------------//
	// PreProcess
	//------------------------------------------------------------------------//
	/**
	 * PreProcess()
	 *
	 * Pre-processes a file
	 *
	 * Pre-processes a file
	 * 
	 * @param	array	$arrRawData		File Data to parse
	 * 
	 * @return	array					Parsed data
	 *
	 * @method
	 */
 	function PreProcess($arrRawData)
 	{
 		// foreach line
 		$strLastFNN		= NULL;
 		$strNewLine		= "";
 		$arrParsedData	= Array();
 		foreach ($arrRawData as $strLine)
 		{
 			// Check the FNN
 			$strFNN		= trim(substr($strLine, 25, 29));
 			$strBasket	= trim(substr($strLine, 54, 3));
 			//Debug($strFNN);
 			
 			// If this line has the same FNN as the last line, and is not Basket 6 (Virtual Preselection), merge
 			if ($strFNN === $strLastFNN && $strBasket !== '006')
 			{
 				$strNewLine			.= $strLine;
 			}
 			elseif ($strNewLine)
 			{
 				$arrParsedData[]	= $strNewLine;
 				$strNewLine			= $strLine;
 			}
 			
 			$strLastFNN = $strFNN;
 		}
 		
 		// Return the new data
 		//Debug($arrParsedData);
 		return $arrParsedData;
 	}
 	
 	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises a line from a Provisioning File
	 *
	 * Normalises a line from a Provisioning File
	 * 
	 * @param	string	$strLine		Line to parse
	 * 
	 * @return	array					Parsed data
	 *
	 * @method
	 */
 	function Normalise($strLine, $intLineNumber)
 	{
 		// Split the line on \n
 		$arrLines = explode("\n", trim($strLine));
 		
 		// There should be 5-6 lines, 1 for each basket
 		$arrPDR		= Array();
 		$arrBaskets	= Array();
 		foreach ($arrLines as $strSubLine)
 		{
 			// Split the sub-line using the file definition
 			$arrData = $this->_SplitLine($strSubLine);
 			
 			//Debug($arrData);
 			
 			//----------------------------------------------------------------//
 			// FNN
 			$arrPDR['FNN']	= trim($arrData['ServiceNo']);
 			//----------------------------------------------------------------//
 			
 			//----------------------------------------------------------------//
 			// Type
			switch ($arrData['OrderType'])
			{
				case "11":	// Migration Request
				case "12":	// Churn to eBill
					$arrPDR['Type']	= PROVISIONING_TYPE_FULL_SERVICE;
					break;
				case "13":	// Virtual PreSelection
					$arrPDR['Type']	= PROVISIONING_TYPE_VIRTUAL_PRESELECTION;
					break;
				case "52":
					$arrPDR['Type']	= PROVISIONING_TYPE_FULL_SERVICE_REVERSE;
					break;
				case "53":
					$arrPDR['Type']	= PROVISIONING_TYPE_VIRTUAL_PRESELECTION_REVERSE;
					break;
				default:
					// Either unhandled or not required
					break;
			}
			
 			switch ($arrData['RecordType'])
 			{
				case "S":	// Gain - new service
					$arrPDR['Type']				= PROVISIONING_TYPE_FULL_SERVICE;
					$arrPDR['Description']		= "Service Gained";
					$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
					
					// This can span over multiple lines in the file
					$arrBaskets[]	= (int)$arrData['Basket'];
					break;
					
				case "G":	// Gain - reversal
					$arrPDR['Type']				= PROVISIONING_TYPE_FULL_SERVICE;
					$arrPDR['Description']		= "Service Gained by Reversal";
					$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
					
					// This can span over multiple lines in the file
					$arrBaskets[]	= (int)$arrData['Basket'];
					break;
					
				case "E":	// Loss - commercial churn
				case "O":	// Loss - other ASD
				case "L":	// Loss - other CSP
					if ($arrPDR['Type'] || (int)$arrData['Basket'] != 6)
					{
						// If there are multiple lines, Full Service Loss
						$arrPDR['Type']				= PROVISIONING_TYPE_LOSS_FULL;
						$arrPDR['Description']		= "Service lost to Carrier #{$arrData['LostTo']}";
						$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
						$bolFullServiceChurn		= TRUE;
					}
					else
					{
						// If there is only Basket 6, then Preslection Loss
						$arrPDR['Type']				= PROVISIONING_TYPE_LOSS_VIRTUAL_PRESELECTION;
						$arrPDR['Description']		= "Preselection lost to Carrier #{$arrData['LostTo']}";
						$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
					}
					
					// This can span over multiple lines in the file
					$arrBaskets[]	= (int)$arrData['Basket'];
					break;
					
				case "X":	// Loss - cancellation
					$arrPDR['Type']				= PROVISIONING_TYPE_DISCONNECT_FULL;
					$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
					$arrPDR['Description']		= "Service Cancelled/Disconnected";
					
					// This can span over multiple lines in the file
					$arrBaskets[]	= (int)$arrData['Basket'];
					break;
				
				case "N":	// Change - number
				case "M":	// Change - address
				case "B":	// Change - number & address
					$arrPDR['Type']				= PROVISIONING_TYPE_CHANGE_ADDRESS;
					$arrPDR['Description']		= "Address Changed";
					break;
					
				case "P":	// Order pending with Telstra
					$arrPDR['request_status']	= REQUEST_STATUS_PENDING;
					$arrPDR['Description']		= "Order Pending with Telstra";
					break;
					
				case "W":	// Order waiting to be processed
					$arrPDR['request_status']	= REQUEST_STATUS_PENDING;
					$arrPDR['Description']		= "Order Pending with Unitel";
					break;
					
				case "A":	// Order actioned by WeBill
					$arrPDR['request_status']	= REQUEST_STATUS_PENDING;
					$arrPDR['Description']		= "Order accepted by Unitel";
					break;
					
				case "D":	// Order disqualified by WeBill
					$arrPDR['request_status']	= REQUEST_STATUS_REJECTED;
					$arrPDR['Description']		= "Order Rejected by Unitel - <Reason>";
					break;
					
				case "R":	// Order rejected by Telstra
					$arrPDR['request_status']	= REQUEST_STATUS_REJECTED;
					$arrPDR['Description']		= "Order Rejected by Telstra - <Reason>";
					break;
					
				case "C":	// Order completed by Telstra
					$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
					$arrPDR['Description']		= "Order completed by Telstra";
					break;
					
				default:	// Unknown Record Type
					$arrPDR['Status']			= RESPONSE_STATUS_CANT_NORMALISE;
 			}
 			//----------------------------------------------------------------//
 			
 			//----------------------------------------------------------------//
 			// CarrierRef
 			$arrPDR['CarrierRef'] = (int)$arrData['OrderId'];
 			//----------------------------------------------------------------//
 			
 			//----------------------------------------------------------------//
 			// Description
 			if (stripos($arrPDR['Description'], '<Reason>'))
 			{
 				// Convert Unitel Error Code to Text
 				$arrPDR['Description']	= str_ireplace('<Reason>', $this->TranslateCarrierCode(PROVISIONING_CONTEXT_REJECT_UNITEL, $arrData['ReasonCode']), $arrPDR['Description']);
 			}
 			
 			$arrPDR['Description']	.= " (Baskets: ".implode(', ', $arrBaskets).")";
 			//----------------------------------------------------------------//
			
			//----------------------------------------------------------------//
			// Request Status
			// Handled Elsewhere
			//----------------------------------------------------------------//
 			
 			//----------------------------------------------------------------//
 			// EffectiveDate
 			if ($arrData['EffectiveYear'])
 			{
 				$arrPDR['EffectiveDate']	= "{$arrData['EffectiveYear']}-{$arrData['EffectiveMonth']}-{$arrData['EffectiveDay']}";
 			}
 			elseif ($arrData['OrderYear'])
 			{
 				$arrPDR['EffectiveDate']	= "{$arrData['OrderYear']}-{$arrData['OrderMonth']}-{$arrData['OrderDay']}";
 			}
 			//----------------------------------------------------------------//
 		}
 		
		//----------------------------------------------------------------//
		// Find Owner
		$arrPDR	= $this->FindFNNOwner($arrPDR);
		if ($arrPDR['Account'])
		{
			if ($arrPDR['Type'] == PROVISIONING_TYPE_LOSS_FULL)
			{
				// Add System Note
				//AddServiceChurnNote($arrOwner['Account'], $arrOwner['AccountGroup'], $arrPDR['FNN'], CARRIER_UNITEL);
				CliEcho("{$arrPDR['FNN']} ({$arrPDR['Account']}) has been lost");
			}
		}
		//----------------------------------------------------------------//
 		
 		if (!$arrPDR['Type'])
 		{
 			Debug($arrData);
 			Debug($arrPDR);
 		}
 		
 		return $arrPDR;
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// LinkToRequest
	//------------------------------------------------------------------------//
	/**
	 * LinkToRequest()
	 *
	 * Attempts to link a Response to a Request
	 *
	 * Attempts to link a Response to a Request
	 * 
	 * @param	array	$arrResponse	Response to match against
	 * 
	 * @return	integer					Request Id
	 *
	 * @method
	 */
	 function LinkToRequest($arrResponse)
	 {
	 	// Match by CarrierRef
	 	if ($arrResponse['CarrierRef'])
	 	{
	 		if ($this->_selRequestByCarrierRef->Execute($arrResponse))
	 		{
	 			// Found a match, return the Id
	 			$arrReturn = $this->_selRequestByCarrierRef->Fetch();
	 			return $arrReturn;
	 		}
	 	}
	 	
	 	// Run the default matcher
	 	return parent::LinkToRequest($arrResponse);
	 }
 }
?>
