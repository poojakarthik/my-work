<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// module_aapt
//----------------------------------------------------------------------------//
/**
 * module_aapt.php
 *
 * Normalisation module for AAPT batch files
 *
 * Normalisation module for AAPT batch files
 *
 * @file			module_aapt.php
 * @language		PHP
 * @package			vixen
 * @author			Jared 'flame' Herbohn
 * @version			6.11
 * @copyright		2006 VOIPTEL Pty Ltd
 * @license			NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// NormalisationModuleAAPT
//----------------------------------------------------------------------------//
/**
 * NormalisationModuleAAPT
 *
 * Normalisation module for AAPT batch files
 *
 * Normalisation module for AAPT batch files
 *
 * @prefix			nrm
 *
 * @package			vixen
 * @class			NormalisationModuleAAPT
 */
class NormalisationModuleAAPT extends NormalisationModule
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
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the column delimiter
		$this->_strDelimiter = "\t";
		
		// define the carrier CDR format
		$arrDefine ['CC']				['Index']		= 0;	// record type indicator
		$arrDefine ['OriginatingCLI']	['Index']		= 1;	// Blank or 10 digit  number
		$arrDefine ['OriginatingCSI']	['Index']		= 2;	// Up to 10 digit numeric
		$arrDefine ['OriginatingCity']	['Index']		= 3;	// 1-13 characters
		$arrDefine ['OriginatingState']	['Index']		= 4;	// 2-3 characters

		$arrDefine ['CallDetails']		['Index']		= 5;	// Contains:
		$arrDefine ['CallTime']			['Index']		= 5;	// 	HHMMSS
		$arrDefine ['CallTime']			['Start']		= 0;
		$arrDefine ['CallTime']			['Length']		= 6;
		$arrDefine ['RatePeriod']		['Index']		= 5;	// 	peak/off peak flag
		$arrDefine ['RatePeriod']		['Start']		= 6;
		$arrDefine ['RatePeriod']		['Length']		= 1;
		$arrDefine ['RevenueCallType']	['Index']		= 5;	// 	3 Character  right justified space filled
		$arrDefine ['RevenueCallType']	['Start']		= 7;
		$arrDefine ['RevenueCallType']	['Length']		= 3;

		$arrDefine ['LocatiotCallType']	['Index']		= 6;	// 3 digits
		$arrDefine ['RateTable']		['Index']		= 7;	// 2-5 digits
		$arrDefine ['Destination']		['Index']		= 8;	// city called
		$arrDefine ['NumberDialled']	['Index']		= 9;	// digits dialled by customer
		$arrDefine ['Duration']			['Index']		= 10;	// HHHH:MM:SS
		$arrDefine ['CallCharge']		['Index']		= 11;	// DDDDDDDDCC
		$arrDefine ['BandStep']			['Index']		= 12;	// 4 digit distance step code  
		$arrDefine ['GSTFlag']			['Index']		= 13;	// One Character flag contains “N”o or “Y”es
		$arrDefine ['RateDate']			['Index']		= 14;	// DD/MM/CCYY
		$arrDefine ['FNN']				['Index']		= 15;	// FNN (added by pre-processor)
		$arrDefine ['CallDate']			['Index']		= 16;	// Call Date (added by pre-processor)
		
		//$arrDefine ['']	['Validate']	= "";
		
		$this->_arrDefineCarrier = $arrDefine;
		
		//##----------------------------------------------------------------##//
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
	 * @return	mixed					Normalised Data (Array, ready for direct UPDATE
	 * 									into DB. Returns an error code (constant) on failure
	 *
	 * @method
	 */	
	function Normalise($arrCDR)
	{
		// ignore header rows
		if ((int)$arrCDR["CDR.SequenceNo"] < 1)
		{
			return CDR_CANT_NORMALISE_BAD_SEQ_NO;
		}
		elseif ((int)$arrCDR["CDR.SequenceNo"] < $this->_intStartRow)
		{
			return CDR_CANT_NORMALISE_HEADER;
		}
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR.CDR"]);

		// ignore non-CDR rows
		$intRowType = (int)$this->_FetchRawCDR('CC');
		if ($intRowType != 3)
		{
			return CDR_CANT_NORMALISE_NON_CDR;
		}

		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return CDR_CANT_NORMALISE_RAW;
		}
		
		// build a new output CDR
		$this->_NewCDR();
		
		//##----------------------------------------------------------------##//
		// add fields to CDR
		//##----------------------------------------------------------------##//
		
		//--------------------------------------------------------//
		// Required Fields
		//--------------------------------------------------------//
		
		// FNN
		$mixValue = $this->_FetchRawCDR('FNN');
		$this->_AppendCDR('FNN', $mixValue);
		
		// CarrierRef
		$mixValue = $this->_GenerateUID($arrCDR["FileName"], $arrCDR["CDR.SequenceNo"]);
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDatetime
		$mixValue = $this->_FetchRawCDR('CallDate');
		$mixValue .= $this->_FetchRawCDR('CallTime');
		$mixValue = ConvertTime($mixValue);
		$this->_AppendCDR('StartDateTime', $mixValue);
		
		// EndDateTime
	 	$mixValue = date("Y-m-d H:i:s", strtotime($mixValue . " +" . $this->_FetchRawCDR('Duration') . "seconds"));
		$this->_AppendCDR('EndDateTime', $mixValue);
	
		// Units
		//TODO !!!! - this will only work with timed items (like calls)
		$arrValue = explode(':', $this->_FetchRawCDR('Duration'));
		$intValue = ((int)$arrValue[0] * 3600) + ((int)$arrValue[1] * 60) + (int)$arrValue[2]; 
		$this->_AppendCDR('Units', $intValue);
		
		// Description
		$mixValue = $this->_FetchRawCDR('OriginatingCity') . " to " . $this->_FetchRawCDR('Destination');
		$this->_AppendCDR('Description', $mixValue);
		
		// RecordType
		$mixValue = $this->_FetchRawCDR(''); // needs to match database
		//$this->_AppendCDR('RecordType', $mixValue);
		
		// ServiceType
		//TODO !!!! - need to account for inbound services
		$mixValue = SERVICE_TYPE_LAND_LINE;
		$this->_AppendCDR('ServiceType', $mixValue);

		//--------------------------------------------------------//
		// Optional Fields
		//--------------------------------------------------------//

		// Source
		$mixValue = $this->_FetchRawCDR('OriginatingCLI');
		//$this->_AppendCDR('Source', $mixValue);
		
		// Destination
		$mixValue = $this->_FetchRawCDR('NumberDialled');
		//$this->_AppendCDR('Destination', $mixValue);
		
		// Cost
		$mixValue = $this->_FetchRawCDR('CallCharge');
		//$this->_AppendCDR('Cost', $mixValue);

		//##----------------------------------------------------------------##//
		
		if (!$this->ApplyOwnership())
		{
			$this->_AppendCDR('Status', CDR_BAD_OWNER);
		}
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleSkel
	//------------------------------------------------------------------------//
	
?>
