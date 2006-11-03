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
		$arrDefine ['EventId']			['Index']		= 0;
		$arrDefine ['RecordType']		['Index']		= 1;
		$arrDefine ['DateTime']			['Index']		= 2;
		$arrDefine ['Duration']			['Index']		= 3;
		$arrDefine ['OriginNo']			['Index']		= 4;
		$arrDefine ['DestinationNo']	['Index']		= 5;
		$arrDefine ['ChargedParty']		['Index']		= 6;
		$arrDefine ['Currency']			['Index']		= 7;
		$arrDefine ['Price']			['Index']		= 8;
		$arrDefine ['PlanId']			['Index']		= 9;
		$arrDefine ['Distance']			['Index']		= 10;
		$arrDefine ['IsLocal']			['Index']		= 11;
		$arrDefine ['CallType']			['Index']		= 12;
		$arrDefine ['BeginDate']		['Index']		= 13;
		$arrDefine ['EndDate']			['Index']		= 14;
		$arrDefine ['Description']		['Index']		= 15;
		$arrDefine ['ItemCount']		['Index']		= 16;
		$arrDefine ['CarrierId']		['Index']		= 17;
		$arrDefine ['RateId']			['Index']		= 18;
		
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
		 	$mixValue					= $this->_FetchRawCDR('Datetime');
		 	$this->_AppendCDR('StartDateTime', $mixValue);
		 	
		 	$mixValue					= strtotime("+" . $this->_FetchRawCDR('Duration') . "seconds", $this->_FetchRawCDR('Datetime'));
			$this->_AppendCDR('EndDateTime', $mixValue);
		}
		else
		{
		 	// For S&E and OC&C CDRs
		 	$mixValue					=  $this->_FetchRawCDR('BeginDate');
		 	$this->_AppendCDR('StartDateTime', $mixValue);
		 	$mixValue					=  $this->_FetchRawCDR('EndDate');
		 	$this->_AppendCDR('EndDateTime', $mixValue);
		}
		
		
		// Units
		if ($this->_FetchRawCDR('EventId') == "1")
		{
		 	// For normal usage CDRs
		 	$mixValue					= $this->_FetchRawCDR('Duration');
		 	$this->_AppendCDR('Units', $mixValue);
		}
		else
		{
		 	// For S&E and OC&C CDRs
		 	$mixValue					=  $this->_FetchRawCDR('ItemCount');
		 	$this->_AppendCDR('Units', $mixValue);
		}
		
		// Description
		if ($this->_FetchRawCDR('EventId') == "1")
		{
		 	// For normal usage CDRs
		 	$mixValue					= $this->_FetchRawCDR('CallType');	// TODO: Link to Call Type List/Table
		 	$this->_AppendCDR('Description', $mixValue);
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


		//--------------------------------------------------------------------//
		
		// return output array
		return $this->_OutputCDR();
	}
}
	
	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleRSLCOM
	//------------------------------------------------------------------------//
	
?>
