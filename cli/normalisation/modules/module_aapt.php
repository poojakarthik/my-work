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
	
	public $intBaseCarrier	= CARRIER_AAPT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD;
	
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
	function __construct($intCarrier)
	{
		// call parent constructor
		parent::__construct($intCarrier);
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the column delimiter
		$this->_strDelimiter = "\t";
		
		// define the carrier CDR format
		$arrDefine ['CC']				['Index']		= 0;	// record type indicator
		$arrDefine ['CC']				['Validate']	= "/^3$/";
		$arrDefine ['OriginatingCLI']	['Index']		= 1;	// Blank or 10 digit  number
		$arrDefine ['OriginatingCLI']	['Validate']	= "/^ $|^\d{10}$/";
		$arrDefine ['OriginatingCSI']	['Index']		= 2;	// Up to 10 digit numeric
		$arrDefine ['OriginatingCity']	['Index']		= 3;	// 1-13 characters
		$arrDefine ['OriginatingState']	['Index']		= 4;	// 2-3 characters

		$arrDefine ['CallDetails']		['Index']		= 5;	// Contains:
		$arrDefine ['CallTime']			['Index']		= 5;	// 	HHMMSS
		$arrDefine ['CallTime']			['Start']		= 0;
		$arrDefine ['CallTime']			['Length']		= 6;
		$arrDefine ['CallTime']			['Validate']	= "/^[0-2]\d[0-5]\d[0-5]\d$/";
		$arrDefine ['RatePeriod']		['Index']		= 5;	// 	peak/off peak flag
		$arrDefine ['RatePeriod']		['Start']		= 6;
		$arrDefine ['RatePeriod']		['Length']		= 1;
		$arrDefine ['RevenueCallType']	['Index']		= 5;	// 	3 Character  right justified space filled
		$arrDefine ['RevenueCallType']	['Start']		= 7;
		$arrDefine ['RevenueCallType']	['Length']		= 3;

		$arrDefine ['LocationCallType']	['Index']		= 6;	// 3 digits
		$arrDefine ['RateTable']		['Index']		= 7;	// 2-5 digits
		$arrDefine ['Destination']		['Index']		= 8;	// city called
		$arrDefine ['NumberDialled']	['Index']		= 9;	// digits dialled by customer
		$arrDefine ['Duration']			['Index']		= 10;	// HHHH:MM:SS
		$arrDefine ['Duration']			['Validate']	= "/^\d+:[0-5]\d:[0-5]\d$/";
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
		if ((int)$arrCDR["SequenceNo"] < 1)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_BAD_SEQ_NO);
		}
		elseif ((int)$arrCDR["SequenceNo"] < $this->_intStartRow)
		{
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_HEADER);
		}
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR"]);

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
		$strFNN 						= $this->_FetchRawCDR('FNN');
		$this->_AppendCDR('FNN', $strFNN);
		
		// ServiceType
		if ($this->_IsInbound($strFNN))
		{
			$intServiceType 			= SERVICE_TYPE_INBOUND;
		}
		else
		{
			$intServiceType 			= SERVICE_TYPE_LAND_LINE;
		}
		$this->_AppendCDR('ServiceType', $intServiceType);
		
		// RecordType
		$mixCarrierCode					= (int)$this->_FetchRawCDR('BandStep');
		$strRecordCode 					= $this->FindRecordCode($mixCarrierCode);
		$mixValue 						= $this->FindRecordType($intServiceType, $strRecordCode); 
		$this->_AppendCDR('RecordType', $mixValue);
				
		// Destination Code & Description (only if we have a context)
		if ($this->_intContext > 0)
		{
			$mixCarrierCode 				= $this->_FetchRawCDR('RateTable');
			$arrDestinationCode 			= $this->FindDestination($mixCarrierCode);
			if ($arrDestinationCode)
			{
				$this->_AppendCDR('DestinationCode', $arrDestinationCode['Code']);
				$this->_AppendCDR('Description', $arrDestinationCode['Description']);
			}
		}
		// CarrierRef
		$mixValue 						= $this->_GenerateUID($arrCDR["FileName"], $arrCDR["SequenceNo"]);
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDatetime
		$mixValue 						 = $this->_FetchRawCDR('CallDate');
		$mixValue 						.= $this->_FetchRawCDR('CallTime');
		$mixValue 						 = $this->ConvertTime($mixValue);
		$strStartDatetime 				= $mixValue;
		$this->_AppendCDR('StartDatetime', $mixValue);
		
		// Units
		//TODO-LATER !!!! - this will only work with timed items (like calls)
		$arrValue 						= explode(':', $this->_FetchRawCDR('Duration'));
		$intValue 						= ((int)$arrValue[0] * 3600) + ((int)$arrValue[1] * 60) + (int)$arrValue[2]; 
		$this->_AppendCDR('Units', $intValue);

		// EndDateTime
		$intTimestamp					= strtotime("+ " . $intValue . " seconds", strtotime($strStartDatetime));
	 	$mixValue 						= date("Y-m-d H:i:s", $intTimestamp);
		$this->_AppendCDR('EndDatetime', $mixValue);
	
		// Description
		$strDescription = '';
		//TODO-LATER !!!! - add description
		if ($strDescription)
		{
			$this->_AppendCDR('Description', $strDescription);
		}

		//--------------------------------------------------------//
		// Optional Fields
		//--------------------------------------------------------//
		
		// Source
		$mixValue 						= $this->_FetchRawCDR('OriginatingCLI');
		if ($mixValue == " ")
		{
			$mixValue = NULL;
		}
		$this->_AppendCDR('Source', $mixValue);
		
		// Destination
		$mixValue 						= $this->_FetchRawCDR('NumberDialled');
		if ($mixValue == " ")
		{
			$mixValue = NULL;
		}
		$this->_AppendCDR('Destination', $mixValue);
		if ($intServiceType == SERVICE_TYPE_INBOUND)
		{
			$this->_AppendCDR('Description', $mixValue);
		}
		
		// Cost
		$mixValue 						= ((int)$this->_FetchRawCDR('CallCharge')) / 100.0;
		$this->_AppendCDR('Cost', $mixValue);

		//##----------------------------------------------------------------##//
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
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

	//------------------------------------------------------------------------//
	// ConvertTime
	//------------------------------------------------------------------------//
	/**
	 * ConvertTime()
	 *
	 * Convert time format
	 *
	 * Converts a datetime string from carrier's format to our own
	 *
	 * @param	string	$strTime	Datetime string to be converted
	 * @return	string				Converted Datetime string
	 *
	 * @method
	 */
	function ConvertTime($strTime)
	{
		$strReturn 	= substr($strTime, 6, 4);			// Year
		$strReturn .= "-" . substr($strTime, 3, 2);		// Month
		$strReturn .= "-" . substr($strTime, 0, 2);		// Day
		$strReturn .= " ";
		$strReturn .= substr($strTime, 10, 2).":";		// Hour
		$strReturn .= substr($strTime, 12, 2).":";		// Mins
		$strReturn .= substr($strTime, 14, 2);			// Secs
		
		return $strReturn;
	}
	
	//------------------------------------------------------------------------//
	// RawDestinationCode
	//------------------------------------------------------------------------//
	/**
	 * RawDestinationCode()
	 *
	 * Returns the Raw Destination Code from the CDR
	 *
	 * Returns the Raw Destination Code from the CDR
	 * 
	 *
	 * @return	mixed					Raw Destination Code
	 *
	 * @method
	 */
	 function RawDestinationCode()
	 {
	 	return $this->_FetchRawCDR('RateTable');
	 }
	 
	//------------------------------------------------------------------------//
	// RawDescription
	//------------------------------------------------------------------------//
	/**
	 * RawDescription()
	 *
	 * Returns the Raw Description from the CDR
	 *
	 * Returns the Raw Description from the CDR
	 * 
	 *
	 * @return	mixed					Raw Description
	 *
	 * @method
	 */
	 function RawDescription()
	 {
	 	return $this->_FetchRawCDR('Destination');
	 }
	 
	//------------------------------------------------------------------------//
	// RawRecordType
	//------------------------------------------------------------------------//
	/**
	 * RawRecordType()
	 *
	 * Returns the Raw RawRecord Type from the CDR
	 *
	 * Returns the Raw RawRecord Type from the CDR
	 * 
	 *
	 * @return	mixed					Raw RawRecord Type
	 *
	 * @method
	 */
	 function RawRecordType()
	 {
	 	return (int)$this->_FetchRawCDR('BandStep');
	 }
}

	//------------------------------------------------------------------------//
	// Constants for NormalisationModuleAAPT
	//------------------------------------------------------------------------//
	define("ROW_FNN",	"/^1/");
	define("ROW_DATE",	"/^2/");
	define("ROW_CDR",	"/^3/");
?>
