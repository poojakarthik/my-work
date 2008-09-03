<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import_preselection
//----------------------------------------------------------------------------//
/**
 * import_preselection
 *
 * Parses a Unitel Preselection Response file
 *
 * Parses a Unitel Preselection Response file
 *
 * @file		import_preselection.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ImportUnitelPreselection
//----------------------------------------------------------------------------//
/**
 * ImportUnitelPreselection
 *
 * Parses a Unitel Preselection Response file
 *
 * Parses a Unitel Preselection Response file
 *
 * @prefix		imp
 *
 * @package		provisioning
 * @class		ImportUnitelPreselection
 */
 class ImportUnitelPreselection extends ImportBase
 {
	public $intBaseCarrier		= CARRIER_UNITEL;
	public $intBaseFileType		= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION;
	
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
 		
 		// Field Delimiter
 		$this->_strDelimiter	= "\t";
 		$this->_strEndOfLine	= "\r\n";
 		
		$arrDefine['FNN']			['Index']		= 0;
 		
		$arrDefine['Type']			['Index']		= 1;
 		
		$arrDefine['Result']		['Index']		= 2;
 		
		$arrDefine['Description']	['Index']		= 3;
		
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
 		// No need to PreProcess
 		return $arrRawData;
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
 		// Check for footer
 		if ($strLine)
 		{
 			if (is_int(stripos($strLine, 'RecCount')))
 			{
				$arrPDR['Status']	= RESPONSE_STATUS_CANT_NORMALISE;
 			}
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
	 	// Run the default matcher
	 	return parent::LinkToRequest($arrResponse);
	 }
 }
?>
