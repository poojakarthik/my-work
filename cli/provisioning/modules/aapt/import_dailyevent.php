<?php
/**
 * ImportAAPTDailyEvent
 *
 * Parses a Unitel Preselection Response file
 *
 * @class		ImportAAPTDailyEvent
 */
 class ImportAAPTDailyEvent extends ImportBase
 {
	public $intBaseCarrier		= CARRIER_UNITEL;
	public $intBaseFileType		= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_DAILYEVENT;
	
	/**
	 * __construct()
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
 		
 		// Field Delimiter
 		$this->_strDelimiter	= ",";
 		$this->_strEndOfLine	= "\r\n";
 		
		$arrDefine['FNN']			['Index']		= 0;
 		
		$arrDefine['Type']			['Index']		= 1;
 		
		$arrDefine['Result']		['Index']		= 2;
 		
		$arrDefine['Description']	['Index']		= 3;
		
		$this->_arrDefine	=	array
								(
									'RowCode'			=>	array
															(
																'Index'	=>	0
															),
									'BatchNo'			=>	array
															(
																'Index'	=>	1
															),
									'IDNo'				=>	array
															(
																'Index'	=>	2
															),
									'BillingAccountNo'	=>	array
															(
																'Index'	=>	3
															),
									'ServiceNumber'		=>	array
															(
																'Index'	=>	4
															),
									'IsSpectrum'		=>	array
															(
																'Index'	=>	5
															),
									'MCPCode'			=>	array
															(
																'Index'	=>	6
															),
									'MCPDate'			=>	array
															(
																'Index'	=>	7
															),
									'RejectCode'		=>	array
															(
																'Index'	=>	8
															),
									'LossCode'			=>	array
															(
																'Index'	=>	9
															),
									'LossPSD'			=>	array
															(
																'Index'	=>	10
															),
									'NewServiceNumber'	=>	array
															(
																'Index'	=>	11
															),
									'WhitelistCode'		=>	array
															(
																'Index'	=>	12
															),
									'WhitelistDate'		=>	array
															(
																'Index'	=>	13
															),
									'WhitelistRefCode'	=>	array
															(
																'Index'	=>	14
															),
									'Comment'			=>	array
															(
																'Index'	=>	15
															),
									'eBillCode'			=>	array
															(
																'Index'	=>	16
															),
									'eBillCodeValue'	=>	array
															(
																'Index'	=>	17
															),
									'eBillDate'			=>	array
															(
																'Index'	=>	18
															)
								);
 	}
 	
	/**
	 * PreProcess()
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
 		// No need to PreProcess
 		return $arrRawData;
 	}
 	
	/**
	 * Normalise()
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
 		$arrPDR	= array();
 		
 		// Check for non-Detail records
 		if (stripos($strLine, 'D') !== 0)
 		{
			$arrPDR['Status']	= RESPONSE_STATUS_CANT_NORMALISE;
			return $arrPDR;
 		}
 		
 		// Split the Line using the file definition
 		$arrData = $this->_SplitLine($strLine);
 		
		//----------------------------------------------------------------//
		// FNN
		$arrPDR['FNN']	= $arrData['FNN'];
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// Type
		
		
		switch ($arrData['Type'])
		{
			case 'Activate':
				$arrPDR['Type']			= PROVISIONING_TYPE_ACTIVATION;
				break;
				
			case 'Deactivation':
				$arrPDR['Type']			= PROVISIONING_TYPE_DEACTIVATION;
				break;
				
			case 'Bar':
				$arrPDR['Type']			= PROVISIONING_TYPE_BAR;
				break;
				
			case 'Unbar':
				$arrPDR['Type']			= PROVISIONING_TYPE_UNBAR;
				break;
				
			case 'Preselect':
				$arrPDR['Type']			= PROVISIONING_TYPE_PRESELECTION;
				break;
				
			case 'PSReversal':
				$arrPDR['Type']			= PROVISIONING_TYPE_PRESELECTION_REVERSE;
				break;
				
			default:
				$arrPDR['Type']			= NULL;
				break;
		}
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// CarrierRef
		$arrPDR['CarrierRef'] = $intLineNumber;
		//----------------------------------------------------------------//
 		
		//----------------------------------------------------------------//
		// Description
		if ($arrData['Description'] == 'Success')
		{
			$arrPDR['Description']	== 'Pending';
		}
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// EffectiveDate
		// Handled Elsewhere
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// Request Status
		if ((int)$arrData['Result'])
		{
			// Unsuccessful
			$arrPDR['request_status']		= REQUEST_STATUS_REJECTED;
		}
		elseif ($arrPDR['Description'] == 'Pending')
		{
			// Successful - Pending
			$arrPDR['request_status']	= REQUEST_STATUS_PENDING;
		}
		else
		{
			// Successful - Completed
			$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
		}
		//----------------------------------------------------------------//
 		
		//----------------------------------------------------------------//
		// Find Owner
		$arrPDR	= $this->FindFNNOwner($arrPDR);
		//----------------------------------------------------------------//
 		
 		if (!$arrPDR['Type'])
 		{
 			Debug($arrData);
 		}
 		
 		return $arrPDR;
 	}
 	
	/**
	 * LinkToRequest()
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
	 	// Run the default matcher
	 	return parent::LinkToRequest($arrResponse);
	 }
 }
?>
