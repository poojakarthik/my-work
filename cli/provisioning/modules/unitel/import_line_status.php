<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import_line_status
//----------------------------------------------------------------------------//
/**
 * import_line_status
 *
 * Parses a Unitel Line Status file
 *
 * Parses a Unitel Line Status file
 *
 * @file		import_line_status.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ImportUnitelLineStatus
//----------------------------------------------------------------------------//
/**
 * ImportUnitelLineStatus
 *
 * Parses a Unitel Line Status file
 *
 * Parses a Unitel Line Status file
 *
 * @prefix		imp
 *
 * @package		provisioning
 * @class		ImportUnitelLineStatus
 */
 class ImportUnitelLineStatus extends ImportBase
 {
	
	public $intBaseCarrier	= CARRIER_UNITEL;
	public $intBaseFileType	= FILE_IMPORT_PROVISIONING_UNITEL_LINE_STATUS;
	
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
 		$this->_strDelimiter	= ",";
 		$this->_strEnclosed		= '"';
 		$this->_strEndOfLine	= "\r\n";
 		
		$arrDefine['AccountCode']			['Index']		= 0;
 		
		$arrDefine['DateCreated']			['Index']		= 1;
 		
		$arrDefine['FNN']					['Index']		= 2;
 		
		$arrDefine['AccountStatus']			['Index']		= 3;
 		
		$arrDefine['CarrierDescription']	['Index']		= 4;
 		
		$arrDefine['PreselectionStatus']	['Index']		= 5;
 		
		$arrDefine['UnitelStatusDate']		['Index']		= 6;
 		
		$arrDefine['LastUsedOutgoing']		['Index']		= 7;
		
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
 		// Split the Line using the file definition
 		$arrData = $this->_SplitLine($strLine);
 			
		//----------------------------------------------------------------//
		// FNN
		$arrPDR['FNN']				= $arrData['FNN'];
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// Type
		switch (trim($arrData['AccountStatus']))
		{
			case 'Activation Confirmed':
				$arrPDR['Type']				= PROVISIONING_TYPE_ACTIVATION;
				$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
				
			case 'Activation Raised':
				$arrPDR['Type']				= PROVISIONING_TYPE_ACTIVATION;
				$arrPDR['request_status']	= REQUEST_STATUS_PENDING;
				
			case 'Deactivation Confirmed':
				$arrPDR['Type']				= PROVISIONING_TYPE_ACTIVATION;
				$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
				
			case 'Bar Confirmed':
			case 'Barred by Another SP':
				$arrPDR['Type']				= PROVISIONING_TYPE_BAR;
				$arrPDR['request_status']	= REQUEST_STATUS_COMPLETED;
				
			default:
				$arrPDR['Type']				= PROVISIONING_TYPE_PRESELECTION;
				$arrPDR['request_status']	= REQUEST_STATUS_REJECTED;
		}
		
		if (stripos($arrData['PreselectionStatus'], 'Preselection Confirmed') !== FALSE)
		{
			$arrPDR['Type']					= PROVISIONING_TYPE_PRESELECTION;
			$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
		}
		elseif (stripos($arrData['PreselectionStatus'], 'Lost to ') !== FALSE)
		{
			$arrPDR['Type']					= PROVISIONING_TYPE_LOSS_PRESELECT;
			$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
		}
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// CarrierRef
		$arrPDR['CarrierRef']		= $arrData['AccountCode'];
		//----------------------------------------------------------------//
 		
		//----------------------------------------------------------------//
		// Description & EffectiveDate
		if (trim($arrData['ChurnDate']))
		{
			$arrPDR['Description']		= $arrData['PreselectionStatus'];
			$arrPDR['EffectiveDate']	= date("Y-m-d", strtotime($arrData['UnitelStatusDate']));
		}
		else
		{
			$arrPDR['Description']		= $arrData['AccountStatus'];
			$arrPDR['EffectiveDate']	= date("Y-m-d", strtotime($arrData['DateCreated']));
		}
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// Request Status
		// Handled Elsewhere
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
