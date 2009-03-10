<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// import_ppr
//----------------------------------------------------------------------------//
/**
 * import_ppr
 *
 * Parses an Optus PPR Report
 *
 * Parses an Optus PPR Report
 *
 * @file		import_ppr.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ImportOptusPPR
//----------------------------------------------------------------------------//
/**
 * ImportOptusPPR
 *
 * Parses an Optus PPR Report
 *
 * Parses an Optus PPR Report
 *
 * @prefix		imp
 *
 * @package		provisioning
 * @class		ImportOptusPPR
 */
 class ImportOptusPPR extends ImportBase
 {
	public $intBaseCarrier	= CARRIER_OPTUS;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_OPTUS_PPR;
	
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
 		
		$arrDefine['ReportYear']	['Index']		= 0;
		$arrDefine['ReportYear']	['Start']		= 0;
		$arrDefine['ReportYear']	['Length']		= 4;
 		
		$arrDefine['ReportMonth']	['Index']		= 0;
		$arrDefine['ReportMonth']	['Start']		= 4;
		$arrDefine['ReportMonth']	['Length']		= 2;
 		
		$arrDefine['ReportDay']		['Index']		= 0;
		$arrDefine['ReportDay']		['Start']		= 6;
		$arrDefine['ReportDay']		['Length']		= 2;
		
		$arrDefine['CorpNo']		['Index']		= 1;
		
		$arrDefine['AccountNo']		['Index']		= 2;

		$arrDefine['AreaCode']		['Index']		= 3;
		$arrDefine['AreaCode']		['Start']		= 0;
		$arrDefine['AreaCode']		['Length']		= 5;

		$arrDefine['ServiceLine']	['Index']		= 3;
		$arrDefine['ServiceLine']	['Start']		= 5;
		$arrDefine['ServiceLine']	['Length']		= 12;
		
		$arrDefine['ASDCode']		['Index']		= 4;
		
		$arrDefine['CarrierCode']	['Index']		= 5;
		
		$arrDefine['ChoiceYear']	['Index']		= 6;
		$arrDefine['ChoiceYear']	['Start']		= 0;
		$arrDefine['ChoiceYear']	['Length']		= 4;
		
		$arrDefine['ChoiceMonth']	['Index']		= 6;
		$arrDefine['ChoiceMonth']	['Start']		= 4;
		$arrDefine['ChoiceMonth']	['Length']		= 2;
		
		$arrDefine['ChoiceDay']		['Index']		= 6;
		$arrDefine['ChoiceDay']		['Start']		= 6;
		$arrDefine['ChoiceDay']		['Length']		= 2;
		
		$arrDefine['ConfirmYear']	['Index']		= 7;
		$arrDefine['ConfirmYear']	['Start']		= 0;
		$arrDefine['ConfirmYear']	['Length']		= 4;
		
		$arrDefine['ConfirmMonth']	['Index']		= 7;
		$arrDefine['ConfirmMonth']	['Start']		= 4;
		$arrDefine['ConfirmMonth']	['Length']		= 2;
		
		$arrDefine['ConfirmDay']	['Index']		= 7;
		$arrDefine['ConfirmDay']	['Start']		= 6;
		$arrDefine['ConfirmDay']	['Length']		= 2;
		
		$arrDefine['Status']		['Index']		= 8;
		
		$arrDefine['RejectCode']	['Index']		= 9;
		
		$arrDefine['EndYear']		['Index']		= 10;
		$arrDefine['EndYear']		['Start']		= 0;
		$arrDefine['EndYear']		['Length']		= 4;
		
		$arrDefine['EndMonth']		['Index']		= 10;
		$arrDefine['EndMonth']		['Start']		= 4;
		$arrDefine['EndMonth']		['Length']		= 2;
		
		$arrDefine['EndDay']		['Index']		= 10;
		$arrDefine['EndDay']		['Start']		= 6;
		$arrDefine['EndDay']		['Length']		= 2;
		
		$arrDefine['LossCode']		['Index']		= 11;
		
		$arrDefine['LossPSD']		['Index']		= 12;
		
		$arrDefine['NewFNN']		['Index']		= 13;
		
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
		$arrPDR['FNN']	= trim($arrData['AreaCode']).trim($arrData['ServiceLine']);
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// Type
		if ((int)$arrData['EndYear'])
		{
			// This is a Loss
			$arrPDR['Type']				= PROVISIONING_TYPE_LOSS_PRESELECT;
			$arrPDR['EffectiveDate']	= $arrData['EndYear'].'-'.$arrData['EndMonth'].'-'.$arrData['EndDay'];
			
			switch ($arrData['LossCode'])
			{
				case '01':
					$strLostTo						= $this->TranslateCarrierCode(CARRIER_TRANSLATION_CONTEXT_EPID, $arrPDR['LossPSD']);
					$arrPDR['Description']			= "Churned to ".(($strLostTo) ? $strLostTo : 'Unknown Carrier');
					$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
					break;
					
				case '02':
					$arrPDR['Type']					= PROVISIONING_TYPE_DISCONNECT_PRESELECT;
					$arrPDR['Description']			= "Service Disconnected";
					$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
					break;
					
				case '03':
					$arrPDR['Description']			= "No PSD Point of Presence in Area";
					$arrPDR['request_status']		= REQUEST_STATUS_REJECTED;
					break;
					
				case '04':
					$arrPDR['Type']					= PROVISIONING_TYPE_PRESELECTION_REVERSE;
					$arrPDR['Description']			= "Churn Reversed";
					$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
					break;
					
				default:
					$arrPDR['Description']			= "Service Lost";
					$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
					break;
			}
		}
		elseif ((int)$arrData['ConfirmYear'])
		{
			// This is a Completed Request
			$arrPDR['Type']				= PROVISIONING_TYPE_PRESELECTION;
			$arrPDR['EffectiveDate']	= $arrData['ConfirmYear'].'-'.$arrData['ConfirmMonth'].'-'.$arrData['ConfirmDay'];
			
			switch ($arrData['Status'])
			{
				case 'SUCCESSFUL':
					$arrPDR['Description']			= "Churn Completed Successfully";
					$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
					break;
					
				case 'SUCCESSFUL*':
					$arrPDR['Description']			= "Churn Completed Successfully (Already on Account)";
					$arrPDR['request_status']		= REQUEST_STATUS_COMPLETED;
					break;
					
				case 'UNSUCCESSFUL':
					$strRejected					= $this->TranslateCarrierCode(CARRIER_TRANSLATION_CONTEXT_REJECT, $arrPDR['LossCode']);
					$arrPDR['Description']			= "Churn Rejected ($strRejected)";
					$arrPDR['request_status']		= REQUEST_STATUS_REJECTED;
					break;
					
				default:
					// What to do??
					break;
			}
			$arrPDR['Description']		= "Completed";
		}
		elseif ((int)$arrData['ChoiceYear'])
		{
			// This is a Pending Request
			$arrPDR['Type']				= PROVISIONING_TYPE_PRESELECTION;
			$arrPDR['EffectiveDate']	= $arrData['ChoiceYear'].'-'.$arrData['ChoiceMonth'].'-'.$arrData['ChoiceDay'];
			$arrPDR['Description']		= "Pending";
			$arrPDR['request_status']	= REQUEST_STATUS_PENDING;
		}
		else
		{
			// This is an Activated Pending Request
			$arrPDR['Type']				= PROVISIONING_TYPE_ACTIVATION;
			$arrPDR['EffectiveDate']	= $arrData['ReportYear'].'-'.$arrData['ReportMonth'].'-'.$arrData['ReportDay'];
			$arrPDR['Description']		= "Activated";
			$arrPDR['request_status']	= REQUEST_STATUS_PENDING;
		}
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// CarrierRef
		$arrPDR['CarrierRef'] = $intLineNumber;
		//----------------------------------------------------------------//
 			
		//----------------------------------------------------------------//
		// Description
		// Handled Elsewhere
		//----------------------------------------------------------------//
		
		//----------------------------------------------------------------//
		// EffectiveDate
		// Handled Elsewhere
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
