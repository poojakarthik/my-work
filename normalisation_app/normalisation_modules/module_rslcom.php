<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_rslcom
//----------------------------------------------------------------------------//
/**
 * module_rslcom.php
 *
 * Normalisation module for RSLCOM batch files
 *
 * Normalisation module for RSLCOM batch files
 *
 * @file			module_rslcom.php
 * @language		PHP
 * @package			vixen
 * @author			Rich Davis
 * @version			6.11
 * @copyright		2006 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// NormalisationModuleRSLCOM
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleRSLCOM
 *
 * Normalisation module for RSLCOM batch files
 *
 * Normalisation module for RSLCOM batch files
 *
 * @prefix			nrm
 *
 * @package			vixen
 * @class			<ClassName||InstanceName>
 */
class NormalisationModuleRSLCOM extends NormalisationModule
{

	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Normalising Module
	 *
	 * Constructor for the Normalising Module
	 *
	 *
	 * @method
	 */
	function __construct()
	{
		// call parent constructor
		parent::__construct();
		
		// define the column delimiter
		$this->_strDelimiter = ",";
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the carrier CDR format
		$arrDefine ['EventId']			['Index']		= 0;	// Unique Identifier
		$arrDefine ['RecordType']		['Index']		= 1;	// 1 = Usage; 7 = S&E; 8 = OC&C
		$arrDefine ['DateTime']			['Index']		= 2;	// Starting Datetime of the call
		$arrDefine ['Duration']			['Index']		= 3;	// Duration in seconds
		$arrDefine ['OriginNo']			['Index']		= 4;	// Originating phone number
		$arrDefine ['DestinationNo']	['Index']		= 5;	// Destination phone number
		$arrDefine ['ChargedParty']		['Index']		= 6;	// Charged phone number
		$arrDefine ['Currency']			['Index']		= 7;	// Usually AUD
		$arrDefine ['Price']			['Index']		= 8;	// Price charged to VOIPTel
		$arrDefine ['PlanId']			['Index']		= 9;	// Unitel Plan ID
		$arrDefine ['Distance']			['Index']		= 10;	// Distance in KM travelled by call
		$arrDefine ['IsLocal']			['Index']		= 11;	// 1 = Local Call; 0 = Non-Local
		$arrDefine ['CallType']			['Index']		= 12;	// Unitel Call Type ID
		$arrDefine ['BeginDate']		['Index']		= 13;	// Starting Date (RecordType 7&8 Only)
		$arrDefine ['EndDate']			['Index']		= 14;	// Ending Date (RecordType 7&8 Only)
		$arrDefine ['Description']		['Index']		= 15;	// Description (RecordType 7&8 Only)
		$arrDefine ['ItemCount']		['Index']		= 16;	// Item Count (RecordType 7&8 Only)
		$arrDefine ['CarrierId']		['Index']		= 17;	// 1 = Telstra; 2 = Optus; 3 = Unitel
		$arrDefine ['RateId']			['Index']		= 18;	// Unitel's Rate ID
		
		$arrDefine ['EventId']			['Validate']	= "^\d+$";
		$arrDefine ['RecordType']		['Validate']	= "^[178]$";
		$arrDefine ['DateTime']			['Validate']	= "^[0-3]\d/[01]\d/\d{4} [0-2]\d:[0-5]\d:[0-5]\d$";
		$arrDefine ['Duration']			['Validate']	= "^\d+$";
		$arrDefine ['OriginNo']			['Validate']	= "^\+?\d+$";
		$arrDefine ['DestinationNo']	['Validate']	= "^\+?\d+$";
		$arrDefine ['ChargedParty']		['Validate']	= "^\+?\d+$";
		$arrDefine ['Currency']			['Validate']	= "^AUD$";
		$arrDefine ['Price']			['Validate']	= "^\d+\.\d\d?$";
		$arrDefine ['CallType']			['Validate']	= "^\d+$";
		$arrDefine ['RateId']			['Validate']	= "^\d+$";
		
		$this->_arrDefineCarrier = $arrDefine;
	}

	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises raw data from the CDR
	 *
	 * Normalises raw data from the CDR
	 * 
	 * @param	array		arrCDR		Array returned from SELECT query on CDR
	 *
	 * @return	array					Normalised Data, ready for direct UPDATE
	 * 									into DB
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */	
	function Normalise($arrCDR)
	{

		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR.CDR"]);
	
		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return CDR_CANT_NORMALISE_RAW;
		}
		
		// build a new output CDR
		$this->_NewCDR();
		
		//--------------------------------------------------------------------//
		// add fields to CDR
		//--------------------------------------------------------------------//
		
		// FNN
		$mixValue = $this->_FetchRawCDR('ChargedParty');
		$this->_AppendCDR('FNN', $this->RemoveAusCode($mixValue));
		
		// CarrierRef
		$mixValue = $this->_FetchRawCDR('EventId');
		$this->_AppendCDR('CarrierRef', $mixValue);

		// StartDateTime & EndDateTime
		if ($mixValue == "1")
		{
		 	// For normal usage CDRs
		 	$mixValue					= ConvertTime($this->_FetchRawCDR('Datetime'));
		 	$this->_AppendCDR('StartDateTime', $mixValue);
		 	
		 	$mixValue					= date("Y-m-d H:i:s", strtotime($this->_FetchRawCDR('Datetime') . " +" . $this->_FetchRawCDR('Duration') . "seconds"));
			$this->_AppendCDR('EndDateTime', $mixValue);
		}
		else
		{
		 	// For S&E and OC&C CDRs
		 	$mixValue					=  ConvertTime($this->_FetchRawCDR('BeginDate'));
		 	$this->_AppendCDR('StartDateTime', $mixValue);
		 	$mixValue					=  ConvertTime($this->_FetchRawCDR('EndDate'));
		 	$this->_AppendCDR('EndDateTime', $mixValue);
		}
		
		
		// Units
		if ($this->_FetchRawCDR('EventId') == "1")
		{
		 	// For normal usage CDRs
		 	$mixValue					= $this->_FetchRawCDR('Duration');
		 	$this->_AppendCDR('Units', (int)$mixValue);
		}
		else
		{
		 	// For S&E and OC&C CDRs
		 	$mixValue					=  $this->_FetchRawCDR('ItemCount');
		 	$this->_AppendCDR('Units', (int)$mixValue);
		}
		
		// Description
		if ($this->_FetchRawCDR('EventId') == "1")
		{
		 	// For normal usage CDRs
		 	$mixValue					= $this->_FetchRawCDR('CallType');	// TODO: Link to Call Type List/Table
		 	$this->_AppendCDR('Description', (int)$mixValue);
		}
		else
		{
		 	// For S&E and OC&C CDRs
		 	$mixValue					=  $this->_FetchRawCDR('Description');
		 	$this->_AppendCDR('Description', $mixValue);
		}

		// RecordType
		//$mixValue = ; // needs to match database
		$this->_AppendCDR('RecordType', $mixValue);
		
		// ServiceType
		$mixValue = SERVICE_TYPE_LAND_LINE;
		$this->_AppendCDR('ServiceType', $mixValue);

		// Cost
		$mixValue						=  $this->_FetchRawCDR('Price');
		$this->_AppendCDR('ServiceType', (float)$mixValue);


		//--------------------------------------------------------------------//
		
		if (!$this->ApplyOwnership())
		{
			$this->_AppendCDR('Status', CDR_BAD_OWNER);
		}
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
	
	function ConvertTime($strTime)
	{
		$strReturn 	= substr($strTime, 6, 4);				// Year
		$strReturn .=  "-" . substr($strTime, 3, 2);		// Month
		$strReturn .=  "-" . substr($strTime, 0, 2);		// Day
		$strReturn .=  substr($strTime, 11, 18);			// Time
		
		return $strReturn;
	}
}
	
	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleRSLCOM
	//------------------------------------------------------------------------//
?>
