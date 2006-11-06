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
	// _strFNN
	//------------------------------------------------------------------------//
	/**
	 * _strFNN
	 *
	 * The current FNN
	 *
	 * The current FNN being applied in the pre-processor
	 *
	 * @type	string
	 *
	 * @property
	 * @see	Preprocessor()
	 */
	private $_strFNN;
	
	//------------------------------------------------------------------------//
	// _strCallDate
	//------------------------------------------------------------------------//
	/**
	 * _strCallDate
	 *
	 * The current Call date
	 *
	 * The current Call date being applied in the pre-processor
	 *
	 * @type	string
	 *
	 * @property
	 * @see	Preprocessor()
	 */
	private $_strCallDate;
	
	
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
		$arrDefine ['CC']				['Validate']	= "^3$";
		$arrDefine ['OriginatingCLI']	['Index']		= 1;	// Blank or 10 digit  number
		$arrDefine ['OriginatingCLI']	['Validate']	= "^$|^\d{10}$";
		$arrDefine ['OriginatingCSI']	['Index']		= 2;	// Up to 10 digit numeric
		$arrDefine ['OriginatingCity']	['Index']		= 3;	// 1-13 characters
		$arrDefine ['OriginatingState']	['Index']		= 4;	// 2-3 characters

		$arrDefine ['CallDetails']		['Index']		= 5;	// Contains:
		$arrDefine ['CallTime']			['Index']		= 5;	// 	HHMMSS
		$arrDefine ['CallTime']			['Start']		= 0;
		$arrDefine ['CallTime']			['Length']		= 6;
		$arrDefine ['CallTime']			['Validate']	= "^[0-2]\d[0-5]\d[0-5]\d$";
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
		$arrDefine ['Duration']			['Validate']	= "^\d{1-4}:[0-5]\d:[0-5]\d$";
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
	 * 									into DB.
	 *
	 * @method
	 */	
	function Normalise($arrCDR)
	{
		// set up CDR
		$this->_NewCDR($arrCDR);
		
		// ignore header rows
		if ((int)$arrCDR["CDR.SequenceNo"] < 1)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_BAD_SEQ_NO);
		}
		elseif ((int)$arrCDR["CDR.SequenceNo"] < $this->_intStartRow)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_HEADER);
		}
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR.CDR"]);

		// ignore non-CDR rows
		$intRowType = (int)$this->_FetchRawCDR('CC');
		if ($intRowType != 3)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		// validation of Raw CDR
		if (!$this->_ValidateRawCDR())
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_RAW);
		}
		
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
		
		// Apply Ownership
		if (!$this->ApplyOwnership())
		{
			$this->_AppendCDR('Status', CDR_BAD_OWNER);
		}
		
		// Validation of Normalised data
		if (!$this->Validate())
		{
			$this->_AppendCDR('Status', CDR_CANT_NORMALISE_INVALID);
		}
		
		// return output array
		return $this->_OutputCDR();
	}

	//------------------------------------------------------------------------//
	// Preprocessor
	//------------------------------------------------------------------------//
	/**
	 * Preprocessor()
	 *
	 * Preprocess raw data from the CDR
	 *
	 * Preprocess raw data from the CDR
	 * 
	 * @param	string		strCDR		CDR line
	 *
	 * @return	string					returns original or modified CDR line
	 *
	 * @method
	 */	
	function Preprocessor($strCDR)
	{
		// Determine the type of row
		if (preg_match(ROW_FNN, $strCDR))
		{
			// FNN Row
			$this->_strFNN = substr($strCDR, 1, 10);
		}
		elseif (preg_match(ROW_DATE, $strCDR))
		{
			// Date Row
			$this->_strCallDate = substr($strCDR, 1, 10);
		}
		elseif (preg_match(ROW_CDR, $strCDR))
		{
			// CDR Row
			$strCDR .= $this->_strDelimiter . $this->_strFNN . $this->_strDelimiter . $this->_strCallDate;		
		}
		
		return $strCDR;
	}
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleAAPT
	//------------------------------------------------------------------------//
	define("ROW_FNN",	"^1");
	define("ROW_DATE",	"^2");
	define("ROW_CDR",	"^3");
?>
